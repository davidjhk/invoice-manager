<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Change password form
 */
class ChangePasswordForm extends Model
{
    public $currentPassword;
    public $newPassword;
    public $confirmPassword;

    private $_user;

    /**
     * Constructor
     * @param User $user
     * @param array $config
     */
    public function __construct($user, $config = [])
    {
        $this->_user = $user;
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['currentPassword', 'newPassword', 'confirmPassword'], 'required'],
            [['currentPassword'], 'validateCurrentPassword'],
            [['newPassword'], 'string', 'min' => 6],
            [['confirmPassword'], 'compare', 'compareAttribute' => 'newPassword'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'currentPassword' => 'Current Password',
            'newPassword' => 'New Password',
            'confirmPassword' => 'Confirm New Password',
        ];
    }

    /**
     * Validates the current password
     */
    public function validateCurrentPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->_user->validatePassword($this->currentPassword)) {
                $this->addError($attribute, 'Current password is incorrect.');
            }
        }
    }

    /**
     * Changes password
     * @return bool if password was changed
     */
    public function changePassword()
    {
        if ($this->validate()) {
            $this->_user->setPassword($this->newPassword);
            $this->_user->removePasswordResetToken();
            return $this->_user->save(false);
        }
        return false;
    }
}