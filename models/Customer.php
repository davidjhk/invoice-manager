<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "jdosa_customers".
 *
 * @property int $id
 * @property string $customer_name
 * @property string|null $customer_address
 * @property string|null $payment_terms
 * @property string|null $customer_phone
 * @property string|null $customer_fax
 * @property string|null $customer_mobile
 * @property string|null $customer_email
 * @property string|null $contact_name
 * @property string|null $billing_address
 * @property string|null $shipping_address
 * @property int $company_id
 * @property bool $is_active
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Company $company
 * @property Invoice[] $invoices
 */
class Customer extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_customers';
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
            [['customer_name', 'company_id'], 'required'],
            [['company_id'], 'integer'],
            [['is_active'], 'boolean'],
            [['customer_address'], 'string'],
            [['payment_terms'], 'string', 'max' => 50],
            [['payment_terms'], 'default', 'value' => 'Net 30'],
            [['created_at', 'updated_at'], 'safe'],
            [['customer_name', 'contact_name'], 'string', 'max' => 255],
            [['customer_phone', 'customer_fax', 'customer_mobile'], 'string', 'max' => 50],
            [['customer_phone', 'customer_fax', 'customer_mobile'], 'match', 'pattern' => '/^[\+\-\s\(\)\d\.\#\*]*$/', 'message' => Yii::t('app/customer', 'Phone number can only contain numbers, spaces, parentheses, plus signs, hyphens, periods, hash and asterisk symbols.'), 'skipOnEmpty' => true],
            [['customer_email'], 'string', 'max' => 255],
            [['billing_address', 'shipping_address'], 'string'],
            [['customer_email'], 'email'],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_name' => 'Customer Name',
            'customer_address' => 'Customer Address',
            'payment_terms' => 'Payment Terms',
            'customer_phone' => 'Phone',
            'customer_fax' => 'Fax',
            'customer_mobile' => 'Mobile',
            'customer_email' => 'Email',
            'contact_name' => 'Contact Name',
            'billing_address' => 'Billing Address',
            'shipping_address' => 'Shipping Address',
            'company_id' => 'Company ID',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Company]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * Gets query for [[Invoices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoices()
    {
        return $this->hasMany(Invoice::class, ['customer_id' => 'id']);
    }

    /**
     * Get active customers for a company
     *
     * @param int $companyId
     * @return \yii\db\ActiveQuery
     */
    public static function findActiveByCompany($companyId)
    {
        return static::find()
            ->where(['company_id' => $companyId, 'is_active' => true])
            ->orderBy(['customer_name' => SORT_ASC]);
    }

    /**
     * Search customers
     *
     * @param string $term
     * @param int $companyId
     * @return \yii\db\ActiveQuery
     */
    public static function search($term, $companyId)
    {
        return static::find()
            ->where(['company_id' => $companyId, 'is_active' => true])
            ->andWhere(['or',
                ['like', 'customer_name', $term],
                ['like', 'customer_email', $term],
                ['like', 'customer_phone', $term],
                ['like', 'customer_fax', $term],
                ['like', 'customer_mobile', $term],
            ])
            ->orderBy(['customer_name' => SORT_ASC]);
    }

    /**
     * Get formatted address
     *
     * @return string
     */
    public function getFormattedAddress()
    {
        $address = [];
        
        if (!empty($this->customer_name)) {
            $address[] = $this->customer_name;
        }
        
        if (!empty($this->customer_address)) {
            $address[] = $this->customer_address;
        }
        
        $contact = [];
        if (!empty($this->customer_phone)) {
            $contact[] = 'Phone: ' . $this->customer_phone;
        }
        
        if (!empty($this->customer_fax)) {
            $contact[] = 'Fax: ' . $this->customer_fax;
        }
        
        if (!empty($this->customer_mobile)) {
            $contact[] = 'Mobile: ' . $this->customer_mobile;
        }
        
        if (!empty($this->customer_email)) {
            $contact[] = 'Email: ' . $this->customer_email;
        }
        
        if (!empty($contact)) {
            $address[] = implode(' | ', $contact);
        }
        
        return implode("\n", $address);
    }

    /**
     * Get customer display name
     *
     * @return string
     */
    public function getDisplayName()
    {
        $name = $this->customer_name;
        if (!empty($this->customer_email)) {
            $name .= ' (' . $this->customer_email . ')';
        }
        return $name;
    }

    /**
     * Get total invoices count
     *
     * @return int
     */
    public function getInvoicesCount()
    {
        return $this->getInvoices()->count();
    }

    /**
     * Get total amount of all invoices
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->getInvoices()->sum('total_amount') ?: 0;
    }

    /**
     * Gets query for outstanding [[Invoices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOutstandingInvoices()
    {
        return $this->getInvoices()
            ->where(['not in', 'status', [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED]])
            ->orderBy(['due_date' => SORT_ASC]);
    }
}