<?php

namespace app\components;

use yii\base\Component;
use app\models\StateTaxRate;

/**
 * US Sales Tax Calculator Component
 * 
 * Provides automatic sales tax calculation for US states based on:
 * - State base rates from database
 * - Economic nexus rules
 * - Tax jurisdiction lookup
 * 
 * @author Generated with Claude Code
 */
class UsSalesTaxCalculator extends Component
{
    /**
     * @var bool Whether fallback mode was used in the last calculation
     */
    public $lastCalculationUsedFallback = false;

    /**
     * @var string Reason for fallback usage
     */
    public $fallbackReason = '';

    /**
     * Calculate sales tax rate for a given state
     * 
     * @param string $stateCode Two-letter state code (e.g., 'CA', 'NY')
     * @param bool $useLocalTax Whether to include average local tax rates
     * @param string|null $zipCode ZIP code for more precise local tax lookup (future enhancement)
     * @param string $countryCode Two-letter country code (default: 'US')
     * @return float Tax rate as percentage (e.g., 8.25 for 8.25%)
     */
    public function calculateTaxRate($stateCode, $useLocalTax = true, $zipCode = null, $countryCode = 'US')
    {
        // Reset fallback tracking
        $this->lastCalculationUsedFallback = false;
        $this->fallbackReason = '';
        
        try {
            $taxRate = StateTaxRate::getTaxRate(strtoupper($stateCode), strtoupper($countryCode), $useLocalTax);
            
            // If we get a rate > 0, return it
            if ($taxRate > 0) {
                return $taxRate;
            }
            
            // If StateTaxRate returns 0, check if table has any data
            $hasData = StateTaxRate::find()
                ->where(['country_code' => strtoupper($countryCode), 'is_active' => true])
                ->exists();
                
            if (!$hasData) {
                // Fallback to hardcoded rates if no data in table
                $this->lastCalculationUsedFallback = true;
                $this->fallbackReason = 'no_data_in_table';
                return $this->getFallbackTaxRate(strtoupper($stateCode), $useLocalTax);
            }
            
            return $taxRate; // Return 0 if state genuinely has no tax
            
        } catch (\Exception $e) {
            // On any database error, fallback to hardcoded rates
            \Yii::error("StateTaxRate database error, using fallback: " . $e->getMessage());
            $this->lastCalculationUsedFallback = true;
            $this->fallbackReason = 'database_error';
            return $this->getFallbackTaxRate(strtoupper($stateCode), $useLocalTax);
        }
    }

    /**
     * Calculate sales tax amount
     * 
     * @param float $taxableAmount The taxable amount
     * @param string $stateCode Two-letter state code
     * @param bool $useLocalTax Whether to include local tax rates
     * @param string|null $zipCode ZIP code for precise calculation (future)
     * @param string $countryCode Two-letter country code (default: 'US')
     * @return float Tax amount in dollars
     */
    public function calculateTaxAmount($taxableAmount, $stateCode, $useLocalTax = true, $zipCode = null, $countryCode = 'US')
    {
        $taxRate = $this->calculateTaxRate($stateCode, $useLocalTax, $zipCode, $countryCode);
        return round($taxableAmount * ($taxRate / 100), 2);
    }

    /**
     * Check if a business has economic nexus in a state
     * 
     * @param string $stateCode Two-letter state code
     * @param float $annualRevenue Annual revenue in the state
     * @param int $annualTransactions Annual number of transactions in the state
     * @param string $countryCode Two-letter country code (default: 'US')
     * @return bool True if nexus threshold is met
     */
    public function hasEconomicNexus($stateCode, $annualRevenue, $annualTransactions = 0, $countryCode = 'US')
    {
        return StateTaxRate::hasEconomicNexus(
            strtoupper($stateCode), 
            strtoupper($countryCode), 
            $annualRevenue, 
            $annualTransactions
        );
    }

    /**
     * Get nexus requirements for a state
     * 
     * @param string $stateCode Two-letter state code
     * @param string $countryCode Two-letter country code (default: 'US')
     * @return array|null Nexus thresholds or null if no sales tax
     */
    public function getNexusRequirements($stateCode, $countryCode = 'US')
    {
        return StateTaxRate::getNexusInfo(strtoupper($stateCode), strtoupper($countryCode));
    }

