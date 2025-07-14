<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use app\models\TaxJurisdiction;
use app\models\State;
use app\components\UsSalesTaxCalculator;

/**
 * Tax Rate Management Console Controller
 * 
 * Provides console commands for importing, updating, and managing US sales tax rates.
 * 
 * Usage examples:
 * - ./yii tax-rate/import-csv /path/to/rates.csv
 * - ./yii tax-rate/seed-basic-rates
 * - ./yii tax-rate/update-from-calculator
 * - ./yii tax-rate/cleanup-old-rates
 * - ./yii tax-rate/verify-rates
 */
class TaxRateController extends Controller
{
    /**
     * Import tax rates from CSV file with debug information
     * 
     * Supports multiple formats:
     * - Avalara: State,ZipCode,TaxRegionName,EstimatedCombinedRate,StateRate,EstimatedCountyRate,EstimatedCityRate,EstimatedSpecialRate,RiskLevel
     * - Standard: zip_code,state_code,state_name,county_name,city_name,tax_region_name,combined_rate,state_rate,county_rate,city_rate,special_rate,estimated_population
     * 
     * @param string $filePath Path to CSV file
     * @param string $dataSource Data source identifier (default: 'import', use 'avalara' for Avalara files)
     * @return int Exit code
     */
    public function actionImportCsv($filePath, $dataSource = TaxJurisdiction::DATA_SOURCE_IMPORT)
    {
        if (!file_exists($filePath)) {
            $this->stderr("Error: File not found: {$filePath}\n", Console::FG_RED);
            return ExitCode::DATAERR;
        }

        $this->stdout("Importing tax rates from: {$filePath}\n", Console::FG_GREEN);
        $this->stdout("Data source: {$dataSource}\n", Console::FG_YELLOW);

        $csvData = [];
        $handle = fopen($filePath, 'r');
        
        if (!$handle) {
            $this->stderr("Error: Could not open file for reading\n", Console::FG_RED);
            return ExitCode::IOERR;
        }
        
        $headers = fgetcsv($handle, 0, ',', '"', '\\'); // Read header row
        
        if (!$headers) {
            $this->stderr("Error: Invalid CSV file format\n", Console::FG_RED);
            fclose($handle);
            return ExitCode::DATAERR;
        }

        // Debug: Show headers detected
        $this->stdout("Headers detected: " . implode(', ', $headers) . "\n", Console::FG_CYAN);
        
        // Test first row to validate format
        $firstRow = fgetcsv($handle, 0, ',', '"', '\\');
        if ($firstRow) {
            $testRowData = array_combine($headers, $firstRow);
            $this->stdout("First row sample: " . json_encode($testRowData) . "\n", Console::FG_CYAN);
            
            // Test normalization
            $normalized = TaxJurisdiction::normalizeRowData($testRowData, $dataSource);
            $this->stdout("Normalized sample: ZIP={$normalized['zip_code']}, State={$normalized['state_code']}, Rate={$normalized['combined_rate']}\n", Console::FG_CYAN);
            
            // Put first row back into processing
            $csvData[] = $testRowData;
        }
        
        // Reset file pointer for full processing
        rewind($handle);
        fgetcsv($handle, 0, ',', '"', '\\'); // Skip headers again

        $rowCount = 0;
        $skippedRows = 0;
        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($row) !== count($headers)) {
                $skippedRows++;
                if ($skippedRows <= 3) { // Show first few problematic rows
                    $this->stderr("Warning: Skipping malformed row {$rowCount} - Expected " . count($headers) . " columns, got " . count($row) . ": " . implode(',', $row) . "\n", Console::FG_YELLOW);
                }
                continue;
            }
            
            $csvData[] = array_combine($headers, $row);
            $rowCount++;
            
