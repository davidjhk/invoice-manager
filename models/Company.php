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
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip_code
 * @property string|null $country
 * @property string|null $company_phone
 * @property string|null $company_email
 * @property string|null $logo_path
 * @property string|null $logo_filename
 * @property string|null $smtp2go_api_key
 * @property string|null $sender_email
 * @property string|null $sender_name
 * @property string|null $bcc_email
 * @property float $tax_rate
 * @property string $currency
 * @property string $invoice_prefix
 * @property string $estimate_prefix
 * @property int $due_date_days
 * @property int $estimate_validity_days
 * @property bool $is_active
 * @property bool $dark_mode
 * @property bool $use_cjk_font
 * @property string|null $pdf_template
 * @property bool $compact_mode
 * @property bool $hide_footer
 * @property string $language
 * @property int|null $user_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Customer[] $customers
 * @property Invoice[] $invoices
 * @property User $user
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
            [['city'], 'string', 'max' => 100],
            [['state'], 'string', 'max' => 2],
            [['zip_code'], 'string', 'max' => 10],
            [['country'], 'string', 'max' => 2],
            [['country'], 'default', 'value' => 'US'],
            [['tax_rate'], 'number', 'min' => 0, 'max' => 100],
            [['due_date_days', 'estimate_validity_days', 'user_id'], 'integer', 'min' => 1],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
            [['is_active', 'dark_mode', 'use_cjk_font', 'compact_mode', 'hide_footer'], 'boolean'],
            [['dark_mode', 'use_cjk_font', 'compact_mode', 'hide_footer'], 'filter', 'filter' => function($value) {
                return $value ? 1 : 0;
            }],
            [['language'], 'string', 'max' => 10],
            [['language'], 'in', 'range' => ['en-US', 'es-ES', 'ko-KR', 'zh-CN', 'zh-TW']],
            [['language'], 'default', 'value' => 'en-US'],
            [['created_at', 'updated_at'], 'safe'],
            [['company_name'], 'string', 'max' => 255],
            [['company_phone'], 'string', 'max' => 50],
            [['company_email', 'smtp2go_api_key', 'sender_email', 'sender_name', 'bcc_email'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 10],
            [['invoice_prefix', 'estimate_prefix'], 'string', 'max' => 20],
            [['company_email', 'sender_email', 'bcc_email'], 'email'],
            [['logo_path', 'logo_filename'], 'string', 'max' => 500],
            [['logo_upload'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, gif', 'maxSize' => 2 * 1024 * 1024],
            [['currency'], 'in', 'range' => ['USD', 'EUR', 'GBP', 'KRW']],
            [['pdf_template'], 'string', 'max' => 50],
            [['pdf_template'], 'default', 'value' => 'classic'],
            [['pdf_template'], 'in', 'range' => ['classic', 'modern', 'elegant', 'corporate', 'creative']],
            
            // Set default values
            [['tax_rate'], 'default', 'value' => 10.00],
            [['currency'], 'default', 'value' => 'USD'],
            [['invoice_prefix'], 'default', 'value' => 'INV'],
            [['estimate_prefix'], 'default', 'value' => 'EST'],
            [['due_date_days'], 'default', 'value' => 30],
            [['estimate_validity_days'], 'default', 'value' => 30],
            [['is_active'], 'default', 'value' => true],
            [['dark_mode'], 'default', 'value' => false],
            [['use_cjk_font'], 'default', 'value' => false],
            [['compact_mode'], 'default', 'value' => false],
            [['hide_footer'], 'default', 'value' => false],
            
            // Custom validation for company count limit
            [['user_id'], 'validateCompanyLimit'],
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
            'city' => 'City',
            'state' => 'State',
            'zip_code' => 'ZIP Code',
            'country' => 'Country',
            'company_phone' => 'Company Phone',
            'company_email' => 'Company Email',
            'logo_path' => 'Logo Path',
            'logo_filename' => 'Logo Filename',
            'logo_upload' => 'Company Logo',
            'smtp2go_api_key' => 'SMTP2GO API Key',
            'sender_email' => 'Sender Email',
            'sender_name' => 'Sender Name',
            'bcc_email' => 'BCC Email',
            'tax_rate' => 'Tax Rate (%)',
            'currency' => 'Currency',
            'invoice_prefix' => 'Invoice Prefix',
            'estimate_prefix' => 'Estimate Prefix',
            'due_date_days' => 'Due Date Days',
            'estimate_validity_days' => 'Estimate Validity Days',
            'is_active' => 'Is Active',
            'dark_mode' => 'Dark Mode',
            'use_cjk_font' => 'Use CJK Fonts for PDF',
            'pdf_template' => 'PDF Template',
            'compact_mode' => 'Compact Mode',
            'language' => 'Interface Language',
            'user_id' => 'Owner',
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
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Get the default company for current user
     *
     * @return Company|null
     */
    public static function getDefault()
    {
        $userId = Yii::$app->user->id;
        if ($userId) {
            return static::find()->where(['user_id' => $userId, 'is_active' => true])->one();
        }
        return static::find()->where(['is_active' => true])->one();
    }

    /**
     * Get the current selected company from session
     *
     * @return Company|null
     */
    public static function getCurrent()
    {
        $companyId = Yii::$app->session->get('current_company_id');
        if ($companyId) {
            return static::findForCurrentUser()->where(['id' => $companyId])->one();
        }
        
        // If no company is selected, get the first company for the user
        $company = static::findForCurrentUser()->one();
        if ($company) {
            Yii::$app->session->set('current_company_id', $company->id);
        }
        
        return $company;
    }

    /**
     * Get companies for current user
     *
     * @return \yii\db\ActiveQuery
     */
    public static function findForCurrentUser()
    {
        $userId = Yii::$app->user->id;
        if ($userId) {
            return static::find()->where(['user_id' => $userId, 'is_active' => true]);
        }
        return static::find()->where(['0' => '1']); // Return empty result if no user
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
     * Check if SMTP2GO is configured and email sending is available
     *
     * @return bool
     */
    public function hasEmailConfiguration()
    {
        return !empty($this->smtp2go_api_key) && !empty($this->sender_email);
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
        if (!empty($this->logo_path)) {
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
        return !empty($this->logo_path);
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
            // Try to delete the physical file if it exists locally
            if (file_exists($logoPath)) {
                unlink($logoPath);
            }
            // Always clear the database fields regardless of file existence
            $this->logo_path = null;
            $this->logo_filename = null;
            return true;
        }
        return false;
    }

    /**
     * Validate company count limit for user
     *
     * @param string $attribute
     * @param mixed $params
     */
    public function validateCompanyLimit($attribute, $params)
    {
        if (!$this->isNewRecord) {
            return; // Only validate on new records
        }
        
        if (!$this->user_id) {
            return; // Skip if no user_id
        }
        
        $user = User::findOne($this->user_id);
        if (!$user) {
            $this->addError($attribute, 'User not found.');
            return;
        }
        
        if (!$user->canCreateMoreCompanies()) {
            $this->addError($attribute, 'You have reached your maximum number of companies (' . $user->max_companies . '). Please upgrade your account or contact support.');
        }
    }

    /**
     * After save event - create default category for new companies
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Create default category for new companies
        if ($insert) {
            $this->createDefaultCategory();
        }
    }

    /**
     * Create default General category for this company
     */
    private function createDefaultCategory()
    {
        $category = new \app\models\ProductCategory();
        $category->company_id = $this->id;
        $category->name = 'General';
        $category->description = null;
        $category->is_active = true;
        $category->sort_order = 1;
        $category->save();
    }

    /**
     * Get available languages
     *
     * @return array
     */
    public static function getLanguageOptions()
    {
        return [
            'en-US' => 'English',
            'es-ES' => 'Español',
            'ko-KR' => '한국어',
            'zh-CN' => '简体中文',
            'zh-TW' => '繁體中文',
        ];
    }

    /**
     * Get current language name
     *
     * @return string
     */
    public function getLanguageName()
    {
        $languages = self::getLanguageOptions();
        return $languages[$this->language] ?? $languages['en-US'];
    }

    /**
     * Apply company language to application
     */
    public function applyLanguage()
    {
        if ($this->language && $this->language !== Yii::$app->language) {
            Yii::$app->language = $this->language;
        }
    }
}