    /**
     * Get all states with no sales tax
     * 
     * @param string $countryCode Two-letter country code (default: 'US')
     * @return array Array of state codes with no sales tax
     */
    public function getNoSalesTaxStates($countryCode = 'US')
    {
        return StateTaxRate::getNoSalesTaxStates(strtoupper($countryCode));
    }

    /**
     * Get detailed tax information for a state
     * 
     * @param string $stateCode Two-letter state code
     * @param string $countryCode Two-letter country code (default: 'US')
     * @return array|null Detailed tax information
     */
    public function getStateTaxInfo($stateCode, $countryCode = 'US')
    {
        return StateTaxRate::getStateTaxInfo(strtoupper($stateCode), strtoupper($countryCode));
    }

    /**
     * Calculate tax for multiple line items with different tax rules
     * 
     * @param array $lineItems Array of line items [amount, is_taxable, state_code, country_code]
     * @param bool $useLocalTax Whether to use local tax rates
     * @return array Tax calculation results
     */
    public function calculateMultiStateTax($lineItems, $useLocalTax = true)
    {
        $results = [
            'total_taxable' => 0,
            'total_tax' => 0,
            'by_state' => [],
        ];
        
        foreach ($lineItems as $item) {
            $amount = $item['amount'] ?? 0;
            $isTaxable = $item['is_taxable'] ?? true;
            $stateCode = $item['state_code'] ?? 'CA'; // Default to CA
            $countryCode = $item['country_code'] ?? 'US'; // Default to US
            
            if (!$isTaxable || $amount <= 0) {
                continue;
            }
            
            $stateCode = strtoupper($stateCode);
            $countryCode = strtoupper($countryCode);
            $stateKey = $countryCode . '-' . $stateCode;
            
            if (!isset($results['by_state'][$stateKey])) {
                $results['by_state'][$stateKey] = [
                    'state_code' => $stateCode,
                    'country_code' => $countryCode,
                    'taxable_amount' => 0,
                    'tax_rate' => $this->calculateTaxRate($stateCode, $useLocalTax, null, $countryCode),
                    'tax_amount' => 0,
                ];
            }
            
            $results['by_state'][$stateKey]['taxable_amount'] += $amount;
            $results['total_taxable'] += $amount;
        }
        
        // Calculate tax for each state
        foreach ($results['by_state'] as $stateKey => &$stateData) {
            $stateData['tax_amount'] = $this->calculateTaxAmount(
                $stateData['taxable_amount'], 
                $stateData['state_code'], 
                $useLocalTax,
                null,
                $stateData['country_code']
            );
            $results['total_tax'] += $stateData['tax_amount'];
        }
        
        return $results;
    }

    /**
     * Validate state code
     * 
     * @param string $stateCode Two-letter state code
     * @param string $countryCode Two-letter country code (default: 'US')
     * @return bool True if valid state code
     */
    public function isValidStateCode($stateCode, $countryCode = 'US')
    {
        $taxInfo = StateTaxRate::getStateTaxInfo(strtoupper($stateCode), strtoupper($countryCode));
        return $taxInfo !== null;
    }

    /**
     * Get all supported state codes
     * 
     * @param string $countryCode Two-letter country code (default: 'US')
     * @return array Array of all state codes
     */
    public function getSupportedStates($countryCode = 'US')
    {
        $rates = StateTaxRate::getActiveRatesByCountry(strtoupper($countryCode));
        return array_keys($rates);
    }

    /**
     * Get all active tax rates by country
     * 
     * @param string $countryCode Two-letter country code (default: 'US')
     * @return array Array of tax rate information
     */
    public function getAllTaxRates($countryCode = 'US')
    {
        return StateTaxRate::getActiveRatesByCountry(strtoupper($countryCode));
    }

