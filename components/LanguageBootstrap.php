<?php

namespace app\components;

use Yii;
use yii\base\BootstrapInterface;
use app\models\Company;

/**
 * Language Bootstrap Component
 * Automatically detects and sets the application language based on user preferences
 */
class LanguageBootstrap implements BootstrapInterface
{
    /**
     * Bootstrap method
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        // Get language from session first
        $sessionLanguage = Yii::$app->session->get('language');
        
        // Debug logging
        Yii::info('LanguageBootstrap - Initial session language: ' . ($sessionLanguage ?? 'null'), 'language');
        Yii::info('LanguageBootstrap - User is guest: ' . (Yii::$app->user->isGuest ? 'yes' : 'no'), 'language');
        
        // If user is logged in, try to get language from company settings
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            $companyId = Yii::$app->session->get('current_company_id');
            
            Yii::info('LanguageBootstrap - Company ID: ' . ($companyId ?? 'null'), 'language');
            
            if ($companyId && $user) {
                try {
                    $company = Company::findOne(['id' => $companyId, 'user_id' => $user->id]);
                    if ($company && $company->language) {
                        $sessionLanguage = $company->language;
                        // Update session language
                        Yii::$app->session->set('language', $sessionLanguage);
                        Yii::info('LanguageBootstrap - Updated language from company: ' . $sessionLanguage, 'language');
                    } else {
                        Yii::info('LanguageBootstrap - No company or language found', 'language');
                    }
                } catch (\Exception $e) {
                    // Ignore database errors during bootstrap
                    Yii::info('LanguageBootstrap - Database error: ' . $e->getMessage(), 'language');
                }
            }
        }
        
        // Set application language
        if ($sessionLanguage && $this->isValidLanguage($sessionLanguage)) {
            Yii::$app->language = $sessionLanguage;
            Yii::info('LanguageBootstrap - Set app language to: ' . $sessionLanguage, 'language');
        } else {
            Yii::info('LanguageBootstrap - Using default language: ' . Yii::$app->language, 'language');
        }
    }
    
    /**
     * Check if language is valid
     * @param string $language
     * @return bool
     */
    private function isValidLanguage($language)
    {
        $allowedLanguages = ['en-US', 'es-ES', 'ko-KR', 'zh-CN', 'zh-TW'];
        return in_array($language, $allowedLanguages);
    }
}