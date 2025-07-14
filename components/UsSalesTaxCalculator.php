<?php

namespace app\components;

use yii\base\Component;

/**
 * US Sales Tax Calculator Component
 * 
 * Provides automatic sales tax calculation for US states based on:
 * - State base rates
 * - Economic nexus rules
 * - Tax jurisdiction lookup
 * 
 * @author Generated with Claude Code
 */
class UsSalesTaxCalculator extends Component
{
    /**
     * 2025 US State Sales Tax Rates
     * Format: [state_code => [base_rate, has_local_tax, avg_total_rate]]
     */
    private const STATE_TAX_RATES = [
        'AL' => [4.00, true, 9.24],    // Alabama
        'AK' => [0.00, true, 1.76],    // Alaska
        'AZ' => [5.60, true, 8.40],    // Arizona  
        'AR' => [6.50, true, 9.47],    // Arkansas
        'CA' => [6.00, true, 8.85],    // California (includes mandatory 1.25% local)
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
        'UT' => [4.85, true, 7.10],    // Utah (includes mandatory 1.25% local)
        'VT' => [6.00, true, 6.24],    // Vermont
        'VA' => [4.30, true, 5.75],    // Virginia (includes mandatory 1% local)
        'WA' => [6.50, true, 9.23],    // Washington
        'WV' => [6.00, true, 6.48],    // West Virginia
        'WI' => [5.00, true, 5.44],    // Wisconsin
        'WY' => [4.00, true, 5.36],    // Wyoming
    ];

    /**
     * Economic Nexus Thresholds for 2025
     * Format: [state_code => [revenue_threshold, transaction_threshold]]
     */
    private const NEXUS_THRESHOLDS = [
        'AL' => [250000, null],         // Alabama - $250k
        'AK' => [100000, null],         // Alaska - $100k (no transaction threshold as of 2025)
        'AZ' => [100000, null],         // Arizona - $100k
        'AR' => [100000, 200],          // Arkansas - $100k or 200 transactions
        'CA' => [500000, null],         // California - $500k
        'CO' => [100000, null],         // Colorado - $100k
        'CT' => [100000, 200],          // Connecticut - $100k or 200 transactions
        'FL' => [100000, null],         // Florida - $100k
        'GA' => [100000, 200],          // Georgia - $100k or 200 transactions
        'HI' => [100000, 200],          // Hawaii - $100k or 200 transactions
        'ID' => [100000, null],         // Idaho - $100k
        'IL' => [100000, 200],          // Illinois - $100k or 200 transactions
        'IN' => [100000, 200],          // Indiana - $100k or 200 transactions
        'IA' => [100000, 200],          // Iowa - $100k or 200 transactions
        'KS' => [100000, null],         // Kansas - $100k
        'KY' => [100000, 200],          // Kentucky - $100k or 200 transactions
        'LA' => [100000, 200],          // Louisiana - $100k or 200 transactions
        'ME' => [100000, 200],          // Maine - $100k or 200 transactions
        'MD' => [100000, 200],          // Maryland - $100k or 200 transactions
        'MA' => [100000, null],         // Massachusetts - $100k
        'MI' => [100000, 200],          // Michigan - $100k or 200 transactions
        'MN' => [100000, 200],          // Minnesota - $100k or 200 transactions
        'MS' => [250000, null],         // Mississippi - $250k
        'MO' => [100000, null],         // Missouri - $100k
        'NE' => [100000, 200],          // Nebraska - $100k or 200 transactions
        'NV' => [100000, 200],          // Nevada - $100k or 200 transactions
        'NJ' => [100000, 200],          // New Jersey - $100k or 200 transactions
        'NM' => [100000, null],         // New Mexico - $100k
        'NY' => [500000, 100],          // New York - $500k or 100 transactions
        'NC' => [100000, 200],          // North Carolina - $100k or 200 transactions
        'ND' => [100000, null],         // North Dakota - $100k
        'OH' => [100000, 200],          // Ohio - $100k or 200 transactions
        'OK' => [100000, null],         // Oklahoma - $100k
        'PA' => [100000, null],         // Pennsylvania - $100k
        'RI' => [100000, 200],          // Rhode Island - $100k or 200 transactions
        'SC' => [100000, null],         // South Carolina - $100k
        'SD' => [100000, 200],          // South Dakota - $100k or 200 transactions
        'TN' => [100000, null],         // Tennessee - $100k
        'TX' => [500000, null],         // Texas - $500k
        'UT' => [100000, 200],          // Utah - $100k or 200 transactions
        'VT' => [100000, 200],          // Vermont - $100k or 200 transactions
        'VA' => [100000, 200],          // Virginia - $100k or 200 transactions
        'WA' => [100000, null],         // Washington - $100k
        'WV' => [100000, 200],          // West Virginia - $100k or 200 transactions
        'WI' => [100000, null],         // Wisconsin - $100k
        'WY' => [100000, 200],          // Wyoming - $100k or 200 transactions
    ];

