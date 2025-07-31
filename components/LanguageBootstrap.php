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
        
        // If user is logged in, try to get language from appropriate source
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            
            // For subusers, check session-based personal settings first
            if ($user && $user->isSubuser()) {
                $subuserLanguage = Yii::$app->session->get('subuser_language');
                if ($subuserLanguage && $this->isValidLanguage($subuserLanguage)) {
                    $sessionLanguage = $subuserLanguage;
                    Yii::$app->session->set('language', $sessionLanguage);
                    Yii::info('LanguageBootstrap - Using subuser language: ' . $sessionLanguage, 'language');
                } else {
                    // Fallback to company language for subusers
                    $companyId = Yii::$app->session->get('current_company_id');
                    if ($companyId) {
                        try {
                            $company = Company::findForCurrentUser()->where(['c.id' => $companyId])->one();
                            if ($company && $company->language) {
                                $sessionLanguage = $company->language;
                                Yii::$app->session->set('language', $sessionLanguage);
                                Yii::info('LanguageBootstrap - Subuser using company language: ' . $sessionLanguage, 'language');
                            }
                        } catch (\Exception $e) {
                            Yii::info('LanguageBootstrap - Database error for subuser: ' . $e->getMessage(), 'language');
                        }
                    }
                }
            } else {
                // For regular users, get language from company settings
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
        }
        
        // Set application language
        if ($sessionLanguage && $this->isValidLanguage($sessionLanguage)) {
            Yii::$app->language = $sessionLanguage;
            Yii::info('LanguageBootstrap - Set app language to: ' . $sessionLanguage, 'language');

            // Set date format based on language
            if ($sessionLanguage === 'ko-KR') {
                Yii::$app->formatter->dateFormat = 'php:Y년 n월 j일';
            } else {
                // Default date format for other languages
                Yii::$app->formatter->dateFormat = 'php:M d, Y';
            }
        } else {
            Yii::info('LanguageBootstrap - Using default language: ' . Yii::$app->language, 'language');
            // Default date format if no language is set
            Yii::$app->formatter->dateFormat = 'php:M d, Y';
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