<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "jdosa_companies".
 *
 * @property int $id
 * @property string $company_name
 * @property string|null $company_address
 * @property string|null $company_phone
 * @property string|null $company_email
 * @property string|null $logo_path
 * @property string|null $logo_filename
 * @property string|null $smtp2go_api_key
 * @property string|null $sender_email
 * @property float $tax_rate
 * @property string $currency
 * @property string $invoice_prefix
 * @property string $estimate_prefix
 * @property int $due_date_days
 * @property int $estimate_validity_days
 * @property bool $is_active
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Customer[] $customers
 * @property Invoice[] $invoices
 */
class Company extends ActiveRecord
{
    /**
     * @var \yii\web\UploadedFile
     */
    public $logo_upload;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_companies';
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_name'], 'required'],
            [['company_address'], 'string'],
            [['tax_rate'], 'number', 'min' => 0, 'max' => 100],
            [['due_date_days', 'estimate_validity_days'], 'integer', 'min' => 1],
            [['is_active'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['company_name'], 'string', 'max' => 255],
            [['company_phone'], 'string', 'max' => 50],
            [['company_email', 'smtp2go_api_key', 'sender_email'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 10],
            [['invoice_prefix', 'estimate_prefix'], 'string', 'max' => 20],
            [['company_email', 'sender_email'], 'email'],
            [['logo_path', 'logo_filename'], 'string', 'max' => 500],
            [['logo_upload'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, gif', 'maxSize' => 2 * 1024 * 1024],
            [['currency'], 'in', 'range' => ['USD', 'EUR', 'GBP', 'KRW']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_name' => 'Company Name',
            'company_address' => 'Company Address',
            'company_phone' => 'Company Phone',
            'company_email' => 'Company Email',
            'logo_path' => 'Logo Path',
            'logo_filename' => 'Logo Filename',
            'logo_upload' => 'Company Logo',
            'smtp2go_api_key' => 'SMTP2GO API Key',
            'sender_email' => 'Sender Email',
            'tax_rate' => 'Tax Rate (%)',
            'currency' => 'Currency',
            'invoice_prefix' => 'Invoice Prefix',
            'estimate_prefix' => 'Estimate Prefix',
            'due_date_days' => 'Due Date Days',
            'estimate_validity_days' => 'Estimate Validity Days',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Customers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::class, ['company_id' => 'id']);
    }

    /**
     * Gets query for [[Invoices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoices()
    {
        return $this->hasMany(Invoice::class, ['company_id' => 'id']);
    }

    /**
     * Get the default company
     *
     * @return Company|null
     */
    public static function getDefault()
    {
        return static::find()->where(['is_active' => true])->one();
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'KRW' => '₩',
        ];
        return $symbols[$this->currency] ?? '$';
    }

    /**
     * Generate next invoice number (prefix + year + 4-digit, roll over after 9999, no year reset)
     * 중복 방지 포함
     * @return string
     */
    public function generateInvoiceNumber()
    {
        $year = date('Y');
        $prefix = $this->invoice_prefix ?: 'INV-';
        $pattern = $prefix . $year . '-';

        // 회사 전체에서 가장 마지막 번호를 찾음 (연도 상관없이)
        $lastInvoice = Invoice::find()
            ->where(['company_id' => $this->id])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $number = 1;
        if ($lastInvoice) {
            if (preg_match('/\d{4}$/', $lastInvoice->invoice_number, $matches)) {
                $number = intval($matches[0]) + 1;
                if ($number > 9999) {
                    $number = 1;
                }
            }
        }

        // 중복 방지: 실제로 존재하는지 확인
        do {
            $invoiceNumber = $pattern . str_pad($number, 4, '0', STR_PAD_LEFT);
            $exists = Invoice::find()->where(['company_id' => $this->id, 'invoice_number' => $invoiceNumber])->exists();
            if ($exists) {
                $number++;
                if ($number > 9999) {
                    $number = 1;
                }
            }
        } while ($exists);

        return $invoiceNumber;
    }

    /**
     * Generate next estimate number (prefix + year + 4-digit, roll over after 9999, no year reset)
     * 중복 방지 포함
     * @return string
     */
    public function generateEstimateNumber()
    {
        $year = date('Y');
        $prefix = $this->estimate_prefix ?: 'EST-';
        $pattern = $prefix . $year . '-';

        $lastEstimate = Estimate::find()
            ->where(['company_id' => $this->id])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $number = 1;
        if ($lastEstimate) {
            if (preg_match('/\d{4}$/', $lastEstimate->estimate_number, $matches)) {
                $number = intval($matches[0]) + 1;
                if ($number > 9999) {
                    $number = 1;
                }
            }
        }

        do {
            $estimateNumber = $pattern . str_pad($number, 4, '0', STR_PAD_LEFT);
            $exists = Estimate::find()->where(['company_id' => $this->id, 'estimate_number' => $estimateNumber])->exists();
            if ($exists) {
                $number++;
                if ($number > 9999) {
                    $number = 1;
                }
            }
        } while ($exists);

        return $estimateNumber;
    }

    /**
     * Get default due date
     *
     * @return string
     */
    public function getDefaultDueDate()
    {
        return date('Y-m-d', strtotime('+' . $this->due_date_days . ' days'));
    }

    /**
     * Get default estimate expiry date
     *
     * @return string
     */
    public function getDefaultExpiryDate()
    {
        // Default to 30 days if not set or invalid
        $days = $this->estimate_validity_days > 0 ? $this->estimate_validity_days : 30;
        return date('Y-m-d', strtotime('+' . $days . ' days'));
    }

    /**
     * Format amount with currency symbol
     *
     * @param float $amount
     * @return string
     */
    public function formatAmount($amount)
    {
        return $this->getCurrencySymbol() . number_format($amount, 2);
    }

    /**
     * Get logo URL
     *
     * @return string|null
     */
    public function getLogoUrl()
    {
        if (!empty($this->logo_path) && file_exists(Yii::getAlias('@webroot') . $this->logo_path)) {
            return Yii::getAlias('@web') . $this->logo_path;
        }
        return null;
    }

    /**
     * Get logo absolute path
     *
     * @return string|null
     */
    public function getLogoAbsolutePath()
    {
        if (!empty($this->logo_path) && file_exists(Yii::getAlias('@webroot') . $this->logo_path)) {
            return Yii::getAlias('@webroot') . $this->logo_path;
        }
        return null;
    }

    /**
     * Check if company has logo
     *
     * @return bool
     */
    public function hasLogo()
    {
        return !empty($this->logo_path) && file_exists(Yii::getAlias('@webroot') . $this->logo_path);
    }

    /**
     * Delete existing logo file
     *
     * @return bool
     */
    public function deleteLogo()
    {
        if ($this->hasLogo()) {
            $logoPath = Yii::getAlias('@webroot') . $this->logo_path;
            if (file_exists($logoPath)) {
                unlink($logoPath);
            }
            $this->logo_path = null;
            $this->logo_filename = null;
            return true;
        }
        return false;
    }
}