    /**
     * Calculate sales tax rate for a given state
     * 
     * @param string $stateCode Two-letter state code (e.g., 'CA', 'NY')
     * @param bool $useLocalTax Whether to include average local tax rates
     * @param string|null $zipCode ZIP code for more precise local tax lookup (future enhancement)
     * @return float Tax rate as percentage (e.g., 8.25 for 8.25%)
     */
    public function calculateTaxRate($stateCode, $useLocalTax = true, $zipCode = null)
    {
        $stateCode = strtoupper($stateCode);
        
        if (!isset(self::STATE_TAX_RATES[$stateCode])) {
            return 0.0; // Unknown state, no tax
        }
        
        $stateData = self::STATE_TAX_RATES[$stateCode];
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

    /**
     * Calculate sales tax amount
     * 
     * @param float $taxableAmount The taxable amount
     * @param string $stateCode Two-letter state code
     * @param bool $useLocalTax Whether to include local tax rates
     * @param string|null $zipCode ZIP code for precise calculation (future)
     * @return float Tax amount in dollars
     */
    public function calculateTaxAmount($taxableAmount, $stateCode, $useLocalTax = true, $zipCode = null)
    {
        $taxRate = $this->calculateTaxRate($stateCode, $useLocalTax, $zipCode);
        return round($taxableAmount * ($taxRate / 100), 2);
    }

    /**
     * Check if a business has economic nexus in a state
     * 
     * @param string $stateCode Two-letter state code
     * @param float $annualRevenue Annual revenue in the state
     * @param int $annualTransactions Annual number of transactions in the state
     * @return bool True if nexus threshold is met
     */
    public function hasEconomicNexus($stateCode, $annualRevenue, $annualTransactions = 0)
    {
        $stateCode = strtoupper($stateCode);
        
        if (!isset(self::NEXUS_THRESHOLDS[$stateCode])) {
            return false; // Unknown state
        }
        
        $thresholds = self::NEXUS_THRESHOLDS[$stateCode];
        $revenueThreshold = $thresholds[0];
        $transactionThreshold = $thresholds[1];
        
        // Check revenue threshold
        if ($annualRevenue >= $revenueThreshold) {
            return true;
        }
        
        // Check transaction threshold if it exists
        if ($transactionThreshold !== null && $annualTransactions >= $transactionThreshold) {
            return true;
        }
        
        return false;
    }

    /**
     * Get nexus requirements for a state
     * 
     * @param string $stateCode Two-letter state code
     * @return array|null Nexus thresholds or null if no sales tax
     */
    public function getNexusRequirements($stateCode)
    {
        $stateCode = strtoupper($stateCode);
        
        if (!isset(self::NEXUS_THRESHOLDS[$stateCode])) {
            return null;
        }
        
        $thresholds = self::NEXUS_THRESHOLDS[$stateCode];
        
        return [
            'revenue_threshold' => $thresholds[0],
            'transaction_threshold' => $thresholds[1],
            'has_sales_tax' => self::STATE_TAX_RATES[$stateCode][0] > 0,
        ];
    }

    /**
     * Get all states with no sales tax
     * 
     * @return array Array of state codes with no sales tax
     */
    public function getNoSalesTaxStates()
    {
        $noTaxStates = [];
        
        foreach (self::STATE_TAX_RATES as $stateCode => $data) {
            if ($data[0] == 0.0) { // Base rate is 0
                $noTaxStates[] = $stateCode;
            }
        }
        
        return $noTaxStates;
    }

    /**
     * Get detailed tax information for a state
     * 
     * @param string $stateCode Two-letter state code
     * @return array|null Detailed tax information
     */
    public function getStateTaxInfo($stateCode)
    {
        $stateCode = strtoupper($stateCode);
        
        if (!isset(self::STATE_TAX_RATES[$stateCode])) {
            return null;
        }
        
        $taxData = self::STATE_TAX_RATES[$stateCode];
        $nexusData = self::NEXUS_THRESHOLDS[$stateCode] ?? null;
        
        return [
            'state_code' => $stateCode,
            'base_rate' => $taxData[0],
            'has_local_tax' => $taxData[1],
            'average_total_rate' => $taxData[2],
            'revenue_threshold' => $nexusData ? $nexusData[0] : null,
            'transaction_threshold' => $nexusData ? $nexusData[1] : null,
        ];
    }

    /**
     * Calculate tax for multiple line items with different tax rules
     * 
     * @param array $lineItems Array of line items [amount, is_taxable, state_code]
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
            
            if (!$isTaxable || $amount <= 0) {
                continue;
            }
            
            $stateCode = strtoupper($stateCode);
            
            if (!isset($results['by_state'][$stateCode])) {
                $results['by_state'][$stateCode] = [
                    'taxable_amount' => 0,
                    'tax_rate' => $this->calculateTaxRate($stateCode, $useLocalTax),
                    'tax_amount' => 0,
                ];
            }
            
            $results['by_state'][$stateCode]['taxable_amount'] += $amount;
            $results['total_taxable'] += $amount;
        }
        
        // Calculate tax for each state
        foreach ($results['by_state'] as $stateCode => &$stateData) {
            $stateData['tax_amount'] = $this->calculateTaxAmount(
                $stateData['taxable_amount'], 
                $stateCode, 
                $useLocalTax
            );
            $results['total_tax'] += $stateData['tax_amount'];
        }
        
        return $results;
    }

    /**
     * Validate state code
     * 
     * @param string $stateCode Two-letter state code
     * @return bool True if valid state code
     */
    public function isValidStateCode($stateCode)
    {
        return isset(self::STATE_TAX_RATES[strtoupper($stateCode)]);
    }

    /**
     * Get all supported state codes
     * 
     * @return array Array of all state codes
     */
    public function getSupportedStates()
    {
        return array_keys(self::STATE_TAX_RATES);
    }
}