<?php

namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Language switcher widget for multi-language support
 */
class LanguageSwitcher extends Widget
{
    /**
     * @var string The CSS class for the dropdown container
     */
    public $containerClass = 'language-switcher dropdown';
    
    /**
     * @var string The CSS class for the dropdown button
     */
    public $buttonClass = 'btn btn-outline-secondary dropdown-toggle';
    
    /**
     * @var string The CSS class for the dropdown menu
     */
    public $menuClass = 'dropdown-menu dropdown-menu-right';
    
    /**
     * @var bool Whether to show language names in their native script
     */
    public $showNativeNames = true;
    
    /**
     * @var array Available languages configuration
     */
    public $languages = [
        'en-US' => [
            'name' => 'English',
            'native' => 'English',
            'flag' => '🇺🇸',
            'code' => 'en'
        ],
        'es-ES' => [
            'name' => 'Spanish',
            'native' => 'Español',
            'flag' => '🇪🇸',
            'code' => 'es'
        ],
        'ko-KR' => [
            'name' => 'Korean',
            'native' => '한국어',
            'flag' => '🇰🇷',
            'code' => 'ko'
        ],
        'zh-CN' => [
            'name' => 'Chinese (Simplified)',
            'native' => '简体中文',
            'flag' => '🇨🇳',
            'code' => 'zh-CN'
        ],
        'zh-TW' => [
            'name' => 'Chinese (Traditional)',
            'native' => '繁體中文',
            'flag' => '🇹🇼',
            'code' => 'zh-TW'
        ]
    ];

    /**
     * Runs the widget
     */
    public function run()
    {
        $currentLanguage = Yii::$app->language;
        
        // Debug: Log current language detection
        Yii::info('LanguageSwitcher - Current app language: ' . $currentLanguage, 'language');
        Yii::info('LanguageSwitcher - Session language: ' . (Yii::$app->session->get('language') ?? 'null'), 'language');
        Yii::info('LanguageSwitcher - Available languages: ' . implode(', ', array_keys($this->languages)), 'language');
        
        // Check if current language exists in our language list
        if (!isset($this->languages[$currentLanguage])) {
            Yii::warning('LanguageSwitcher - Current language not found in available languages, defaulting to en-US', 'language');
            $currentLanguage = 'en-US';
        }
        
        $currentLangConfig = $this->languages[$currentLanguage];
        
        // Register required assets
        $this->registerAssets();
        
        $html = Html::beginTag('div', ['class' => $this->containerClass]);
        
        // Dropdown button
        $buttonText = $currentLangConfig['flag'] . ' ' . 
                     ($this->showNativeNames ? $currentLangConfig['native'] : $currentLangConfig['name']);
        
        $html .= Html::button($buttonText, [
            'class' => $this->buttonClass,
            'type' => 'button',
            'data-toggle' => 'dropdown',
            'aria-haspopup' => 'true',
            'aria-expanded' => 'false',
            'id' => 'language-switcher-btn'
        ]);
        
        // Dropdown menu
        $html .= Html::beginTag('div', [
            'class' => $this->menuClass,
            'aria-labelledby' => 'language-switcher-btn'
        ]);
        
        foreach ($this->languages as $langCode => $langConfig) {
            $isActive = $langCode === $currentLanguage;
            $linkText = $langConfig['flag'] . ' ' . 
                       ($this->showNativeNames ? $langConfig['native'] : $langConfig['name']);
            
            $html .= Html::a($linkText, 
                Url::to(['/site/change-language', 'language' => $langCode]), [
                'class' => 'dropdown-item' . ($isActive ? ' active' : ''),
                'data-language' => $langCode
            ]);
        }
        
        $html .= Html::endTag('div'); // dropdown-menu
        $html .= Html::endTag('div'); // container
        
        return $html;
    }
    
