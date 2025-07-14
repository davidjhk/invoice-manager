<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "jdosa_tax_jurisdictions".
 *
 * @property int $id
 * @property string $zip_code
 * @property string $state_code
 * @property string|null $state_name
 * @property string|null $county_name
 * @property string|null $city_name
 * @property string|null $tax_region_name
 * @property float $state_rate
 * @property float $county_rate
 * @property float $city_rate
 * @property float $special_rate
 * @property float $combined_rate
 * @property int|null $estimated_population
 * @property string|null $tax_authority
 * @property string|null $jurisdiction_code
 * @property string $data_source
 * @property string $effective_date
 * @property string|null $expiry_date
 * @property bool $is_active
 * @property int|null $data_year
 * @property int|null $data_month
 * @property string|null $last_verified
 * @property string|null $notes
 * @property string $created_at
 * @property string $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class TaxJurisdiction extends ActiveRecord
{
    const DATA_SOURCE_MANUAL = 'manual';
    const DATA_SOURCE_API = 'api';
    const DATA_SOURCE_IMPORT = 'import';
    const DATA_SOURCE_AVALARA = 'avalara';
    const DATA_SOURCE_TAXJAR = 'taxjar';
    const DATA_SOURCE_ZIP2TAX = 'zip2tax';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_tax_jurisdictions';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => function() {
                    return date('Y-m-d H:i:s');
                },
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'defaultValue' => null,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['zip_code', 'state_code', 'combined_rate', 'effective_date'], 'required'],
            [['state_rate', 'county_rate', 'city_rate', 'special_rate', 'combined_rate'], 'number', 'min' => 0, 'max' => 99.9999],
            [['estimated_population', 'data_year', 'data_month', 'created_by', 'updated_by'], 'integer'],
            [['effective_date', 'expiry_date', 'last_verified'], 'date', 'format' => 'php:Y-m-d'],
            [['is_active'], 'boolean'],
            [['notes'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['zip_code'], 'string', 'max' => 10],
            [['state_code'], 'string', 'max' => 2],
            [['state_name'], 'string', 'max' => 50],
            [['county_name', 'city_name'], 'string', 'max' => 100],
            [['tax_region_name', 'tax_authority'], 'string', 'max' => 200],
            [['jurisdiction_code', 'data_source'], 'string', 'max' => 50],
            [['data_source'], 'in', 'range' => [
                self::DATA_SOURCE_MANUAL, 
                self::DATA_SOURCE_API, 
                self::DATA_SOURCE_IMPORT,
                self::DATA_SOURCE_AVALARA,
                self::DATA_SOURCE_TAXJAR,
                self::DATA_SOURCE_ZIP2TAX
            ]],
            [['state_code'], 'match', 'pattern' => '/^[A-Z]{2}$/'],
            [['zip_code'], 'match', 'pattern' => '/^\d{5}(-\d{4})?$/'],
            [['data_year'], 'integer', 'min' => 2020, 'max' => 2030],
            [['data_month'], 'integer', 'min' => 1, 'max' => 12],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'zip_code' => 'ZIP Code',
            'state_code' => 'State Code',
            'state_name' => 'State Name',
            'county_name' => 'County Name',
            'city_name' => 'City Name',
            'tax_region_name' => 'Tax Region Name',
            'state_rate' => 'State Rate (%)',
            'county_rate' => 'County Rate (%)',
            'city_rate' => 'City Rate (%)',
            'special_rate' => 'Special Rate (%)',
            'combined_rate' => 'Combined Rate (%)',
            'estimated_population' => 'Estimated Population',
            'tax_authority' => 'Tax Authority',
            'jurisdiction_code' => 'Jurisdiction Code',
            'data_source' => 'Data Source',
            'effective_date' => 'Effective Date',
            'expiry_date' => 'Expiry Date',
            'is_active' => 'Is Active',
            'data_year' => 'Data Year',
            'data_month' => 'Data Month',
            'last_verified' => 'Last Verified',
            'notes' => 'Notes',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Get data source options
     *
     * @return array
     */
    public static function getDataSourceOptions()
    {
        return [
            self::DATA_SOURCE_MANUAL => 'Manual Entry',
            self::DATA_SOURCE_API => 'API',
            self::DATA_SOURCE_IMPORT => 'CSV Import',
            self::DATA_SOURCE_AVALARA => 'Avalara',
            self::DATA_SOURCE_TAXJAR => 'TaxJar',
            self::DATA_SOURCE_ZIP2TAX => 'Zip2Tax',
        ];
    }

    /**
     * Find tax rate by ZIP code
     *
     * @param string $zipCode
     * @param string|null $date Optional date for historical rates
     * @return static|null
     */
    public static function findByZipCode($zipCode, $date = null)
    {
        $query = static::find()
            ->where(['zip_code' => $zipCode, 'is_active' => true]);
        
        if ($date) {
            $query->andWhere(['<=', 'effective_date', $date])
                  ->andWhere(['or', ['expiry_date' => null], ['>', 'expiry_date', $date]]);
        } else {
            $query->andWhere(['<=', 'effective_date', date('Y-m-d')])
                  ->andWhere(['or', ['expiry_date' => null], ['>', 'expiry_date', date('Y-m-d')]]);
        }
        
        return $query->orderBy(['effective_date' => SORT_DESC])->one();
    }

    /**
     * Find tax rates by state
     *
     * @param string $stateCode
     * @param bool $activeOnly
     * @return \yii\db\ActiveQuery
     */
    public static function findByState($stateCode, $activeOnly = true)
    {
        $query = static::find()->where(['state_code' => stateCode]);
        
        if ($activeOnly) {
            $query->andWhere(['is_active' => true]);
        }
        
        return $query->orderBy(['zip_code' => SORT_ASC]);
    }

    /**
     * Get current tax rate for a ZIP code
     *
     * @param string $zipCode
     * @return float
     */
    public static function getTaxRate($zipCode)
    {
        $jurisdiction = static::findByZipCode($zipCode);
        return $jurisdiction ? $jurisdiction->combined_rate : 0.0;
    }

    /**
     * Get detailed tax breakdown for a ZIP code
     *
     * @param string $zipCode
     * @return array|null
     */
    public static function getTaxBreakdown($zipCode)
    {
        $jurisdiction = static::findByZipCode($zipCode);
        
        if (!$jurisdiction) {
            return null;
        }
        
        return [
            'zip_code' => $jurisdiction->zip_code,
            'state_code' => $jurisdiction->state_code,
            'state_rate' => $jurisdiction->state_rate,
            'county_rate' => $jurisdiction->county_rate,
            'city_rate' => $jurisdiction->city_rate,
            'special_rate' => $jurisdiction->special_rate,
            'combined_rate' => $jurisdiction->combined_rate,
            'tax_region_name' => $jurisdiction->tax_region_name,
            'effective_date' => $jurisdiction->effective_date,
        ];
    }

    /**
     * Import tax rates from CSV data
     *
     * @param array $csvData
     * @param string $dataSource
     * @return array Import results
     */
    public static function importFromCsv($csvData, $dataSource = self::DATA_SOURCE_IMPORT)
    {
        $results = ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];
        
        foreach ($csvData as $rowIndex => $row) {
            try {
                // Normalize column names based on data source
                $normalizedRow = static::normalizeRowData($row, $dataSource);
                
                // Validate required fields
                if (empty($normalizedRow['zip_code'])) {
                    $results['errors'][] = "Row " . ($rowIndex + 1) . ": Missing ZIP code. Raw data: " . json_encode($row);
                    continue;
                }
                
                if (empty($normalizedRow['state_code'])) {
                    $results['errors'][] = "Row " . ($rowIndex + 1) . ": Missing state code. Raw data: " . json_encode($row);
                    continue;
                }
                
                if (!is_numeric($normalizedRow['combined_rate']) || $normalizedRow['combined_rate'] < 0) {
                    $results['errors'][] = "Row " . ($rowIndex + 1) . ": Invalid combined rate '{$normalizedRow['combined_rate']}'. Raw data: " . json_encode($row);
                    continue;
                }
                
                // Check for existing record with same zip_code, effective_date, and is_active=true
                $effectiveDate = $normalizedRow['effective_date'] ?? date('Y-m-d');
                $existingJurisdiction = static::find()
                    ->where([
                        'zip_code' => $normalizedRow['zip_code'],
                        'effective_date' => $effectiveDate,
                        'is_active' => true
                    ])
                    ->one();
                
                // Skip if duplicate record already exists
                if ($existingJurisdiction !== null) {
                    $results['skipped']++;
                    continue;
                }
                
                // Create new record
                $jurisdiction = new static();
                
                // Set attributes with better error handling
                $jurisdiction->zip_code = trim($normalizedRow['zip_code']);
                $jurisdiction->state_code = strtoupper(trim($normalizedRow['state_code']));
                $jurisdiction->state_name = !empty($normalizedRow['state_name']) ? trim($normalizedRow['state_name']) : null;
                $jurisdiction->county_name = !empty($normalizedRow['county_name']) ? trim($normalizedRow['county_name']) : null;
                $jurisdiction->city_name = !empty($normalizedRow['city_name']) ? trim($normalizedRow['city_name']) : null;
                $jurisdiction->tax_region_name = !empty($normalizedRow['tax_region_name']) ? trim($normalizedRow['tax_region_name']) : null;
                $jurisdiction->combined_rate = (float)$normalizedRow['combined_rate'];
                $jurisdiction->state_rate = (float)($normalizedRow['state_rate'] ?? 0);
                $jurisdiction->county_rate = (float)($normalizedRow['county_rate'] ?? 0);
                $jurisdiction->city_rate = (float)($normalizedRow['city_rate'] ?? 0);
                $jurisdiction->special_rate = (float)($normalizedRow['special_rate'] ?? 0);
                $jurisdiction->data_source = $dataSource;
                $jurisdiction->effective_date = $effectiveDate;
                $jurisdiction->data_year = $normalizedRow['data_year'] ?? date('Y');
                $jurisdiction->data_month = $normalizedRow['data_month'] ?? date('n');
                $jurisdiction->last_verified = date('Y-m-d');
                $jurisdiction->is_active = true;
                
                if ($jurisdiction->save()) {
                    $results['imported']++;
                } else {
                    $errorDetails = [];
                    foreach ($jurisdiction->errors as $attribute => $errors) {
                        $errorDetails[] = "$attribute: " . implode(', ', $errors);
                    }
                    $results['errors'][] = "Row " . ($rowIndex + 1) . " (ZIP: {$jurisdiction->zip_code}): " . implode('; ', $errorDetails) . ". Raw data: " . json_encode($row);
                }
                
            } catch (\Exception $e) {
                $results['errors'][] = "Row " . ($rowIndex + 1) . ": " . $e->getMessage() . ". Raw data: " . json_encode($row);
            }
        }
        
        return $results;
    }

    /**
     * Normalize CSV row data to handle different formats based on data source
     *
     * @param array $row
     * @param string $dataSource
     * @return array Normalized row data
     */
    public static function normalizeRowData($row, $dataSource = self::DATA_SOURCE_IMPORT)
    {
        $normalized = [];
        
        // Convert all keys to lowercase for easier matching
        $lowercaseRow = array_change_key_case($row, CASE_LOWER);
        
        if ($dataSource === self::DATA_SOURCE_AVALARA) {
            // Avalara specific mapping
            $normalized = static::normalizeAvalaraRow($lowercaseRow);
        } else {
            // Generic/Standard CSV mapping
            $normalized = static::normalizeStandardRow($lowercaseRow);
        }
        
        // Common post-processing for all formats
        $normalized = static::addCommonFields($normalized, $lowercaseRow);
        
        return $normalized;
    }
    
    /**
     * Normalize Avalara specific CSV format
     *
     * @param array $lowercaseRow
     * @return array
     */
    private static function normalizeAvalaraRow($lowercaseRow)
    {
        return [
            'zip_code' => $lowercaseRow['zipcode'] ?? null,
            'state_code' => $lowercaseRow['state'] ?? null,
            'state_name' => null, // Avalara doesn't include state name in their standard format
            'county_name' => null, // Not included in Avalara format
            'city_name' => null, // Not included in Avalara format
            'tax_region_name' => $lowercaseRow['taxregionname'] ?? null,
            'combined_rate' => $lowercaseRow['estimatedcombinedrate'] ?? 0,
            'state_rate' => $lowercaseRow['staterate'] ?? 0,
            'county_rate' => $lowercaseRow['estimatedcountyrate'] ?? 0,
            'city_rate' => $lowercaseRow['estimatedcityrate'] ?? 0,
            'special_rate' => $lowercaseRow['estimatedspecialrate'] ?? 0,
        ];
    }
    
    /**
     * Normalize standard/generic CSV format
     *
     * @param array $lowercaseRow
     * @return array
     */
    private static function normalizeStandardRow($lowercaseRow)
    {
        return [
            'zip_code' => $lowercaseRow['zip_code'] ?? 
                          $lowercaseRow['zipcode'] ?? 
                          $lowercaseRow['zip'] ?? 
                          null,
            'state_code' => $lowercaseRow['state_code'] ?? 
                           $lowercaseRow['state'] ?? 
                           null,
            'state_name' => $lowercaseRow['state_name'] ?? 
                           $lowercaseRow['statename'] ?? 
                           null,
            'county_name' => $lowercaseRow['county_name'] ?? 
                            $lowercaseRow['countyname'] ?? 
                            $lowercaseRow['county'] ?? 
                            null,
            'city_name' => $lowercaseRow['city_name'] ?? 
                          $lowercaseRow['cityname'] ?? 
                          $lowercaseRow['city'] ?? 
                          null,
            'tax_region_name' => $lowercaseRow['tax_region_name'] ?? 
                                $lowercaseRow['taxregionname'] ?? 
                                $lowercaseRow['region_name'] ?? 
                                $lowercaseRow['regionname'] ?? 
                                null,
            'combined_rate' => $lowercaseRow['combined_rate'] ?? 
                              $lowercaseRow['total_rate'] ?? 
                              $lowercaseRow['totalrate'] ?? 
                              0,
            'state_rate' => $lowercaseRow['state_rate'] ?? 
                           $lowercaseRow['staterate'] ?? 
                           0,
            'county_rate' => $lowercaseRow['county_rate'] ?? 
                            $lowercaseRow['countyrate'] ?? 
                            0,
            'city_rate' => $lowercaseRow['city_rate'] ?? 
                          $lowercaseRow['cityrate'] ?? 
                          0,
            'special_rate' => $lowercaseRow['special_rate'] ?? 
                             $lowercaseRow['specialrate'] ?? 
                             0,
        ];
    }
    
    /**
     * Add common fields to normalized data
     *
     * @param array $normalized
     * @param array $lowercaseRow
     * @return array
     */
    private static function addCommonFields($normalized, $lowercaseRow)
    {
        // Optional fields
        $normalized['effective_date'] = $lowercaseRow['effective_date'] ?? 
                                       $lowercaseRow['effectivedate'] ?? 
                                       date('Y-m-d');
        
        $normalized['data_year'] = $lowercaseRow['data_year'] ?? 
                                  $lowercaseRow['year'] ?? 
                                  date('Y');
        
        $normalized['data_month'] = $lowercaseRow['data_month'] ?? 
                                   $lowercaseRow['month'] ?? 
                                   date('n');
        
        // Extract year and month from filename pattern if present (common in Avalara files)
        if (!isset($lowercaseRow['data_year']) && !isset($lowercaseRow['year'])) {
            // Look for YYYYMM pattern in any field (e.g., CA202507 in Avalara filenames)
            foreach ($lowercaseRow as $value) {
                if (is_string($value) && preg_match('/[A-Z]*([0-9]{4})([0-9]{2})/', $value, $matches)) {
                    $year = (int)$matches[1];
                    $month = (int)$matches[2];
                    
                    // Validate year and month ranges
                    if ($year >= 2020 && $year <= 2030 && $month >= 1 && $month <= 12) {
                        $normalized['data_year'] = $year;
                        $normalized['data_month'] = $month;
                        break;
                    }
                }
            }
        }
        
        return $normalized;
    }

    /**
     * Get statistics about tax jurisdictions
     *
     * @return array
     */
    public static function getStatistics()
    {
        return [
            'total_jurisdictions' => static::find()->count(),
            'active_jurisdictions' => static::find()->where(['is_active' => true])->count(),
            'states_covered' => static::find()->select('state_code')->distinct()->count(),
            'data_sources' => static::find()->select('data_source')->distinct()->all(),
            'last_updated' => static::find()->max('updated_at'),
            'latest_effective_date' => static::find()->max('effective_date'),
        ];
    }

    /**
     * Deactivate old rates when importing new ones
     *
     * @param string $dataSource
     * @param string $effectiveDate
     */
    public static function deactivateOldRates($dataSource, $effectiveDate)
    {
        static::updateAll(
            ['is_active' => false], 
            ['and', 
                ['data_source' => $dataSource], 
                ['<', 'effective_date', $effectiveDate]
            ]
        );
    }

    /**
     * Format rate as percentage
     *
     * @param float $rate
     * @return string
     */
    public static function formatRate($rate)
    {
        return number_format($rate * 100, 2) . '%';
    }

    /**
     * Get formatted combined rate
     *
     * @return string
     */
    public function getFormattedCombinedRate()
    {
        return static::formatRate($this->combined_rate);
    }

    /**
     * Check if jurisdiction has expired
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date < date('Y-m-d');
    }

    /**
     * Check if jurisdiction needs verification
     *
     * @param int $daysThreshold
     * @return bool
     */
    public function needsVerification($daysThreshold = 90)
    {
        if (!$this->last_verified) {
            return true;
        }
        
        $verifiedDate = new \DateTime($this->last_verified);
        $threshold = new \DateTime("-{$daysThreshold} days");
        
        return $verifiedDate < $threshold;
    }

    /**
     * Get actual state tax rate from StateTaxRate table (used in automatic calculations)
     *
     * @param bool $useLocalTax Whether to include local taxes in the rate
     * @return float
     */
    public function getActualStateTaxRate($useLocalTax = false)
    {
        if (!class_exists('app\models\StateTaxRate')) {
            return $this->state_rate; // Fallback to stored rate
        }

        try {
            $stateTaxRate = \app\models\StateTaxRate::getCurrentRate($this->state_code, 'US');
            
            if ($stateTaxRate) {
                if ($useLocalTax && $stateTaxRate->has_local_tax) {
                    return (float) $stateTaxRate->average_total_rate;
                } else {
                    return (float) $stateTaxRate->base_rate;
                }
            }
        } catch (\Exception $e) {
            // Log error and fall back to stored rate
            Yii::error("Error getting actual state tax rate for {$this->state_code}: " . $e->getMessage());
        }

        return $this->state_rate; // Fallback to stored rate
    }

    /**
     * Get formatted actual state tax rate as percentage
     *
     * @param bool $useLocalTax Whether to include local taxes in the rate
     * @return string
     */
    public function getFormattedActualStateTaxRate($useLocalTax = false)
    {
        return static::formatRate($this->getActualStateTaxRate($useLocalTax));
    }

    /**
     * Check if actual state tax rate differs from stored rate
     *
     * @return bool
     */
    public function hasStateTaxRateMismatch()
    {
        $actualRate = $this->getActualStateTaxRate(false);
        $storedRate = $this->state_rate;
        
        // Consider rates different if they differ by more than 0.0001%
        return abs($actualRate - $storedRate * 100) > 0.0001;
    }

    /**
     * Get state tax rate information for display
     *
     * @return array
     */
    public function getStateTaxRateInfo()
    {
        $actualRate = $this->getActualStateTaxRate(false) / 100;
        $actualRateWithLocal = $this->getActualStateTaxRate(true);
        $storedRate = $this->state_rate;
        $hasMismatch = $this->hasStateTaxRateMismatch();

        return [
            'actual_base_rate' => $actualRate,
            'actual_rate_with_local' => $actualRateWithLocal,
            'stored_rate' => $storedRate,
            'has_mismatch' => $hasMismatch,
            'formatted_actual_base' => static::formatRate($actualRate),
            'formatted_actual_with_local' => static::formatRate($actualRateWithLocal),
            'formatted_stored' => static::formatRate($storedRate),
        ];
    }
}