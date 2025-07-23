<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%jdosa_payments}}".
 *
 * @property int $id
 * @property int $company_id
 * @property int $customer_id
 * @property int $invoice_id
 * @property float $amount
 * @property string $payment_date
 * @property string|null $payment_method
 * @property string|null $reference_number
 * @property string|null $notes
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Company $company
 * @property Customer $customer
 * @property Invoice $invoice
 */
class Payment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%jdosa_payments}}';
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
                'value' => function () {
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
            [['company_id', 'customer_id', 'invoice_id', 'amount', 'payment_date'], 'required'],
            [['company_id', 'customer_id', 'invoice_id'], 'integer'],
            [['amount'], 'number', 'min' => 0.01],
            [['payment_date'], 'date', 'format' => 'php:Y-m-d'],
            [['notes'], 'string'],
            [['payment_method'], 'string', 'max' => 50],
            [['reference_number'], 'string', 'max' => 100],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::class, 'targetAttribute' => ['invoice_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
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
            'company_id' => 'Company',
            'customer_id' => 'Customer',
            'invoice_id' => 'Invoice',
            'amount' => 'Amount',
            'payment_date' => 'Payment Date',
            'payment_method' => 'Payment Method',
            'reference_number' => 'Reference Number',
            'notes' => 'Notes',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
    }

    /**
     * After saving a payment, update the related invoice status.
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->invoice) {
            $this->invoice->updatePaymentStatus();
        }
    }

    /**
     * After deleting a payment, update the related invoice status.
     */
    public function afterDelete()
    {
        parent::afterDelete();
        if ($this->invoice) {
            $this->invoice->updatePaymentStatus();
        }
    }
}