    /**
     * Register required CSS and JS assets
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        
        $view->registerCss("
            .language-switcher {
                position: relative;
                display: inline-block;
            }
            
            /* Match the top bar dropdown menu styling */
            .language-switcher .dropdown-menu {
                position: absolute !important;
                top: 100% !important;
                right: 0 !important;
                left: auto !important;
                z-index: 9999 !important;
                display: none !important;
                float: none !important;
                min-width: 200px !important;
                padding: 0.5rem 0 !important;
                margin: 0.125rem 0 0 !important;
                background: rgba(255, 255, 255, 0.98) !important;
                border: 2px solid rgba(99, 102, 241, 0.3) !important;
                border-radius: 0.5rem !important;
                box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.4), 0 10px 20px -5px rgba(0, 0, 0, 0.2) !important;
                backdrop-filter: blur(10px) !important;
                transform: translateY(0) !important;
                opacity: 0 !important;
                transition: all 0.2s ease !important;
            }
            
            .language-switcher .dropdown-menu.show {
                display: block !important;
                opacity: 1 !important;
                transform: translateY(0) !important;
            }
            
            .language-switcher .dropdown-item {
                display: block !important;
                width: 100% !important;
                padding: .3rem .8rem !important;
                clear: both !important;
                font-weight: 400 !important;
                color: #374151 !important;
                text-align: inherit !important;
                white-space: nowrap !important;
                background: transparent !important;
                border: 0 !important;
                text-decoration: none !important;
                font-size: 0.875rem !important;
                line-height: 1.5 !important;
                transition: all 0.15s ease !important;
            }
            
            .language-switcher .dropdown-item:hover,
            .language-switcher .dropdown-item:focus {
                background: rgba(99, 102, 241, 0.1) !important;
                color: #4f46e5 !important;
                text-decoration: none !important;
            }
            
            .language-switcher .dropdown-item.active {
                background: rgba(99, 102, 241, 0.2) !important;
                color: #4f46e5 !important;
                font-weight: 600 !important;
            }
            
            .language-switcher .dropdown-divider {
                height: 0 !important;
                margin: 0.5rem 0 !important;
                overflow: hidden !important;
                border-top: 1px solid rgba(0, 0, 0, 0.1) !important;
            }
            
            /* Dark mode support - match top bar dropdown */
            body.dark-mode .language-switcher .dropdown-menu {
                background: rgba(31, 41, 55, 0.98) !important;
                border: 2px solid rgba(139, 92, 246, 0.3) !important;
                box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.6), 0 10px 20px -5px rgba(0, 0, 0, 0.3) !important;
            }
            
            body.dark-mode .language-switcher .dropdown-item {
                color: #d1d5db !important;
            }
            
            body.dark-mode .language-switcher .dropdown-item:hover,
            body.dark-mode .language-switcher .dropdown-item:focus {
                background: rgba(99, 102, 241, 0.2) !important;
                color: #a5b4fc !important;
            }
            
            body.dark-mode .language-switcher .dropdown-item.active {
                background: rgba(99, 102, 241, 0.3) !important;
                color: #a5b4fc !important;
            }
            
            body.dark-mode .language-switcher .dropdown-divider {
                border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
            }
        ");
        
        $view->registerJs("
            // Handle Bootstrap dropdown initialization
            if (typeof bootstrap !== 'undefined') {
                try {
                    // Initialize Bootstrap 5 dropdowns
                    var dropdownElementList = [].slice.call(document.querySelectorAll('.language-switcher .dropdown-toggle'))
                    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                        return new bootstrap.Dropdown(dropdownToggleEl)
                    })
                } catch(e) {
                    // Fallback to Bootstrap 4/jQuery
                    $('.language-switcher .dropdown-toggle').dropdown();
                }
            } else {
                // jQuery Bootstrap 4 fallback
                $('.language-switcher .dropdown-toggle').dropdown();
            }
            
            // Handle language switching
            $('.language-switcher .dropdown-item').on('click', function(e) {
                if (!$(this).hasClass('active')) {
                    e.preventDefault();
                    var language = $(this).data('language');
                    var \$button = $('.language-switcher .dropdown-toggle');
                    
                    // Show loading state
                    var originalButtonText = \$button.html();
                    \$button.html('<i class=\"fas fa-spinner fa-spin\"></i> Loading...');
                    
                    // Make AJAX request to change language
                    $.post($(this).attr('href'), {}, function() {
                        // Reload page to apply language changes
                        window.location.reload();
                    }).fail(function() {
                        // Restore original text on failure
                        \$button.html(originalButtonText);
                        alert('Failed to change language. Please try again.');
                    });
                }
            });
        ");
    }
}