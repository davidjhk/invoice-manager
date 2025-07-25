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
    public $buttonClass = 'btn btn-outline-light btn-sm dropdown-toggle company-btn';
    
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
            'flag' => 'EN',
            'code' => 'en'
        ],
        'es-ES' => [
            'name' => 'Spanish',
            'native' => 'Español',
            'flag' => 'ES',
            'code' => 'es'
        ],
        'ko-KR' => [
            'name' => 'Korean',
            'native' => '한국어',
            'flag' => 'KO',
            'code' => 'ko'
        ],
        'zh-CN' => [
            'name' => 'Chinese (Simplified)',
            'native' => '简体中文',
            'flag' => 'CN',
            'code' => 'zh-CN'
        ],
        'zh-TW' => [
            'name' => 'Chinese (Traditional)',
            'native' => '繁體中文',
            'flag' => 'TW',
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
        
        // Dropdown button with modern language code
        $flagHtml = '<span class="lang-code">' . $currentLangConfig['flag'] . '</span>';
        $buttonText = $flagHtml . ' ' . 
                     ($this->showNativeNames ? $currentLangConfig['native'] : $currentLangConfig['name']);
        
        $html .= '<button class="' . $this->buttonClass . ' language-dropdown-toggle" type="button" aria-haspopup="true" aria-expanded="false" id="language-switcher-btn">';
        $html .= $buttonText;
        $html .= '</button>';
        
        // Dropdown menu
        $html .= Html::beginTag('div', [
            'class' => $this->menuClass,
            'aria-labelledby' => 'language-switcher-btn'
        ]);
        
        foreach ($this->languages as $langCode => $langConfig) {
            $isActive = $langCode === $currentLanguage;
            $flagHtml = '<span class="lang-code">' . $langConfig['flag'] . '</span>';
            $linkText = $flagHtml . ' ' . 
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

            /* Styles for company-btn, applied to language switcher */
            .language-switcher .company-btn {
                max-width: 120px !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }
            
            /* Modern language code styling */
            .lang-code {
                display: inline-block;
                background: rgba(99, 102, 241, 0.9);
                color: #ffffff;
                font-size: 0.6rem;
                font-weight: 700;
                padding: 0.1rem 0.3rem;
                border-radius: 0.25rem;
                margin-right: 0.4rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
                vertical-align: middle;
            }
            
            body.dark-mode .lang-code {
                background: rgba(139, 92, 246, 0.9);
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
            }
        ");
        
        $view->registerJs("
            // Wait for DOM and dependencies
            $(document).ready(function() {
                setTimeout(function() {
                    console.log('=== Language Switcher Debug ===');
                    console.log('jQuery version:', $.fn.jquery);
                    console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
                    
                    // Find elements
                    var languageDropdown = $('.language-switcher');
                    var dropdownToggle = $('.language-dropdown-toggle');
                    var dropdownMenu = languageDropdown.find('.dropdown-menu');
                    
                    console.log('Elements found:', {
                        dropdown: languageDropdown.length,
                        toggle: dropdownToggle.length,
                        menu: dropdownMenu.length
                    });
                    
                    if (dropdownToggle.length === 0) {
                        console.error('Language dropdown toggle not found!');
                        return;
                    }
                    
                    if (dropdownMenu.length === 0) {
                        console.error('Language dropdown menu not found!');
                        return;
                    }
                    
                    // Remove any existing handlers
                    dropdownToggle.off('click.languageSwitcher');
                    
                    // Add click handler with namespace
                    dropdownToggle.on('click.languageSwitcher', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        console.log('Language dropdown clicked!');
                        
                        // Close all other dropdowns
                        $('.dropdown-menu').not(dropdownMenu).removeClass('show');
                        $('.dropdown-toggle').not(dropdownToggle).attr('aria-expanded', 'false');
                        
                        // Toggle this dropdown
                        var isOpen = dropdownMenu.hasClass('show');
                        if (isOpen) {
                            dropdownMenu.removeClass('show');
                            dropdownToggle.attr('aria-expanded', 'false');
                            console.log('Dropdown closed');
                        } else {
                            dropdownMenu.addClass('show');
                            dropdownToggle.attr('aria-expanded', 'true');
                            console.log('Dropdown opened');
                        }
                    });
                    
                    // Close dropdown when clicking outside
                    $(document).off('click.languageDropdown').on('click.languageDropdown', function(e) {
                        if (!languageDropdown.is(e.target) && languageDropdown.has(e.target).length === 0) {
                            dropdownMenu.removeClass('show');
                            dropdownToggle.attr('aria-expanded', 'false');
                        }
                    });
                    
                    // Close on escape
                    $(document).off('keydown.languageDropdown').on('keydown.languageDropdown', function(e) {
                        if (e.keyCode === 27) { // Escape key
                            dropdownMenu.removeClass('show');
                            dropdownToggle.attr('aria-expanded', 'false');
                        }
                    });
                    
                    // Handle language switching
                    $('.language-switcher .dropdown-item').off('click.languageSwitch').on('click.languageSwitch', function(e) {
                        if (!$(this).hasClass('active')) {
                            e.preventDefault();
                            var language = $(this).data('language');
                            var url = $(this).attr('href');
                            
                            console.log('Language change:', language, url);
                            
                            // Show loading state
                            var originalText = dropdownToggle.html();
                            dropdownToggle.html('<i class=\"fas fa-spinner fa-spin\"></i> Loading...');
                            
                            // Close dropdown
                            dropdownMenu.removeClass('show');
                            dropdownToggle.attr('aria-expanded', 'false');
                            
                            // Simple redirect instead of AJAX for better reliability
                            window.location.href = url;
                        }
                    });
                    
                    console.log('Language Switcher initialized successfully');
                }, 500); // Small delay to ensure everything is loaded
            });
        ");
    }
}