            // Process in smaller batches for better debugging
            if ($rowCount % 100 === 0) {
                $this->stdout("Processed {$rowCount} rows...\n");
                $this->processBatch($csvData, $dataSource);
                $csvData = [];
            }
        }
        
        fclose($handle);
        
        // Process remaining rows
        if (!empty($csvData)) {
            $this->processBatch($csvData, $dataSource);
        }

        if ($skippedRows > 0) {
            $this->stdout("Warning: Skipped {$skippedRows} malformed rows\n", Console::FG_YELLOW);
        }

        $this->stdout("Import completed. Total rows processed: {$rowCount}\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * Process a batch of CSV data with detailed debugging
     */
    private function processBatch($csvData, $dataSource)
    {
        $this->stdout("Processing batch of " . count($csvData) . " records...\n", Console::FG_BLUE);
        
        $results = TaxJurisdiction::importFromCsv($csvData, $dataSource);
        
        $this->stdout("Batch results - Imported: {$results['imported']}, Updated: {$results['updated']}\n", Console::FG_GREEN);
        
        if (!empty($results['errors'])) {
            $this->stderr("Errors in this batch: " . count($results['errors']) . "\n", Console::FG_RED);
            $errorCount = 0;
            foreach ($results['errors'] as $error) {
                $this->stderr("  - {$error}\n", Console::FG_RED);
                $errorCount++;
                // Show only first 5 errors per batch to avoid spam
                if ($errorCount >= 5) {
                    $remaining = count($results['errors']) - $errorCount;
                    if ($remaining > 0) {
                        $this->stderr("  ... and {$remaining} more errors\n", Console::FG_RED);
                    }
                    break;
                }
            }
        }
    }

    /**
     * Seed basic tax rates from UsSalesTaxCalculator
     * This creates basic state-level tax jurisdictions for ZIP codes starting with state-specific prefixes
     */
    public function actionSeedBasicRates()
    {
        $this->stdout("Seeding basic tax rates from UsSalesTaxCalculator...\n", Console::FG_GREEN);

        $calculator = new UsSalesTaxCalculator();
        $states = $calculator->getSupportedStates();
        $imported = 0;

        foreach ($states as $stateCode) {
            $stateInfo = $calculator->getStateTaxInfo($stateCode);
            if (!$stateInfo) continue;

            // Create a basic entry for each state (using state code as ZIP for now)
            $jurisdiction = TaxJurisdiction::findByZipCode($stateCode . '00000');
            
            if (!$jurisdiction) {
                $jurisdiction = new TaxJurisdiction();
                $jurisdiction->zip_code = $stateCode . '00000'; // Placeholder ZIP
                $jurisdiction->state_code = $stateCode;
                $jurisdiction->state_name = $this->getStateName($stateCode);
                $jurisdiction->tax_region_name = "Default {$stateCode} Rate";
                $jurisdiction->state_rate = $stateInfo['base_rate'];
                $jurisdiction->combined_rate = $stateInfo['average_total_rate'];
                $jurisdiction->county_rate = max(0, $stateInfo['average_total_rate'] - $stateInfo['base_rate']);
                $jurisdiction->data_source = TaxJurisdiction::DATA_SOURCE_MANUAL;
                $jurisdiction->effective_date = date('Y-m-d');
                $jurisdiction->data_year = date('Y');
                $jurisdiction->data_month = date('n');
                $jurisdiction->last_verified = date('Y-m-d');
                $jurisdiction->is_active = true;

                if ($jurisdiction->save()) {
                    $imported++;
                    $this->stdout("Created basic rate for {$stateCode}: {$stateInfo['average_total_rate']}%\n");
                } else {
                    $this->stderr("Failed to create rate for {$stateCode}: " . json_encode($jurisdiction->errors) . "\n", Console::FG_RED);
                }
            }
        }

        $this->stdout("Seeded {$imported} basic tax rates\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * Update rates from calculator (for states where we don't have detailed ZIP data)
     */
    public function actionUpdateFromCalculator()
    {
        $this->stdout("Updating tax rates from UsSalesTaxCalculator...\n", Console::FG_GREEN);

        $calculator = new UsSalesTaxCalculator();
        $updated = 0;

        $jurisdictions = TaxJurisdiction::find()
            ->where(['data_source' => TaxJurisdiction::DATA_SOURCE_MANUAL])
            ->andWhere(['is_active' => true])
            ->all();

        foreach ($jurisdictions as $jurisdiction) {
            $stateInfo = $calculator->getStateTaxInfo($jurisdiction->state_code);
            if (!$stateInfo) continue;

            // Update if rate has changed
            if ($jurisdiction->combined_rate != $stateInfo['average_total_rate']) {
                $jurisdiction->state_rate = $stateInfo['base_rate'];
                $jurisdiction->combined_rate = $stateInfo['average_total_rate'];
                $jurisdiction->county_rate = max(0, $stateInfo['average_total_rate'] - $stateInfo['base_rate']);
                $jurisdiction->last_verified = date('Y-m-d');

                if ($jurisdiction->save()) {
                    $updated++;
                    $this->stdout("Updated {$jurisdiction->state_code}: {$stateInfo['average_total_rate']}%\n");
                }
            }
        }

        $this->stdout("Updated {$updated} tax rates\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * Clean up old and expired tax rates
     */
    public function actionCleanupOldRates($daysOld = 365)
    {
        $this->stdout("Cleaning up tax rates older than {$daysOld} days...\n", Console::FG_GREEN);

        $cutoffDate = date('Y-m-d', strtotime("-{$daysOld} days"));
        
        $deletedCount = TaxJurisdiction::deleteAll([
            'and',
            ['is_active' => false],
            ['<', 'effective_date', $cutoffDate]
        ]);

        $this->stdout("Deleted {$deletedCount} old tax rate records\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * Verify tax rates and mark those needing updates
     */
    public function actionVerifyRates($verificationDays = 90)
    {
        $this->stdout("Verifying tax rates (checking those older than {$verificationDays} days)...\n", Console::FG_GREEN);

        $needsVerification = TaxJurisdiction::find()
            ->where(['is_active' => true])
            ->andWhere([
                'or',
                ['last_verified' => null],
                ['<', 'last_verified', date('Y-m-d', strtotime("-{$verificationDays} days"))]
            ])
            ->count();

        $expired = TaxJurisdiction::find()
            ->where(['is_active' => true])
            ->andWhere(['<', 'expiry_date', date('Y-m-d')])
            ->count();

        $this->stdout("Tax rates needing verification: {$needsVerification}\n", Console::FG_YELLOW);
        $this->stdout("Expired tax rates: {$expired}\n", Console::FG_RED);

        if ($expired > 0) {
            $this->stdout("Deactivating expired rates...\n");
            TaxJurisdiction::updateAll(
                ['is_active' => false],
                ['and', ['is_active' => true], ['<', 'expiry_date', date('Y-m-d')]]
            );
            $this->stdout("Deactivated {$expired} expired rates\n", Console::FG_GREEN);
        }

        return ExitCode::OK;
    }

    /**
     * Show tax rate statistics
     */
    public function actionStats()
    {
        $this->stdout("Tax Rate Database Statistics:\n", Console::FG_GREEN);
        
        $stats = TaxJurisdiction::getStatistics();
        
        $this->stdout("Total Jurisdictions: {$stats['total_jurisdictions']}\n");
        $this->stdout("Active Jurisdictions: {$stats['active_jurisdictions']}\n");
        $this->stdout("States Covered: {$stats['states_covered']}\n");
        $this->stdout("Last Updated: {$stats['last_updated']}\n");
        $this->stdout("Latest Effective Date: {$stats['latest_effective_date']}\n");
        
        $this->stdout("\nData Sources:\n", Console::FG_CYAN);
        foreach ($stats['data_sources'] as $source) {
            $count = TaxJurisdiction::find()->where(['data_source' => $source->data_source, 'is_active' => true])->count();
            $this->stdout("  {$source->data_source}: {$count} jurisdictions\n");
        }

        return ExitCode::OK;
    }

    /**
     * Test tax rate lookup for a ZIP code
     */
    public function actionTest($zipCode)
    {
        $this->stdout("Testing tax rate lookup for ZIP code: {$zipCode}\n", Console::FG_GREEN);

        $jurisdiction = TaxJurisdiction::findByZipCode($zipCode);
        
        if ($jurisdiction) {
            $this->stdout("Found jurisdiction:\n", Console::FG_GREEN);
            $this->stdout("  ZIP Code: {$jurisdiction->zip_code}\n");
            $this->stdout("  State: {$jurisdiction->state_code}\n");
            $this->stdout("  Region: {$jurisdiction->tax_region_name}\n");
            $this->stdout("  State Rate: " . TaxJurisdiction::formatRate($jurisdiction->state_rate) . "\n");
            $this->stdout("  County Rate: " . TaxJurisdiction::formatRate($jurisdiction->county_rate) . "\n");
            $this->stdout("  City Rate: " . TaxJurisdiction::formatRate($jurisdiction->city_rate) . "\n");
            $this->stdout("  Special Rate: " . TaxJurisdiction::formatRate($jurisdiction->special_rate) . "\n");
            $this->stdout("  Combined Rate: " . TaxJurisdiction::formatRate($jurisdiction->combined_rate) . "\n");
            $this->stdout("  Effective Date: {$jurisdiction->effective_date}\n");
            $this->stdout("  Data Source: {$jurisdiction->data_source}\n");
        } else {
            $this->stderr("No tax jurisdiction found for ZIP code: {$zipCode}\n", Console::FG_RED);
            
            // Fallback to calculator
            $calculator = new UsSalesTaxCalculator();
            $stateCode = $this->guessStateFromZip($zipCode);
            if ($stateCode) {
                $rate = $calculator->calculateTaxRate($stateCode, true);
                $this->stdout("Fallback rate from calculator for state {$stateCode}: {$rate}%\n", Console::FG_YELLOW);
            }
        }

        return ExitCode::OK;
    }

    /**
     * Generate sample CSV file for testing
     */
    public function actionGenerateSampleCsv($outputPath = 'sample_tax_rates.csv')
    {
        $this->stdout("Generating sample CSV file: {$outputPath}\n", Console::FG_GREEN);

        $sampleData = [
            ['zip_code', 'state_code', 'state_name', 'county_name', 'city_name', 'tax_region_name', 'combined_rate', 'state_rate', 'county_rate', 'city_rate', 'special_rate', 'estimated_population'],
            ['90210', 'CA', 'California', 'Los Angeles', 'Beverly Hills', 'Beverly Hills Tax Region', '9.5000', '6.0000', '1.0000', '2.5000', '0.0000', '34000'],
            ['10001', 'NY', 'New York', 'New York', 'New York', 'Manhattan Tax Region', '8.2500', '4.0000', '2.2500', '2.0000', '0.0000', '1600000'],
            ['60601', 'IL', 'Illinois', 'Cook', 'Chicago', 'Chicago Tax Region', '10.2500', '6.2500', '1.7500', '2.2500', '0.0000', '2700000'],
            ['75201', 'TX', 'Texas', 'Dallas', 'Dallas', 'Dallas Tax Region', '8.2500', '6.2500', '0.5000', '1.5000', '0.0000', '1300000'],
            ['33101', 'FL', 'Florida', 'Miami-Dade', 'Miami', 'Miami Tax Region', '7.0000', '6.0000', '0.5000', '0.5000', '0.0000', '470000'],
        ];

        $handle = fopen($outputPath, 'w');
        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        $this->stdout("Sample CSV file created: {$outputPath}\n", Console::FG_GREEN);
        $this->stdout("You can use this file to test the import functionality:\n");
        $this->stdout("./yii tax-rate/import-csv {$outputPath}\n", Console::FG_CYAN);

        return ExitCode::OK;
    }

    /**
     * Get state name from state code
     */
    private function getStateName($stateCode)
    {
        $stateName = State::getStateName($stateCode, 'US');
        return $stateName ?? $stateCode;
    }

    /**
     * Guess state code from ZIP code (basic implementation)
     */
    private function guessStateFromZip($zipCode)
    {
        $zip = substr($zipCode, 0, 3);
        
        $zipRanges = [
            '100' => 'NY', '200' => 'DC', '300' => 'PA', '400' => 'GA',
            '500' => 'IA', '600' => 'IL', '700' => 'TX', '800' => 'CO',
            '900' => 'CA', '010' => 'MA', '020' => 'MA', '030' => 'NH',
        ];

        return $zipRanges[$zip] ?? null;
    }
}