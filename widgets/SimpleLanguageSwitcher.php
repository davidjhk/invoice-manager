<?php

namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Simple Language switcher widget - no Bootstrap dependency
 */
class SimpleLanguageSwitcher extends Widget
{
    public $languages = [
        'en-US' => ['name' => 'English', 'native' => 'English', 'flag' => 'EN'],
        'es-ES' => ['name' => 'Spanish', 'native' => 'Español', 'flag' => 'ES'],
        'ko-KR' => ['name' => 'Korean', 'native' => '한국어', 'flag' => 'KO'],
        'zh-CN' => ['name' => 'Chinese (Simplified)', 'native' => '简体中文', 'flag' => 'CN'],
        'zh-TW' => ['name' => 'Chinese (Traditional)', 'native' => '繁體中文', 'flag' => 'TW']
    ];

    public function run()
    {
        $currentLanguage = Yii::$app->language;
        
        if (!isset($this->languages[$currentLanguage])) {
            $currentLanguage = 'en-US';
        }
        
        $currentLangConfig = $this->languages[$currentLanguage];
        
        $this->registerAssets();
        
        $html = '<div class="simple-language-switcher">';
        
        // Current language button with modern code style
        $flagHtml = '<span class="lang-code">' . $currentLangConfig['flag'] . '</span>';
        $buttonText = $flagHtml . ' ' . $currentLangConfig['native'];
        $html .= '<button type="button" class="simple-lang-button" onclick="toggleLanguageMenu()">';
        $html .= $buttonText;
        $html .= '</button>';
        
        // Language menu
        $html .= '<div class="simple-lang-menu" id="languageMenu" style="display: none;">';
        
        foreach ($this->languages as $langCode => $langConfig) {
            $isActive = $langCode === $currentLanguage;
            $flagHtml = '<span class="lang-code">' . $langConfig['flag'] . '</span>';
            $linkText = $flagHtml . ' ' . $langConfig['native'];
            
            if ($isActive) {
                $html .= '<div class="simple-lang-item active">' . $linkText . '</div>';
            } else {
                $url = Url::to(['/site/change-language', 'language' => $langCode]);
                $html .= '<a href="' . $url . '" class="simple-lang-item">' . $linkText . '</a>';
            }
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    protected function registerAssets()
    {
        $view = $this->getView();
        
        $view->registerCss("
            .simple-language-switcher {
                position: relative;
                display: inline-block;
            }
            
            .simple-lang-button {
                background: rgba(75, 85, 99, 0.1);
                border: 1px solid rgba(156, 163, 175, 0.2);
                color: #d1d5db;
                padding: 0.5rem 1rem;
                border-radius: 0.375rem;
                cursor: pointer;
                font-size: 0.875rem;
                font-weight: 500;
                backdrop-filter: blur(8px);
                transition: all 0.2s ease;
            }
            
            .simple-lang-button:hover {
                background: rgba(75, 85, 99, 0.25);
                border-color: rgba(156, 163, 175, 0.4);
                color: #ffffff;
            }
            
            .simple-lang-menu {
                position: absolute;
                top: 100%;
                right: 0;
                z-index: 9999;
                min-width: 200px;
                background: rgba(255, 255, 255, 0.98);
                border: 2px solid rgba(99, 102, 241, 0.3);
                border-radius: 0.5rem;
                box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.4);
                backdrop-filter: blur(10px);
                margin-top: 0.125rem;
                padding: 0.5rem 0;
            }
            
            .simple-lang-item {
                display: block;
                padding: 0.5rem 1rem;
                color: #374151;
                text-decoration: none;
                font-size: 0.875rem;
                transition: all 0.15s ease;
            }
            
            .simple-lang-item:hover {
                background: rgba(99, 102, 241, 0.1);
                color: #4f46e5;
                text-decoration: none;
            }
            
            .simple-lang-item.active {
                background: rgba(99, 102, 241, 0.2);
                color: #4f46e5;
                font-weight: 600;
            }
            
            /* Dark mode support */
            body.dark-mode .simple-lang-menu {
                background: rgba(31, 41, 55, 0.98);
                border-color: rgba(139, 92, 246, 0.3);
            }
            
            body.dark-mode .simple-lang-item {
                color: #d1d5db;
            }
            
            body.dark-mode .simple-lang-item:hover {
                background: rgba(99, 102, 241, 0.2);
                color: #a5b4fc;
            }
            
            body.dark-mode .simple-lang-item.active {
                background: rgba(99, 102, 241, 0.3);
                color: #a5b4fc;
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
            // Global function for language menu toggle
            window.toggleLanguageMenu = function() {
                console.log('Simple language menu toggle clicked');
                var menu = document.getElementById('languageMenu');
                if (menu.style.display === 'none' || menu.style.display === '') {
                    // Close all other menus first
                    var allMenus = document.querySelectorAll('.simple-lang-menu');
                    allMenus.forEach(function(m) {
                        if (m !== menu) m.style.display = 'none';
                    });
                    
                    menu.style.display = 'block';
                    console.log('Language menu opened');
                } else {
                    menu.style.display = 'none';
                    console.log('Language menu closed');
                }
            };
            
            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                var switcher = e.target.closest('.simple-language-switcher');
                if (!switcher) {
                    var menu = document.getElementById('languageMenu');
                    if (menu) {
                        menu.style.display = 'none';
                    }
                }
            });
            
            // Close on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    var menu = document.getElementById('languageMenu');
                    if (menu) {
                        menu.style.display = 'none';
                    }
                }
            });
            
            console.log('Simple Language Switcher loaded');
        ");
    }
}