    /**
     * Get fallback tax rate when database is unavailable
     * 
     * @param string $stateCode Two-letter state code
     * @param bool $useLocalTax Whether to include local tax rates
     * @return float Tax rate as percentage
     */
    protected function getFallbackTaxRate($stateCode, $useLocalTax = true)
    {
        // Fallback hardcoded rates for when StateTaxRate table is empty
        $fallbackRates = [
            'AL' => [4.00, true, 9.24],    // Alabama
            'AK' => [0.00, true, 1.76],    // Alaska
            'AZ' => [5.60, true, 8.40],    // Arizona  
            'AR' => [6.50, true, 9.47],    // Arkansas
            'CA' => [6.00, true, 8.85],    // California
            'CO' => [2.90, true, 7.86],    // Colorado
            'CT' => [6.35, false, 6.35],   // Connecticut
            'DE' => [0.00, false, 0.00],   // Delaware - No sales tax
            'FL' => [6.00, true, 7.02],    // Florida
            'GA' => [4.00, true, 7.31],    // Georgia
            'HI' => [4.00, true, 4.44],    // Hawaii
            'ID' => [6.00, true, 6.03],    // Idaho
            'IL' => [6.25, true, 8.64],    // Illinois
            'IN' => [7.00, false, 7.00],   // Indiana
            'IA' => [6.00, true, 6.94],    // Iowa
            'KS' => [6.50, true, 8.68],    // Kansas
            'KY' => [6.00, false, 6.00],   // Kentucky
            'LA' => [4.45, true, 9.56],    // Louisiana
            'ME' => [5.50, false, 5.50],   // Maine
            'MD' => [6.00, false, 6.00],   // Maryland
            'MA' => [6.25, false, 6.25],   // Massachusetts
            'MI' => [6.00, false, 6.00],   // Michigan
            'MN' => [6.88, true, 7.46],    // Minnesota
            'MS' => [7.00, true, 7.07],    // Mississippi
            'MO' => [4.23, true, 8.30],    // Missouri
            'MT' => [0.00, false, 0.00],   // Montana - No sales tax
            'NE' => [5.50, true, 6.94],    // Nebraska
            'NV' => [4.60, true, 8.23],    // Nevada
            'NH' => [0.00, false, 0.00],   // New Hampshire - No sales tax
            'NJ' => [6.63, false, 6.63],   // New Jersey
            'NM' => [5.13, true, 7.69],    // New Mexico
            'NY' => [4.00, true, 8.54],    // New York
            'NC' => [4.75, true, 6.98],    // North Carolina
            'ND' => [5.00, true, 6.86],    // North Dakota
            'OH' => [5.75, true, 7.26],    // Ohio
            'OK' => [4.50, true, 9.05],    // Oklahoma
            'OR' => [0.00, false, 0.00],   // Oregon - No sales tax
            'PA' => [6.00, true, 6.34],    // Pennsylvania
            'RI' => [7.00, false, 7.00],   // Rhode Island
            'SC' => [6.00, true, 7.46],    // South Carolina
            'SD' => [4.20, true, 6.40],    // South Dakota
            'TN' => [7.00, true, 9.55],    // Tennessee
            'TX' => [6.25, true, 8.20],    // Texas
            'UT' => [4.85, true, 7.10],    // Utah
            'VT' => [6.00, true, 6.24],    // Vermont
            'VA' => [4.30, true, 5.75],    // Virginia
            'WA' => [6.50, true, 9.23],    // Washington
            'WV' => [6.00, true, 6.48],    // West Virginia
            'WI' => [5.00, true, 5.44],    // Wisconsin
            'WY' => [4.00, true, 5.36],    // Wyoming
        ];

        if (!isset($fallbackRates[$stateCode])) {
            return 0.0; // Unknown state, no tax
        }

        $stateData = $fallbackRates[$stateCode];
        $baseRate = $stateData[0];
        $hasLocalTax = $stateData[1];
        $avgTotalRate = $stateData[2];

        // If using local tax and state has local tax, return average total rate
        if ($useLocalTax && $hasLocalTax) {
            return $avgTotalRate;
        }

        // Otherwise return base state rate
        return $baseRate;
    }
}