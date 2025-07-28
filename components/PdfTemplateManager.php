<?php

namespace app\components;

use Yii;

class PdfTemplateManager
{
    /**
     * Template definitions with complete styling and layout configurations
     */
    private static $templates = [
        'classic' => [
            'name' => 'Classic',
            'description' => 'Traditional professional layout with clean lines and borders',
            'color_scheme' => '#667eea',
            'font_family' => '"FreeSans", "DejavuSans", "Arial", sans-serif',
            'header_style' => 'bordered',
            'layout_type' => 'traditional',
            'accent_color' => '#667eea',
            'secondary_color' => '#f8f9fa',
            'text_color' => '#333333',
            'border_style' => 'solid',
            'border_width' => '2px',
            'border_radius' => '8px',
            'company_title_size' => '24px',
            'section_spacing' => '20px',
            'table_style' => 'bordered',
            'table_header_bg' => '#667eea',
            'table_header_color' => 'white',
            'table_border' => '1px solid #ddd',
            'table_stripe' => true,
            'notes_style' => 'bordered',
            'notes_bg' => '#f8f9fa',
            'notes_border' => '1px solid #dee2e6',
            'bill_to_border' => 'none',
            'ship_to_border' => 'none',
            'use_address_borders' => false,
            'separator_style' => 'line',
            'header_bg' => '#ffffff',
            'use_shadows' => false,
            'watermark_opacity' => '0.3',
        ],
        'modern' => [
            'name' => 'Modern',
            'description' => 'Clean minimalist design with subtle shadows and spacing',
            'color_scheme' => '#2563eb',
            'font_family' => '"Poppins", "Helvetica", "FreeSans", "DejavuSans", "Arial", sans-serif',
            'header_style' => 'minimal',
            'layout_type' => 'spacious',
            'accent_color' => '#2563eb',
            'secondary_color' => '#f1f5f9',
            'text_color' => '#1e293b',
            'border_style' => 'none',
            'border_width' => '0px',
            'border_radius' => '12px',
            'company_title_size' => '28px',
            'section_spacing' => '10px',
            'table_style' => 'minimal',
            'table_header_bg' => '#2563eb',
            'table_header_color' => 'white',
            'table_border' => 'none',
            'table_stripe' => false,
            'notes_style' => 'card',
            'notes_bg' => '#f1f5f9',
            'notes_border' => 'none',
            'bill_to_border' => 'none',
            'ship_to_border' => 'none',
            'use_address_borders' => false,
            'separator_style' => 'space',
            'header_bg' => '#ffffff',
            'use_shadows' => true,
            'watermark_opacity' => '0.15',
        ],
        'elegant' => [
            'name' => 'Elegant',
            'description' => 'Sophisticated design with refined typography and subtle dividers',
            'color_scheme' => '#059669',
            'font_family' => '"Playfair Display","FreeSerif", "Times", "DejavuSerif", serif',
            'header_style' => 'underlined',
            'layout_type' => 'refined',
            'accent_color' => '#059669',
            'secondary_color' => '#ecfdf5',
            'text_color' => '#064e3b',
            'border_style' => 'none',
            'border_width' => '0px',
            'border_radius' => '6px',
            'company_title_size' => '26px',
            'section_spacing' => '25px',
            'table_style' => 'elegant',
            'table_header_bg' => 'transparent',
            'table_header_color' => '#059669',
            'table_border' => '1px solid #d1fae5',
            'table_stripe' => true,
            'notes_style' => 'quote',
            'notes_bg' => '#ecfdf5',
            'notes_border' => '3px solid #059669',
            'bill_to_border' => 'border-left: 2px solid #059669;',
            'ship_to_border' => 'border-left: 2px solid #059669;',
            'use_address_borders' => true,
            'separator_style' => 'gradient',
            'header_bg' => '#ffffff',
            'use_shadows' => false,
            'watermark_opacity' => '0.2',
        ],
        'corporate' => [
            'name' => 'Corporate',
            'description' => 'Bold professional design with strong geometric elements',
            'color_scheme' => '#1e3a8a',
            'font_family' => '"FreeSans", "DejavuSans", "Arial", sans-serif',
            'header_style' => 'block',
            'layout_type' => 'structured',
            'accent_color' => '#1e3a8a',
            'secondary_color' => '#f1f5f9',
            'text_color' => '#1e293b',
            'border_style' => 'solid',
            'border_width' => '3px',
            'border_radius' => '0px',
            'company_title_size' => '28px',
            'section_spacing' => '18px',
            'table_style' => 'corporate',
            'table_header_bg' => '#1e3a8a',
            'table_header_color' => 'white',
            'table_border' => '2px solid #1e3a8a',
            'table_stripe' => false,
            'notes_style' => 'block',
            'notes_bg' => '#f1f5f9',
            'notes_border' => '3px solid #1e3a8a',
            'bill_to_border' => '3px solid #1e3a8a',
            'ship_to_border' => '3px solid #1e3a8a',
            'use_address_borders' => false,
            'separator_style' => 'thick',
            'header_bg' => '#f1f5f9',
            'use_shadows' => false,
            'watermark_opacity' => '0.25',
        ],
        'creative' => [
            'name' => 'Creative',
            'description' => 'Dynamic asymmetric layout with rounded corners and gradients',
            'color_scheme' => '#7c3aed',
            'font_family' => '"FreeSans", "DejavuSans", "Arial", sans-serif',
            'header_style' => 'angled',
            'layout_type' => 'dynamic',
            'accent_color' => '#7c3aed',
            'secondary_color' => '#f3f4f6',
            'text_color' => '#374151',
            'border_style' => 'dashed',
            'border_width' => '2px',
            'border_radius' => '15px',
            'company_title_size' => '32px',
            'section_spacing' => '25px',
            'table_style' => 'creative',
            'table_header_bg' => 'linear-gradient(135deg, #7c3aed 0%, #a855f7 100%)',
            'table_header_color' => 'white',
            'table_border' => '2px solid #e5e7eb',
            'table_stripe' => true,
            'notes_style' => 'artistic',
            'notes_bg' => 'linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%)',
            'notes_border' => '2px dashed #7c3aed',
            'bill_to_border' => '2px dashed #7c3aed',
            'ship_to_border' => '2px dashed #7c3aed',
            'use_address_borders' => true,
            'separator_style' => 'decorative',
            'header_bg' => 'linear-gradient(135deg, #ffffff 0%, #f9fafb 100%)',
            'use_shadows' => true,
            'watermark_opacity' => '0.1',
        ]
    ];

    /**
     * Get all available templates
     *
     * @return array
     */
    public static function getAllTemplates()
    {
        $templates = [];
        foreach (self::$templates as $key => $template) {
            $templates[$key] = [
                'id' => $key,
                'name' => Yii::t('invoice', 'template_' . $key),
                'description' => Yii::t('invoice', 'template_' . $key . '_desc'),
                'color_scheme' => $template['color_scheme'],
                'preview_url' => '/images/templates/' . $key . '-preview.png'
            ];
        }
        return $templates;
    }

    /**
     * Get template configuration by ID
     *
     * @param string $templateId
     * @return array|null
     */
    public static function getTemplate($templateId)
    {
        return self::$templates[$templateId] ?? null;
    }

    /**
     * Get template options for dropdown
     *
     * @return array
     */
    public static function getTemplateOptions()
    {
        $options = [];
        foreach (self::$templates as $key => $template) {
            $options[$key] = Yii::t('invoice', 'template_' . $key);
        }
        return $options;
    }

    /**
     * Get default template ID
     *
     * @return string
     */
    public static function getDefaultTemplate()
    {
        return 'classic';
    }

    /**
     * Generate CSS styles for a specific template with unique layouts
     *
     * @param string $templateId
     * @param bool $useCJKFont
     * @return string
     */
    public static function generateTemplateStyles($templateId, $useCJKFont = false)
    {
        $template = self::getTemplate($templateId) ?? self::getTemplate('classic');
        
        $letterSpacing = $useCJKFont ? 'letter-spacing: 0.5px;' : '';
        
        // Base styles compatible with mPDF
        $baseStyles = '
        <style>
            body { 
                font-family: ' . $template['font_family'] . '; 
                font-size: 9px; 
                line-height: 1.4; 
                color: ' . $template['text_color'] . ';
                ' . $letterSpacing . ' 
            }
            p { margin: 0; padding: 5px; text-indent: 0; }
            div { text-indent: 0; }
            br { margin: 0; padding: 0; }
            * { text-indent: 0 !important; margin-left: 0 !important; }
            .address-line { display: block; text-indent: 0; margin-left: 0; }
            .logo { max-height: 15px; max-width: 90px; height: auto; }
            .paid-watermark { opacity: ' . $template['watermark_opacity'] . '; }
        ';
        
        // Add template-specific styles that are mPDF compatible
        $templateStyles = self::getTemplateSpecificStylesMpdf($template, $letterSpacing);
        return $baseStyles . $templateStyles . '</style>';
    }

    /**
     * Generate template-specific CSS styles based on layout type (mPDF compatible)
     *
     * @param array $template
     * @param string $letterSpacing
     * @return string
     */
    private static function getTemplateSpecificStylesMpdf($template, $letterSpacing)
    {
        switch ($template['layout_type']) {
            case 'traditional':
                return self::getClassicStylesMpdf($template, $letterSpacing);
            case 'spacious':
                return self::getModernStylesMpdf($template, $letterSpacing);
            case 'refined':
                return self::getElegantStylesMpdf($template, $letterSpacing);
            case 'structured':
                return self::getCorporateStylesMpdf($template, $letterSpacing);
            case 'dynamic':
                return self::getCreativeStylesMpdf($template, $letterSpacing);
            default:
                return self::getClassicStylesMpdf($template, $letterSpacing);
        }
    }

    /**
     * Generate template-specific CSS styles based on layout type (for web preview)
     *
     * @param array $template
     * @param string $letterSpacing
     * @param string $boxShadow
     * @return string
     */
    private static function getTemplateSpecificStyles($template, $letterSpacing, $boxShadow)
    {
        switch ($template['layout_type']) {
            case 'traditional':
                return self::getClassicStyles($template, $letterSpacing, $boxShadow);
            case 'spacious':
                return self::getModernStyles($template, $letterSpacing, $boxShadow);
            case 'refined':
                return self::getElegantStyles($template, $letterSpacing, $boxShadow);
            case 'structured':
                return self::getCorporateStyles($template, $letterSpacing, $boxShadow);
            case 'dynamic':
                return self::getCreativeStyles($template, $letterSpacing, $boxShadow);
            default:
                return self::getClassicStyles($template, $letterSpacing, $boxShadow);
        }
    }

    /**
     * Classic template styles - Traditional bordered layout
     */
    private static function getClassicStyles($template, $letterSpacing, $boxShadow)
    {
        return '
            .header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                page-break-after: avoid; 
                background: ' . $template['header_bg'] . ';
                border: ' . $template['border_width'] . ' ' . $template['border_style'] . ' ' . $template['accent_color'] . ';
                border-radius: ' . $template['border_radius'] . ';
                padding: 15px;
            }
            .company-info { font-size: 10px; line-height: 1.4; ' . $letterSpacing . ' }
            .document-title { 
                font-size: ' . $template[''] . '; 
                font-weight: bold; 
                color: ' . $template['accent_color'] . '; 
                margin-bottom: 9px;
                border-bottom: 2px solid ' . $template['accent_color'] . ';
                padding-bottom: 5px;
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                page-break-after: avoid; 
                border-radius: ' . $template['border_radius'] . ';
                padding: 5px;
            }
            .sub-header-column { vertical-align: top; padding: 10px; }
			.bill-to, .ship-to { 
                padding: 10px; 
                border: 1px solid #ddd;
                border-radius: 4px;
                background: #fafafa;
            }
            .document-details-box { 
                border: 1px solid ' . $template['accent_color'] . '; 
                border-radius: 4px;
                padding: 10px;
                background-color: ' . $template['secondary_color'] . ' !important;
            }
            .document-details-box table { width: 100%;  }
            .document-details-box td { padding: 8px; font-size: 9px; border-bottom: 1px dotted #ccc; }
            .separator { 
                border-top: ' . $template['border_width'] . ' ' . $template['border_style'] . ' ' . $template['accent_color'] . '; 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: 0; 
            }
            .items-table { 
                width: 100%; 
                 
                margin: ' . $template['section_spacing'] . ' 0; 
                border: ' . $template['table_border'] . ';
                border-radius: ' . $template['border_radius'] . ';
            }
            .items-table th { 
                background: ' . $template['table_header_bg'] . '; 
                color: ' . $template['table_header_color'] . '; 
                padding: 12px; 
                font-size: 12px; 
                font-weight: bold; 
                border-bottom: 2px solid ' . $template['accent_color'] . ';
            }
            .items-table td { 
                padding: 12px; 
                border-bottom: ' . $template['table_border'] . '; 
                font-size: 9px; 
                ' . $letterSpacing . ';
                background: ' . ($template['table_stripe'] ? '#f9f9f9' : 'transparent') . ';
            }
            .items-table tr:nth-child(even) td { background: #f9f9f9; }
            .totals { margin-top: ' . $template['section_spacing'] . '; }
            .totals-table { 
                 
                border: 1px solid ' . $template['accent_color'] . ';
                border-radius: 4px;
            }
            .totals-table td { 
                padding: 10px; 
                font-size: 9px; 
                border-bottom: 1px solid #ddd; 
            }
            .total-row { 
                font-weight: bold; 
                font-size: 11px; 
                background-color: ' . $template['secondary_color'] . ' !important; 
            }
            .notes { 
                margin-top: ' . $template['section_spacing'] . '; 
                padding: 15px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
                border: ' . $template['notes_border'] . ';
                border-radius: ' . $template['border_radius'] . ';
            }
        ';
    }

    /**
     * Modern template styles - Clean minimal layout
     */
    private static function getModernStyles($template, $letterSpacing, $boxShadow)
    {
        return '
            .header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                page-break-after: avoid; 
                background: ' . $template['header_bg'] . ';
                padding: 20px 0;
            }
            .company-info { font-size: 11px; line-height: 1.6; ' . $letterSpacing . ' }
            .document-title { 
                font-size: ' . $template['company_title_size'] . '; 
                font-weight: 300; 
                color: ' . $template['accent_color'] . '; 
                margin-bottom: 15px;
                letter-spacing: 1px;
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                page-break-after: avoid; 
                padding: 0;
            }
            .sub-header-column { vertical-align: top; padding: 0 20px 0 0; }
            .bill-to, .ship-to { 
                padding: 0; 
                background: transparent;
                margin-bottom: 20px;
            }
            .document-details-box { 
                padding: 20px; 
                background-color: ' . $template['secondary_color'] . ' !important;
                border-radius: ' . $template['border_radius'] . ';
                ' . $boxShadow . '
            }
            .document-details-box table { width: 100%;  }
            .document-details-box td { padding: 10px 0; font-size: 10px; border: none; }
            .separator { 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: ' . $template['section_spacing'] . ';
                background: transparent;
                border: none;
            }
            .items-table { 
                width: 100%; 
                 
                margin: ' . $template['section_spacing'] . ' 0; 
                ' . $boxShadow . '
            }
            .items-table th { 
                background: ' . $template['table_header_bg'] . '; 
                color: ' . $template['table_header_color'] . '; 
                padding: 15px; 
                font-size: 10px; 
                font-weight: 500; 
                border: none;
            }
            .items-table td { 
                padding: 15px; 
                border: none; 
                font-size: 9px; 
                ' . $letterSpacing . ';
            }
            .totals { margin-top: ' . $template['section_spacing'] . '; }
            .totals-table { 
                 
                background: white;
                border-radius: ' . $template['border_radius'] . ';
                ' . $boxShadow . '
            }
            .totals-table td { 
                padding: 12px 15px; 
                font-size: 10px; 
                border: none; 
            }
            .total-row { 
                font-weight: 600; 
                font-size: 12px; 
                background-color: ' . $template['secondary_color'] . ' !important; 
            }
            .notes { 
                margin-top: ' . $template['section_spacing'] . '; 
                padding: 20px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
                border-radius: ' . $template['border_radius'] . ';
                ' . $boxShadow . '
            }
        ';
    }

    /**
     * Elegant template styles - Sophisticated refined layout
     */
    private static function getElegantStyles($template, $letterSpacing, $boxShadow)
    {
        return '
            .header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                page-break-after: avoid; 
                background: ' . $template['header_bg'] . ';
                padding: 15px 0;
                border-bottom: 2px solid ' . $template['accent_color'] . ';
            }
            .company-info { font-size: 10px; line-height: 1.5; ' . $letterSpacing . ' }
            .document-title { 
                font-size: ' . $template['company_title_size'] . '; 
                font-weight: 400; 
                color: ' . $template['accent_color'] . '; 
                margin-bottom: 10px;
                position: relative;
            }
            .document-title:after {
                content: "";
                position: absolute;
                bottom: -5px;
                left: 0;
                width: 40px;
                height: 2px;
                background: ' . $template['accent_color'] . ';
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                page-break-after: avoid; 
                padding: 0;
            }
            .sub-header-column { vertical-align: top; padding: 0 15px 0 0; }
            .bill-to, .ship-to { 
                padding: 0; 
                background: transparent;
                position: relative;
            }
            .document-details-box { 
                padding: 15px; 
                background-color: ' . $template['secondary_color'] . ' !important;
                border-radius: ' . $template['border_radius'] . ';
                border-left: 4px solid ' . $template['accent_color'] . ';
            }
            .document-details-box table { width: 100%;  }
            .document-details-box td { padding: 8px 0; font-size: 9px; }
            .separator { 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: 1px;
                background: linear-gradient(to right, ' . $template['accent_color'] . ', transparent);
            }
            .items-table { 
                width: 100%; 
                 
                margin: ' . $template['section_spacing'] . ' 0; 
            }
            .items-table th { 
                background: ' . $template['table_header_bg'] . '; 
                color: ' . $template['table_header_color'] . '; 
                padding: 12px; 
                font-size: 10px; 
                font-weight: 500; 
                border-bottom: 2px solid ' . $template['accent_color'] . ';
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .items-table td { 
                padding: 12px; 
                border-bottom: ' . $template['table_border'] . '; 
                font-size: 9px; 
                ' . $letterSpacing . ';
            }
            .items-table tr:nth-child(odd) td { background-color: #fafafa !important; }
            .totals { margin-top: ' . $template['section_spacing'] . '; }
            .totals-table { 
                 
                border-left: 3px solid ' . $template['accent_color'] . ';
            }
            .totals-table td { 
                padding: 10px; 
                font-size: 9px; 
                border-bottom: 1px dotted #ccc; 
            }
            .total-row { 
                font-weight: 500; 
                font-size: 11px; 
                background-color: ' . $template['secondary_color'] . ' !important; 
                border-left: 4px solid ' . $template['accent_color'] . ';
            }
            .notes { 
                margin-top: ' . $template['section_spacing'] . '; 
                padding: 15px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
                border-left: ' . $template['notes_border'] . ';
                border-radius: 0 ' . $template['border_radius'] . ' ' . $template['border_radius'] . ' 0;
                font-style: italic;
            }
        ';
    }

    /**
     * Corporate template styles - Bold structured layout
     */
    private static function getCorporateStyles($template, $letterSpacing, $boxShadow)
    {
        return '
            .header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                page-break-after: avoid; 
                background: ' . $template['header_bg'] . ';
                border: ' . $template['border_width'] . ' ' . $template['border_style'] . ' ' . $template['accent_color'] . ';
                padding: 20px;
            }
            .company-info { font-size: 11px; line-height: 1.4; ' . $letterSpacing . ' font-weight: 500; }
            .document-title { 
                font-size: ' . $template['company_title_size'] . '; 
                font-weight: bold; 
                color: ' . $template['accent_color'] . '; 
                margin-bottom: 10px;
                text-transform: uppercase;
                background-color: ' . $template['accent_color'] . ' !important;
                color: white;
                padding: 10px;
                margin: -20px -20px 15px -20px;
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                page-break-after: avoid; 
                border: 2px solid ' . $template['accent_color'] . ';
                padding: 15px;
            }
            .sub-header-column { vertical-align: top; padding: 10px; }
            .bill-to, .ship-to { 
                padding: 10px; 
                border: 1px solid ' . $template['accent_color'] . ';
                margin-bottom: 10px;
            }
            .document-details-box { 
                padding: 15px; 
                background-color: ' . $template['accent_color'] . ' !important;
                color: white;
                font-weight: bold;
            }
            .document-details-box table { width: 100%;  }
            .document-details-box td { padding: 8px; font-size: 10px; color: white; }
            .separator { 
                border-top: ' . $template['border_width'] . ' ' . $template['border_style'] . ' ' . $template['accent_color'] . '; 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: 0;
            }
            .items-table { 
                width: 100%; 
                 
                margin: ' . $template['section_spacing'] . ' 0; 
                border: ' . $template['table_border'] . ';
            }
            .items-table th { 
                background: ' . $template['table_header_bg'] . '; 
                color: ' . $template['table_header_color'] . '; 
                padding: 15px; 
                font-size: 11px; 
                font-weight: bold; 
                text-transform: uppercase;
                border-bottom: 3px solid ' . $template['accent_color'] . ';
            }
            .items-table td { 
                padding: 15px; 
                border: 1px solid ' . $template['accent_color'] . '; 
                font-size: 10px; 
                ' . $letterSpacing . ';
            }
            .totals { margin-top: ' . $template['section_spacing'] . '; }
            .totals-table { 
                 
                border: 2px solid ' . $template['accent_color'] . ';
            }
            .totals-table td { 
                padding: 12px; 
                font-size: 10px; 
                border: 1px solid #ddd; 
                font-weight: 500;
            }
            .total-row { 
                font-weight: bold; 
                font-size: 12px; 
                background: ' . $template['accent_color'] . '; 
                color: white;
            }
            .notes { 
                margin-top: ' . $template['section_spacing'] . '; 
                padding: 15px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
                border: ' . $template['notes_border'] . ';
                font-weight: 500;
            }
        ';
    }

    /**
     * Creative template styles - Dynamic artistic layout
     */
    private static function getCreativeStyles($template, $letterSpacing, $boxShadow)
    {
        return '
            .header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                page-break-after: avoid; 
                background: ' . $template['header_bg'] . ';
                border-radius: ' . $template['border_radius'] . ';
                padding: 20px;
                ' . $boxShadow . '
                position: relative;
                overflow: hidden;
            }
            .header:before {
                content: "";
                position: absolute;
                top: -50%;
                right: -20px;
                width: 100px;
                height: 200%;
                background: ' . $template['accent_color'] . ';
                opacity: 0.1;
                transform: rotate(15deg);
            }
            .company-info { font-size: 10px; line-height: 1.5; ' . $letterSpacing . ' }
            .document-title { 
                font-size: ' . $template['company_title_size'] . '; 
                font-weight: 700; 
                color: ' . $template['accent_color'] . '; 
                margin-bottom: 15px;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
                position: relative;
                z-index: 2;
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                page-break-after: avoid; 
                padding: 0;
                position: relative;
            }
            .sub-header-column { vertical-align: top; padding: 0 15px 0 0; }
            .bill-to, .ship-to { 
                padding: 15px; 
                background: linear-gradient(135deg, #ffffff 0%, ' . $template['secondary_color'] . ' 100%);
                border-radius: ' . $template['border_radius'] . ';
                border: ' . $template['border_width'] . ' ' . $template['border_style'] . ' ' . $template['accent_color'] . ';
                border-radius: ' . $template['border_radius'] . ';
                padding: 15px;
                margin-bottom: 15px;
            }
            .document-details-box { 
                padding: 20px; 
                background-color: ' . $template['notes_bg'] . ' !important;
                border-radius: ' . $template['border_radius'] . ';
                border: ' . $template['border_width'] . ' ' . $template['border_style'] . ' ' . $template['accent_color'] . ';
                ' . $boxShadow . '
            }
            .document-details-box table { width: 100%;  }
            .document-details-box td { padding: 10px 0; font-size: 10px; }
            .separator { 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: 3px;
                background: linear-gradient(135deg, ' . $template['accent_color'] . ' 0%, transparent 100%);
                border-radius: 3px;
            }
            .items-table { 
                width: 100%; 
                 
                margin: ' . $template['section_spacing'] . ' 0; 
                border-radius: ' . $template['border_radius'] . ';
                overflow: hidden;
                ' . $boxShadow . '
            }
            .items-table th { 
                background: ' . $template['table_header_bg'] . '; 
                color: ' . $template['table_header_color'] . '; 
                padding: 15px; 
                font-size: 11px; 
                font-weight: 600; 
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .items-table td { 
                padding: 15px; 
                border-bottom: ' . $template['table_border'] . '; 
                font-size: 10px; 
                ' . $letterSpacing . ';
            }
            .items-table tr:nth-child(even) td { 
                background: linear-gradient(135deg, #ffffff 0%, ' . $template['secondary_color'] . ' 100%); 
            }
            .totals { margin-top: ' . $template['section_spacing'] . '; }
            .totals-table { 
                 
                border-radius: ' . $template['border_radius'] . ';
                overflow: hidden;
                ' . $boxShadow . '
            }
            .totals-table td { 
                padding: 12px 15px; 
                font-size: 10px; 
                border-bottom: 1px solid #eee; 
            }
            .total-row { 
                font-weight: 700; 
                font-size: 12px; 
                background: ' . $template['accent_color'] . '; 
                color: white;
            }
            .notes { 
                margin-top: ' . $template['section_spacing'] . '; 
                padding: 20px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
                border: ' . $template['notes_border'] . ';
                border-radius: ' . $template['border_radius'] . ';
                ' . $boxShadow . '
                position: relative;
            }
            .notes:before {
                content: """;
                font-size: 40px;
                color: ' . $template['accent_color'] . ';
                position: absolute;
                top: 5px;
                left: 10px;
                opacity: 0.3;
            }
        ';
    }

    /**
     * Classic template styles - Traditional bordered layout (mPDF compatible)
     */
    private static function getClassicStylesMpdf($template, $letterSpacing)
    {
        return '
            .header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                background-color: ' . $template['header_bg'] . ' !important;
                padding: 15px;
            }
            .company-info { font-size: 10px; line-height: 1.4; ' . $letterSpacing . ' }
            .document-title { 
                font-size: ' . $template['company_title_size'] . '; 
                font-weight: bold; 
                color: ' . $template['accent_color'] . '; 
                margin-bottom: 9px;
                border-bottom: 2px solid ' . $template['accent_color'] . ';
                padding-bottom: 5px;
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                padding: 15px;
            }
            .sub-header-column { vertical-align: top; padding: 10px; }
            .bill-to, .ship-to { 
                padding: 10px; 
                background-color: #fafafa !important;
            }
            .document-details-box { 
                padding: 10px;
                background-color: ' . $template['secondary_color'] . ' !important;
            }
            .document-details-box table { width: 100%; border-collapse: collapse; }
            .document-details-box td { padding: 8px; font-size: 9px; border-bottom: 1px dotted #ccc; }
            .separator { 
                border-top: ' . $template['border_width'] . ' ' . $template['border_style'] . ' ' . $template['accent_color'] . '; 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: 0; 
            }
            .items-table { 
                width: 100%; 
                margin: ' . $template['section_spacing'] . ' 0; 
                border: ' . $template['table_border'] . ';
                border-collapse: collapse;
            }
            .items-table th { 
                background-color: ' . $template['table_header_bg'] . ' !important;
                color: ' . $template['table_header_color'] . ' !important; 
                padding: 12px; 
                font-size: 10px; 
                font-weight: bold; 
                border-bottom: 2px solid ' . $template['accent_color'] . ';
                text-align: left;
            }
            .items-table td { 
                padding: 12px; 
                border-bottom: ' . $template['table_border'] . '; 
                font-size: 9px; 
                ' . $letterSpacing . ';
                vertical-align: top;
            }
            .items-table tr:nth-child(even) td { background-color: #f9f9f9 !important; }
            .totals { margin-top: ' . $template['section_spacing'] . '; }
            .totals-table { 
                border: 1px solid ' . $template['accent_color'] . ';
                border-collapse: collapse;
            }
            .totals-table td { 
                padding: 10px; 
                font-size: 9px; 
                border-bottom: 1px solid #ddd; 
            }
            .total-row { 
                font-weight: bold; 
                font-size: 11px; 
                background-color: ' . $template['secondary_color'] . ' !important; 
            }
            .notes { 
                margin-top: ' . $template['section_spacing'] . '; 
                padding: 15px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
                border: ' . $template['notes_border'] . ';
            }
        ';
    }

    /**
     * Modern template styles - Clean minimal layout (mPDF compatible)
     */
    private static function getModernStylesMpdf($template, $letterSpacing)
    {
        return '
            .header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                background-color: ' . $template['header_bg'] . ' !important;
                padding: 20px 0;
            }
            .company-info { font-size: 11px; line-height: 1.6; ' . $letterSpacing . ' }
            .document-title { 
                font-size: ' . $template['company_title_size'] . '; 
                font-weight: bold; 
                color: ' . $template['accent_color'] . '; 
                margin-bottom: 15px;
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                padding: 0;
            }
            .sub-header-column { vertical-align: top; padding: 0 20px 0 0; }
            .bill-to, .ship-to { 
                padding: 0; 
                background: transparent;
                margin-bottom: 20px;
            }
            .document-details-box { 
                padding: 20px; 
                background-color: ' . $template['secondary_color'] . ' !important;
            }
            .document-details-box table { width: 100%; border-collapse: collapse; }
            .document-details-box td { padding: 10px 0; font-size: 10px; border: none; }
            .separator { 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: 0px;
                background: transparent;
                border: none;
            }
            .items-table { 
                width: 100%; 
                margin: ' . $template['section_spacing'] . ' 0; 
                border-collapse: collapse;
            }
            .items-table th { 
                background-color: ' . $template['table_header_bg'] . ' !important;
                color: ' . $template['table_header_color'] . ' !important; 
                padding: 15px; 
                font-size: 10px; 
                font-weight: 500; 
                border: none;
                text-align: left;
            }
            .items-table td { 
                padding: 15px; 
                font-size: 9px; 
                ' . $letterSpacing . ';
                border: none;
                vertical-align: top;
            }
            .totals { margin-top: ' . $template['section_spacing'] . '; }
            .totals-table { 
                background-color: white !important;
                border-collapse: collapse;
            }
            .totals-table td { 
                padding: 12px 15px; 
                font-size: 10px; 
                border: none;
            }
            .total-row { 
                font-weight: 600; 
                font-size: 12px; 
                background-color: ' . $template['secondary_color'] . ' !important; 
            }
            .notes { 
                margin-top: ' . $template['section_spacing'] . '; 
                padding: 20px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
            }
        ';
    }

    /**
     * Elegant template styles - Sophisticated refined layout (mPDF compatible)
     */
    private static function getElegantStylesMpdf($template, $letterSpacing)
    {
        return '
            .header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                background-color: ' . $template['header_bg'] . ' !important;
                padding: 15px 0;
                border-bottom: 2px solid ' . $template['accent_color'] . ';
            }
            .company-info { font-size: 10px; line-height: 1.5; ' . $letterSpacing . ' }
            .document-title { 
                font-size: ' . $template['company_title_size'] . '; 
                font-weight: 400; 
                color: ' . $template['accent_color'] . '; 
                margin-bottom: 10px;
                border-bottom: 2px solid ' . $template['accent_color'] . ';
                padding-bottom: 5px;
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                padding: 0;
            }
            .sub-header-column { vertical-align: top; padding: 0 15px 0 0; }
            .bill-to, .ship-to { 
                padding: 0 0 0 10px; 
                background: transparent;
                border-left: 0px solid ' . $template['accent_color'] . ';
            }
            .document-details-box { 
                padding: 15px; 
                background-color: ' . $template['secondary_color'] . ' !important;
                border-left: 4px solid ' . $template['accent_color'] . ';
            }
            .document-details-box table { width: 100%; }
            .document-details-box td { padding: 8px 0; font-size: 9px; }
            .separator { 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: 1px;
                background: ' . $template['accent_color'] . ';
                border: none;
            }
            .items-table { 
                width: 100%; 
                margin: ' . $template['section_spacing'] . ' 0; 
                border-collapse: collapse;
            }
            .items-table th { 
                background-color: ' . $template['table_header_bg'] . ' !important;
                color: ' . $template['table_header_color'] . ' !important; 
                padding: 12px; 
                font-size: 10px; 
                font-weight: 500; 
                border-bottom: 2px solid ' . $template['accent_color'] . ';
                text-align: left;
            }
            .items-table td { 
                padding: 12px; 
                border-bottom: ' . $template['table_border'] . '; 
                font-size: 9px; 
                ' . $letterSpacing . ';
                vertical-align: top;
            }
            .items-table tr:nth-child(odd) td { background-color: #fafafa !important; }
            .totals { margin-top: ' . $template['section_spacing'] . '; }
            .totals-table { 
                border-left: 3px solid ' . $template['accent_color'] . ';
            }
            .totals-table td { 
                padding: 10px; 
                font-size: 9px; 
                border-bottom: 1px dotted #ccc; 
            }
            .total-row { 
                font-weight: 500; 
                font-size: 11px; 
                background-color: ' . $template['secondary_color'] . ' !important; 
                border-left: 4px solid ' . $template['accent_color'] . ';
            }
            .notes { 
                margin-top: ' . $template['section_spacing'] . '; 
                padding: 15px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
                border-left: 3px solid ' . $template['accent_color'] . ';
            }
        ';
    }

    /**
     * Corporate template styles - Bold structured layout (mPDF compatible)
     */
    private static function getCorporateStylesMpdf($template, $letterSpacing)
    {
        return '
            .header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                background-color: ' . $template['header_bg'] . ' !important;
                border: ' . $template['border_width'] . ' ' . $template['border_style'] . ' ' . $template['accent_color'] . ';
                padding: 20px;
            }
            .company-info { font-size: 11px; line-height: 1.4; ' . $letterSpacing . ' font-weight: 500; }
            .document-title { 
                font-size: ' . $template['company_title_size'] . '; 
                font-weight: bold; 
                color: white; 
                background-color: ' . $template['accent_color'] . ' !important;
                padding: 2px 6px;
                margin: -20px -20px 20px -20px;
            }
			.document-subtitle{
				padding: 10px 6px;
			}
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                border: 2px solid ' . $template['accent_color'] . ';
                padding: 15px;
            }
            .sub-header-column { vertical-align: top; padding: 10px; }
            .bill-to, .ship-to { 
                padding: 10px; 
                margin-bottom: 10px;
            }
            .document-details-box { 
                padding: 15px; 
                background-color: ' . $template['accent_color'] . ' !important;
                color: white;
                font-weight: bold;
            }
            .document-details-box table { width: 100%; }
            .document-details-box td { padding: 8px; font-size: 10px; color: white; }
            .separator { 
                border-top: ' . $template['border_width'] . ' ' . $template['border_style'] . ' ' . $template['accent_color'] . '; 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: 0;
            }
            .items-table { 
                width: 100%; 
                margin: ' . $template['section_spacing'] . ' 0; 
                border: ' . $template['table_border'] . ';
            }
            .items-table th { 
                background-color: ' . $template['table_header_bg'] . ' !important;
                color: ' . $template['table_header_color'] . ' !important; 
                padding: 15px; 
                font-size: 11px; 
                font-weight: bold; 
                border-bottom: 3px solid ' . $template['accent_color'] . ';
                text-align: left;
            }
            .items-table td { 
                padding: 15px; 
                border: 1px solid #aaa; 
                font-size: 10px; 
                ' . $letterSpacing . ';
                vertical-align: top;
            }
            .totals { margin-top: ' . $template['section_spacing'] . '; }
            .totals-table { 
                border: 2px solid ' . $template['accent_color'] . ';
            }
            .totals-table td { 
                padding: 12px; 
                font-size: 10px; 
                border: 1px solid #aaa; 
                font-weight: 500;
            }
            .total-row td { 
                font-weight: bold; 
                font-size: 12px; 
                background: ' . $template['accent_color'] . '; 
                color: white;
            }
            .notes { 
                margin-top: ' . $template['section_spacing'] . '; 
                padding: 15px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
                border: ' . $template['notes_border'] . ';
                font-weight: 500;
            }
        ';
    }

    /**
     * Creative template styles - Dynamic artistic layout (mPDF compatible)
     */
    private static function getCreativeStylesMpdf($template, $letterSpacing)
    {
        return '
            .header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                background-color: #f9fafb;
                padding: 25px;
                border: 1px solid #e5e7eb;
            }
            .company-info { font-size: 10px; line-height: 1.5; ' . $letterSpacing . ' }
            .document-title { 
                font-size: ' . $template['company_title_size'] . '; 
                font-weight: bold; 
                color: ' . $template['accent_color'] . '; 
                margin-bottom: 15px;
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                padding: 0;
            }
            .sub-header-column { vertical-align: top; padding: 0 15px 0 0; }
            .sub-header-column { 
                vertical-align: top; 
                padding: 0 15px 0 0; 
            }
            .bill-to, .ship-to { 
                background-color: ' . $template['secondary_color'] . ';
                margin-bottom: 20px;
                border: 2px dashed ' . $template['accent_color'] . ';
                padding: 15px;
                border-radius: 8px;
            }
            .document-details-box { 
                padding: 20px; 
                background: linear-gradient(315deg, #f9fafb 0%, #f3f4f6 100%);
                border: 2px dashed ' . $template['accent_color'] . ';
                border-radius: 8px;
            }
            .document-details-box table { width: 100%; }
            .document-details-box td { padding: 10px 0; font-size: 10px; }
            .separator { 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: 3px;
                background: linear-gradient(315deg, ' . $template['accent_color'] . ' 0%, transparent 100%);
                border: none;
                border-radius: 3px;
            }
            .items-table { 
                width: 100%; 
                margin: ' . $template['section_spacing'] . ' 0; 
                border-collapse: collapse;
                border-radius: 8px;
                overflow: hidden;
            }
            .items-table th { 
                background: linear-gradient(315deg, ' . $template['accent_color'] . ' 0%, #a855f7 100%);
                color: ' . $template['table_header_color'] . ' !important; 
                padding: 20px; 
                font-size: 11px; 
                font-weight: 600; 
                text-align: left;
            }
            .items-table td { 
                padding: 15px; 
                border-bottom: ' . $template['table_border'] . '; 
                font-size: 10px; 
                ' . $letterSpacing . ';
                vertical-align: top;
            }
            .items-table tr:nth-child(even) td { 
                background-color: ' . $template['secondary_color'] . ' !important; 
            }
            .totals { margin-top: ' . $template['section_spacing'] . '; }
            .totals-table { 
            }
            .totals-table td { 
                padding: 12px 15px; 
                font-size: 10px; 
                border-bottom: 1px solid #eee; 
            }
            .total-row { 
                font-weight: 700; 
                font-size: 12px; 
                background: ' . $template['accent_color'] . '; 
                color: white;
            }
            .notes { 
                margin-top: ' . $template['section_spacing'] . '; 
                padding: 20px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
                border: ' . $template['notes_border'] . ';
                border-radius: 8px;
            }
        ';
    }

    /**
     * Generate preview CSS styles for web display
     *
     * @param string $templateId
     * @return string
     */
    public static function generatePreviewStyles($templateId)
    {
        $template = self::getTemplate($templateId) ?? self::getTemplate('classic');
        
        $baseStyles = '
        <style>
			body.dark-mode .document-preview-container h2,
            .document-preview-container h2,
			body.dark-mode .document-preview-container div,
            .document-preview-container div,
			body.dark-mode .document-preview-container span,
            .document-preview-container span,
			.document-preview-container { 
                font-family: ' . $template['font_family'] . ';
            }
			.document-preview-container { 
                background: white; 
                box-shadow: 0 0 20px rgba(0,0,0,0.1); 
                color: ' . $template['text_color'] . ';
            }
            .logo { max-height: 120px; max-width: 405px; height: auto; }
            .paid-watermark {
                position: absolute; 
                top: 50%; 
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 120px; 
                font-weight: bold; 
                color: rgba(220, 220, 220, ' . $template['watermark_opacity'] . ');
                z-index: 1000; 
                pointer-events: none; 
                user-select: none;
            }
        ';
        
        $templateSpecificStyles = self::getPreviewTemplateStyles($template);
        
        return $baseStyles . $templateSpecificStyles . '</style>';
    }

    /**
     * Generate template-specific preview styles
     *
     * @param array $template
     * @return string
     */
    private static function getPreviewTemplateStyles($template)
    {
        switch ($template['layout_type']) {
            case 'traditional':
                return self::getClassicPreviewStyles($template);
            case 'spacious':
                return self::getModernPreviewStyles($template);
            case 'refined':
                return self::getElegantPreviewStyles($template);
            case 'structured':
                return self::getCorporatePreviewStyles($template);
            case 'dynamic':
                return self::getCreativePreviewStyles($template);
            default:
                return self::getClassicPreviewStyles($template);
        }
    }

    /**
     * Classic template preview styles
     */
    private static function getClassicPreviewStyles($template)
    {
        return '
            .document-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                border-radius: ' . $template['border_radius'] . ';
                padding: 20px;
                background: ' . $template['header_bg'] . ' !important;
            }
            .document-header table { width: 100%; border-collapse: collapse; }
            body.dark-mode .company-info,.company-info, .company-info {
               font-size: 16px;color: black !important;
            }
			.company-info h2{
                margin: 0 0 20px 0; 
			}
            /* Dark Mode Styles - maintain template colors */
            body.dark-mode .company-info h2 span,
			.company-info h2 span{
                color: ' . $template['accent_color'] . ' !important; 
                font-size: 32px;
				font-weight: bold;
                border-bottom: 2px solid ' . $template['accent_color'] . ';
                padding-bottom: 8px;
            }
			body.dark-mode .logo-section div,
            .logo-section div{ 
                color: ' . $template['accent_color'] . ' !important; 
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                border-radius: ' . $template['border_radius'] . ';
                padding: 5px;
            }
            .sub-header table { width: 100%; border-collapse: collapse; }
            .sub-header-column { vertical-align: top; padding: 15px; width: 33.33%; }
            .bill-to, .ship-to { 
                padding: 0px; 
                border: none;
                border-radius: 0;
                background: transparent;
                margin-bottom: 15px;
            }
            .document-details-box { 
                padding: 15px;
                border: 1px solid ' . $template['accent_color'] . ';
                border-radius: 4px;
                background-color: ' . $template['secondary_color'] . ' !important;
            }
            .document-details-box table { width: 100%; border-collapse: collapse; }
            .document-details-box td { padding: 13px 0; text-indent: 0; border-bottom: 1px dotted #ccc; }
            .separator { 
                border-top: ' . $template['border_width'] . ' ' . $template['border_style'] . ' ' . $template['accent_color'] . '; 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: 0;
            }
            .items-table { 
                width: 100%; 
                border-collapse: collapse;
                margin: ' . $template['section_spacing'] . ' 0; 
                border: ' . $template['table_border'] . ';
                border-radius: ' . $template['border_radius'] . ';
            }
            .items-table thead th { 
                --table-header-bg: ' . $template['table_header_bg'] . ';
                --table-header-color: ' . $template['table_header_color'] . ';
                background-color: ' . $template['table_header_bg'] . ' !important; 
                background: ' . $template['table_header_bg'] . ' !important; 
                color: ' . $template['table_header_color'] . ' !important; 
                padding: 15px; 
                text-align: left; 
                border-bottom: 2px solid ' . $template['accent_color'] . ';
                font-size: 14px;
                font-weight: bold;
            }
            .items-table th { 
                background-color: ' . $template['table_header_bg'] . ' !important; 
                background: ' . $template['table_header_bg'] . ' !important; 
                color: ' . $template['table_header_color'] . ' !important; 
            }
            .items-table td { 
                padding: 15px; 
                border-bottom: ' . $template['table_border'] . '; 
                background: ' . ($template['table_stripe'] ? '#f9f9f9' : 'transparent') . ';
                vertical-align: top;
            }
            .items-table tr:nth-child(even) td { background: #f9f9f9; }
            .items-table .text-right { text-align: right; }
            .totals-section { margin-top: 30px; }
            .totals-table { 
                width: 350px; 
                margin-left: auto; 
                border-collapse: collapse;
                border: 1px solid ' . $template['accent_color'] . ';
                border-radius: 4px;
            }
            .totals-table td { 
                padding: 15px; 
                border-bottom: 1px solid #ddd; 
            }
            .total-row { 
                background-color: ' . $template['secondary_color'] . ' !important; 
                font-weight: bold; 
                font-size: 16px; 
            }
            body.dark-mode div.notes-section,
			.notes-section { 
                margin-top: 30px; 
                padding: 20px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
                border: ' . $template['notes_border'] . ';
                border-radius: ' . $template['border_radius'] . ';
            }
            .paid-row { background: #e8f5e8 !important; }
            .balance-due-paid { background: #d4edda !important; }
            .balance-due-unpaid { background: #fff3cd !important; }
            
            /* Dark Mode Styles - maintain template colors */
            body.dark-mode .company-info h2 span,
			.company-info h2 span {
                color: ' . $template['accent_color'] . ' !important;
                border-bottom-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .document-header,
            body.dark-mode .estimate-preview-container .document-header,
            body.dark-mode .invoice-preview-wrapper .document-header,
            body.dark-mode .estimate-preview-wrapper .document-header {
                border-color: ' . $template['accent_color'] . ' !important;
                background: ' . $template['header_bg'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .document-header td,
            body.dark-mode .estimate-preview-container .document-header td,
            body.dark-mode .invoice-preview-wrapper .document-header td,
            body.dark-mode .estimate-preview-wrapper .document-header td {
                background: ' . $template['header_bg'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .sub-header,
            body.dark-mode .estimate-preview-container .sub-header,
            body.dark-mode .invoice-preview-wrapper .sub-header,
            body.dark-mode .estimate-preview-wrapper .sub-header {
                border-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .document-details-box,
            body.dark-mode .estimate-preview-container .document-details-box,
            body.dark-mode .invoice-preview-wrapper .document-details-box,
            body.dark-mode .estimate-preview-wrapper .document-details-box {
                border-color: ' . $template['accent_color'] . ' !important;
                background-color: ' . $template['secondary_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .separator,
            body.dark-mode .estimate-preview-container .separator,
            body.dark-mode .invoice-preview-wrapper .separator,
            body.dark-mode .estimate-preview-wrapper .separator {
                border-top-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .totals-table,
            body.dark-mode .estimate-preview-container .totals-table,
            body.dark-mode .invoice-preview-wrapper .totals-table,
            body.dark-mode .estimate-preview-wrapper .totals-table {
                border-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .total-row,
            body.dark-mode .estimate-preview-container .total-row,
            body.dark-mode .invoice-preview-wrapper .total-row,
            body.dark-mode .estimate-preview-wrapper .total-row {
                background-color: ' . $template['secondary_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .notes-section,
            body.dark-mode .estimate-preview-container .notes-section,
            body.dark-mode .invoice-preview-wrapper .notes-section,
            body.dark-mode .estimate-preview-wrapper .notes-section {
                background-color: ' . $template['notes_bg'] . ' !important;
                border-color: ' . $template['accent_color'] . ' !important;
            }
        ';
    }

    /**
     * Modern template preview styles
     */
    private static function getModernPreviewStyles($template)
    {
        return '
            .document-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                padding: 25px 0;
                background: ' . $template['header_bg'] . ';
            }
            .document-header table { width: 100%;  }
            body.dark-mode .company-info,.company-info, .company-info {
               font-size: 18px;color: black !important;
            }
            body.dark-mode .company-info h2 span,
            .company-info h2 span{ 
                color: ' . $template['accent_color'] . ' !important; 
                margin: 0 0 15px 0; 
                font-size: 36px;
                font-weight: bold;
                letter-spacing: 1px;
            }
			body.dark-mode .logo-section div,
            .logo-section { 
                color: ' . $template['accent_color'] . ' !important; 
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                padding: 0;
            }
            .sub-header table { width: 100%;  }
            .sub-header-column { vertical-align: top; padding: 0 25px 0 0; width: 33.33%; }
            .bill-to, .ship-to { 
                padding: 0; 
                background: transparent;
                margin-bottom: 25px;
            }
            .document-details-box { 
                padding: 25px;
                background-color: ' . $template['secondary_color'] . ' !important;
                border-radius: ' . $template['border_radius'] . ';
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .document-details-box table { width: 100%;  }
            .document-details-box td { padding: 15px 0; text-indent: 0; border: none; }
            .separator { 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: ' . $template['section_spacing'] . ';
                background: transparent;
                border: none;
            }
            .items-table { 
                width: 100%; 
                 
                margin: ' . $template['section_spacing'] . ' 0; 
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .items-table th { 
                --table-header-bg: ' . $template['table_header_bg'] . ';
                --table-header-color: ' . $template['table_header_color'] . ';
                background: ' . $template['table_header_bg'] . ' !important; 
                color: ' . $template['table_header_color'] . ' !important; 
                padding: 18px; 
                text-align: left; 
                font-weight: 500;
                border: none;
            }
            .items-table td { 
                padding: 18px; 
                border: none;
            }
            .items-table .text-right { text-align: right; }
            .totals-section { margin-top: 30px; }
            .totals-table { 
                width: 350px; 
                margin-left: auto; 
                 
                background: white;
                border-radius: ' . $template['border_radius'] . ';
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .totals-table td { 
                padding: 18px; 
                border: none; 
            }
            .total-row { 
                background-color: ' . $template['secondary_color'] . ' !important; 
                font-weight: 600; 
                font-size: 18px; 
            }
            body.dark-mode div.notes-section,
            .notes-section { 
                margin-top: 30px; 
                padding: 25px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
                border-radius: ' . $template['border_radius'] . ';
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .paid-row { background: #e8f5e8 !important; }
            .balance-due-paid { background: #d4edda !important; }
            .balance-due-unpaid { background: #fff3cd !important; }
            
            body.dark-mode .invoice-preview-container .document-header,
            body.dark-mode .estimate-preview-container .document-header,
            body.dark-mode .invoice-preview-wrapper .document-header,
            body.dark-mode .estimate-preview-wrapper .document-header {
                background: ' . $template['header_bg'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .document-details-box,
            body.dark-mode .estimate-preview-container .document-details-box,
            body.dark-mode .invoice-preview-wrapper .document-details-box,
            body.dark-mode .estimate-preview-wrapper .document-details-box {
                background-color: ' . $template['secondary_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .total-row,
            body.dark-mode .estimate-preview-container .total-row,
            body.dark-mode .invoice-preview-wrapper .total-row,
            body.dark-mode .estimate-preview-wrapper .total-row {
                background-color: ' . $template['secondary_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .notes-section,
            body.dark-mode .estimate-preview-container .notes-section,
            body.dark-mode .invoice-preview-wrapper .notes-section,
            body.dark-mode .estimate-preview-wrapper .notes-section {
                background-color: ' . $template['notes_bg'] . ' !important;
            }
        ';
    }

    /**
     * Elegant template preview styles
     */
    private static function getElegantPreviewStyles($template)
    {
        return '
            .document-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                padding: 20px 0;
                background: ' . $template['header_bg'] . ';
                border-bottom: 2px solid ' . $template['accent_color'] . ';
            }
            .document-header table { width: 100%;  }
            body.dark-mode .company-info,.company-info, .company-info {
               font-size: 17px;color: black !important;
            }
            body.dark-mode .company-info h2 span,
            .company-info h2 span{ 
                color: ' . $template['accent_color'] . ' !important; 
                margin: 0 0 15px 0; 
                font-size: 34px;
                font-weight: 400;
                position: relative;
            }
            .company-info h2:after {
                content: "";
                position: absolute;
                bottom: -8px;
                left: 0;
                width: 50px;
                height: 2px;
                background: ' . $template['accent_color'] . ';
            }
			body.dark-mode .logo-section div,
            .logo-section { 
                color: ' . $template['accent_color'] . ' !important; 
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                padding: 0;
            }
            .sub-header table { width: 100%;  }
            .sub-header-column { vertical-align: top; padding: 0 20px 0 0; width: 33.33%; }
            .bill-to, .ship-to { 
                padding: 0 0 0 10px; 
                background: transparent;
                border-left: 3px solid ' . $template['accent_color'] . ';
                margin-bottom: 20px;
            }
            .document-details-box { 
                padding: 20px;
                background-color: ' . $template['secondary_color'] . ' !important;
                border-radius: ' . $template['border_radius'] . ';
                border-left: 4px solid ' . $template['accent_color'] . ';
            }
            .document-details-box table { width: 100%;  }
            .document-details-box td { padding: 12px 0; text-indent: 0; }
            .separator { 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: 1px;
                background: linear-gradient(to right, ' . $template['accent_color'] . ', transparent);
                border: none;
            }
            .items-table { 
                width: 100%; 
                 
                margin: ' . $template['section_spacing'] . ' 0; 
            }
            .items-table th { 
                --table-header-bg: transparent;
                --table-header-color: ' . $template['accent_color'] . ';
                background: transparent !important; 
                color: ' . $template['accent_color'] . ' !important; 
                padding: 15px; 
                text-align: left; 
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border-bottom: 2px solid ' . $template['accent_color'] . ';
            }
            .items-table td { 
                padding: 15px; 
                border-bottom: ' . $template['table_border'] . '; 
            }
            .items-table tr:nth-child(odd) td { background-color: #fafafa !important; }
            .items-table .text-right { text-align: right; }
            .totals-section { margin-top: 30px; }
            .totals-table { 
                width: 350px; 
                margin-left: auto; 
                 
                border-left: 3px solid ' . $template['accent_color'] . ';
            }
            .totals-table td { 
                padding: 15px; 
                border-bottom: 1px dotted #ccc; 
            }
            .total-row { 
                background-color: ' . $template['secondary_color'] . ' !important; 
                font-weight: 500; 
                font-size: 16px; 
                border-left: 4px solid ' . $template['accent_color'] . ';
            }
            body.dark-mode div.notes-section,
            .notes-section { 
                margin-top: 30px; 
                padding: 20px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
                border-left: 3px solid ' . $template['accent_color'] . ';
                border-radius: 0 ' . $template['border_radius'] . ' ' . $template['border_radius'] . ' 0;
                font-style: italic;
            }
            .paid-row { background: #e8f5e8 !important; }
            .balance-due-paid { background: #d4edda !important; }
            .balance-due-unpaid { background: #fff3cd !important; }
            
            body.dark-mode .invoice-preview-container .company-info h2:after,
            body.dark-mode .estimate-preview-container .company-info h2:after,
            body.dark-mode .invoice-preview-wrapper .company-info h2:after,
            body.dark-mode .estimate-preview-wrapper .company-info h2:after {
                background: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .document-header,
            body.dark-mode .estimate-preview-container .document-header,
            body.dark-mode .invoice-preview-wrapper .document-header,
            body.dark-mode .estimate-preview-wrapper .document-header {
                background: ' . $template['header_bg'] . ' !important;
                border-bottom-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .bill-to,
            body.dark-mode .estimate-preview-container .bill-to,
            body.dark-mode .invoice-preview-wrapper .bill-to,
            body.dark-mode .estimate-preview-wrapper .bill-to,
            body.dark-mode .invoice-preview-container .ship-to,
            body.dark-mode .estimate-preview-container .ship-to,
            body.dark-mode .invoice-preview-wrapper .ship-to,
            body.dark-mode .estimate-preview-wrapper .ship-to {
                border-left-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .document-details-box,
            body.dark-mode .estimate-preview-container .document-details-box,
            body.dark-mode .invoice-preview-wrapper .document-details-box,
            body.dark-mode .estimate-preview-wrapper .document-details-box {
                background-color: ' . $template['secondary_color'] . ' !important;
                border-left-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .separator,
            body.dark-mode .estimate-preview-container .separator,
            body.dark-mode .invoice-preview-wrapper .separator,
            body.dark-mode .estimate-preview-wrapper .separator {
                background: linear-gradient(to right, ' . $template['accent_color'] . ', transparent) !important;
            }
            body.dark-mode .invoice-preview-container .items-table thead th,
            body.dark-mode .estimate-preview-container .items-table thead th,
            body.dark-mode .invoice-preview-wrapper .items-table thead th,
            body.dark-mode .estimate-preview-wrapper .items-table thead th {
                color: ' . $template['accent_color'] . ' !important;
                border-bottom-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .total-row,
            body.dark-mode .estimate-preview-container .total-row,
            body.dark-mode .invoice-preview-wrapper .total-row,
            body.dark-mode .estimate-preview-wrapper .total-row {
                background-color: ' . $template['secondary_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .notes-section,
            body.dark-mode .estimate-preview-container .notes-section,
            body.dark-mode .invoice-preview-wrapper .notes-section,
            body.dark-mode .estimate-preview-wrapper .notes-section {
                background-color: ' . $template['notes_bg'] . ' !important;
                border-left-color: ' . $template['accent_color'] . ' !important;
            }
        ';
    }

    /**
     * Corporate template preview styles
     */
    private static function getCorporatePreviewStyles($template)
    {
        return '
            .document-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                background: ' . $template['header_bg'] . ';
                border: ' . $template['border_width'] . ' ' . $template['border_style'] . ' ' . $template['accent_color'] . ';
                padding: 25px;
            }
            .document-header table { width: 100%;  }
            body.dark-mode .company-info,.company-info, .company-info { font-size: 18px; font-weight: 500; color: black !important; }
			.dark-mode .estimate-preview-wrapper *:not(table):not(thead):not(th):not(.items-table thead th) .company-info h2 span,
            .company-info h2  span{ 
                color: white !important; 
                font-size: 38px;
                font-weight: bold;
                text-transform: uppercase;
                background: ' . $template['accent_color'] . ' !important;
                padding: 5px;
            }
			body.dark-mode .logo-section div,
            .logo-section { 
                color: ' . $template['accent_color'] . ' !important; 
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                border: 2px solid ' . $template['accent_color'] . ';
                padding: 20px;
            }
            .sub-header table { width: 100%;  }
            .sub-header-column { vertical-align: top; padding: 15px; width: 33.33%; }
            .bill-to, .ship-to { 
                margin-bottom: 15px;
            }
            .document-details-box { 
                padding: 20px;
                background-color: ' . $template['accent_color'] . ' !important;
                color: white;
                font-weight: bold;
            }
            .document-details-box table { width: 100%;  }
            .document-details-box td { padding: 12px 0; text-indent: 0; color: white; }
            .separator { 
                border-top: ' . $template['border_width'] . ' ' . $template['border_style'] . ' ' . $template['accent_color'] . '; 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: 0;
            }
            .items-table { 
                width: 100%; 
                 
                margin: ' . $template['section_spacing'] . ' 0; 
                border: ' . $template['table_border'] . ';
            }
            .items-table th { 
                --table-header-bg: ' . $template['table_header_bg'] . ';
                --table-header-color: ' . $template['table_header_color'] . ';
                background: ' . $template['table_header_bg'] . ' !important; 
                color: ' . $template['table_header_color'] . ' !important; 
                padding: 18px; 
                text-align: left; 
                font-weight: bold;
                text-transform: uppercase;
                border-bottom: 3px solid ' . $template['accent_color'] . ';
            }
            .items-table td { 
                padding: 18px; 
                border: 1px solid #ddd; 
            }
            .items-table .text-right { text-align: right; }
            .totals-section { margin-top: 30px; }
            .totals-table { 
                width: 350px; 
                margin-left: auto; 
                 
                border: 2px solid ' . $template['accent_color'] . ';
            }
            .totals-table td { 
                padding: 18px; 
                border: 1px solid #ddd; 
                font-weight: 500;
            }
            body.dark-mode .invoice-preview-container .totals-section tbody .total-row td,
            body.dark-mode .estimate-preview-container .totals-section tbody .total-row td,
            body.dark-mode .invoice-preview-wrapper .totals-section tbody .total-row td,
            body.dark-mode .estimate-preview-wrapper .totals-section tbody .total-row td,
			body.dark-mode .total-row td strong,
			.totals-table .total-row td,
			.totals-table .total-row td strong { 
                background: ' . $template['accent_color'] . '; 
                color: white !important;
                font-weight: bold; 
                font-size: 18px; 
            }
            body.dark-mode div.notes-section,
            .notes-section { 
                margin-top: 30px; 
                padding: 20px; 
                background-color: ' . $template['notes_bg'] . ' !important; 
                border: ' . $template['notes_border'] . ';
                font-weight: 500;
            }
            .paid-row { background: #e8f5e8 !important; }
            .balance-due-paid { background: #d4edda !important; }
            .balance-due-unpaid { background: #fff3cd !important; }
            
            body.dark-mode .invoice-preview-container .document-header,
            body.dark-mode .estimate-preview-container .document-header,
            body.dark-mode .invoice-preview-wrapper .document-header,
            body.dark-mode .estimate-preview-wrapper .document-header {
                background: ' . $template['header_bg'] . ' !important;
                border-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .sub-header,
            body.dark-mode .estimate-preview-container .sub-header,
            body.dark-mode .invoice-preview-wrapper .sub-header,
            body.dark-mode .estimate-preview-wrapper .sub-header {
                border-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .bill-to,
            body.dark-mode .estimate-preview-container .bill-to,
            body.dark-mode .invoice-preview-wrapper .bill-to,
            body.dark-mode .estimate-preview-wrapper .bill-to,
            body.dark-mode .invoice-preview-container .ship-to,
            body.dark-mode .estimate-preview-container .ship-to,
            body.dark-mode .invoice-preview-wrapper .ship-to,
            body.dark-mode .estimate-preview-wrapper .ship-to {
                border-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .document-details-box,
            body.dark-mode .estimate-preview-container .document-details-box,
            body.dark-mode .invoice-preview-wrapper .document-details-box,
            body.dark-mode .estimate-preview-wrapper .document-details-box {
                background-color: ' . $template['accent_color'] . ' !important;
                color: white !important;
            }
            body.dark-mode .invoice-preview-container .separator,
            body.dark-mode .estimate-preview-container .separator,
            body.dark-mode .invoice-preview-wrapper .separator,
            body.dark-mode .estimate-preview-wrapper .separator {
                border-top-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .items-table,
            body.dark-mode .estimate-preview-container .items-table,
            body.dark-mode .invoice-preview-wrapper .items-table,
            body.dark-mode .estimate-preview-wrapper .items-table {
                border-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .items-table thead th,
            body.dark-mode .estimate-preview-container .items-table thead th,
            body.dark-mode .invoice-preview-wrapper .items-table thead th,
            body.dark-mode .estimate-preview-wrapper .items-table thead th {
                border-bottom-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .items-table td,
            body.dark-mode .estimate-preview-container .items-table td,
            body.dark-mode .invoice-preview-wrapper .items-table td,
            body.dark-mode .estimate-preview-wrapper .items-table td {
                border-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .totals-table,
            body.dark-mode .estimate-preview-container .totals-table,
            body.dark-mode .invoice-preview-wrapper .totals-table,
            body.dark-mode .estimate-preview-wrapper .totals-table {
                border-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .total-row,
            body.dark-mode .estimate-preview-container .total-row,
            body.dark-mode .invoice-preview-wrapper .total-row,
            body.dark-mode .estimate-preview-wrapper .total-row {
                background-color: ' . $template['secondary_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .notes-section,
            body.dark-mode .estimate-preview-container .notes-section,
            body.dark-mode .invoice-preview-wrapper .notes-section,
            body.dark-mode .estimate-preview-wrapper .notes-section {
                background-color: ' . $template['notes_bg'] . ' !important;
                border-color: ' . $template['accent_color'] . ' !important;
            }
        ';
    }

    /**
     * Creative template preview styles
     */
    private static function getCreativePreviewStyles($template)
    {
        return '
            .document-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
                border-radius: ' . $template['border_radius'] . ';
                padding: 25px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                position: relative;
                overflow: hidden;
            }
            .document-header:before {
                content: "";
                position: absolute;
                top: -50%;
                right: -20px;
                width: 100px;
                height: 200%;
                background: ' . $template['accent_color'] . ';
                opacity: 0.1;
                transform: rotate(15deg);
            }
            .document-header table { width: 100%;  position: relative; z-index: 2; }
            body.dark-mode .company-info,.company-info, .company-info { font-size: 17px; color: black !important; }
            body.dark-mode .company-info h2 span,
            .company-info h2 span{ 
                color: ' . $template['accent_color'] . ' !important; 
                margin: 0 0 20px 0; 
                font-size: 40px;
                font-weight: 700;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            }
			body.dark-mode .logo-section div,
            .logo-section { 
                color: ' . $template['accent_color'] . ' !important; 
            }
            .sub-header { 
                margin-bottom: ' . $template['section_spacing'] . '; 
                padding: 0;
            }
            .sub-header table { width: 100%;  }
            .sub-header-column { vertical-align: top; padding: 0 20px 0 0; width: 33.33%; }
            .bill-to, .ship-to { 
                padding: 20px; 
                background: linear-gradient(135deg, #ffffff 0%, ' . $template['secondary_color'] . ' 100%);
                border-radius: ' . $template['border_radius'] . ';
                border: ' . $template['border_width'] . ' dashed ' . $template['accent_color'] . ';
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .document-details-box { 
                padding: 25px;
                background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
                border-radius: ' . $template['border_radius'] . ';
                border: ' . $template['border_width'] . ' dashed ' . $template['accent_color'] . ';
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .document-details-box table { width: 100%;  }
            .document-details-box td { padding: 15px 0; text-indent: 0; }
            .separator { 
                margin: ' . $template['section_spacing'] . ' 0; 
                height: 3px;
                background: linear-gradient(135deg, ' . $template['accent_color'] . ' 0%, transparent 100%);
                border-radius: 3px;
                border: none;
            }
            .items-table { 
                width: 100%; 
                 
                margin: ' . $template['section_spacing'] . ' 0; 
                border-radius: ' . $template['border_radius'] . ';
                overflow: hidden;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .items-table th { 
                --table-header-bg: linear-gradient(135deg, ' . $template['accent_color'] . ' 0%, #a855f7 100%);
                --table-header-color: white;
                background: linear-gradient(135deg, ' . $template['accent_color'] . ' 0%, #a855f7 100%) !important; 
                color: white !important; 
                padding: 20px; 
                text-align: left; 
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .items-table td { 
                padding: 20px; 
                border-bottom: 2px solid #e5e7eb; 
            }
            .items-table tr:nth-child(even) td { 
                background: linear-gradient(135deg, #ffffff 0%, ' . $template['secondary_color'] . ' 100%); 
            }
            .items-table .text-right { text-align: right; }
            .totals-section { margin-top: 30px; }
            .totals-table { 
                width: 350px; 
                margin-left: auto; 
                 
                border-radius: ' . $template['border_radius'] . ';
                overflow: hidden;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .totals-table td { 
                padding: 18px; 
                border-bottom: 1px solid #eee; 
            }
            .total-row { 
                background: ' . $template['accent_color'] . '; 
                color: white;
                font-weight: 700; 
                font-size: 18px; 
            }
            body.dark-mode div.notes-section,
            .notes-section { 
                margin-top: 30px; 
                padding: 25px; 
                background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); 
                border: 2px dashed ' . $template['accent_color'] . ';
                border-radius: ' . $template['border_radius'] . ';
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                position: relative;
            }
            .notes-section:before {
                content: """;
                font-size: 50px;
                color: ' . $template['accent_color'] . ';
                position: absolute;
                top: 8px;
                left: 15px;
                opacity: 0.3;
            }
            .paid-row { background: #e8f5e8 !important; }
            .balance-due-paid { background: #d4edda !important; }
            .balance-due-unpaid { background: #fff3cd !important; }
            
            body.dark-mode .invoice-preview-container .document-header,
            body.dark-mode .estimate-preview-container .document-header,
            body.dark-mode .invoice-preview-wrapper .document-header,
            body.dark-mode .estimate-preview-wrapper .document-header {
                background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
            }
            body.dark-mode .invoice-preview-container .document-header:before,
            body.dark-mode .estimate-preview-container .document-header:before,
            body.dark-mode .invoice-preview-wrapper .document-header:before,
            body.dark-mode .estimate-preview-wrapper .document-header:before {
                background: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .bill-to,
            body.dark-mode .estimate-preview-container .bill-to,
            body.dark-mode .invoice-preview-wrapper .bill-to,
            body.dark-mode .estimate-preview-wrapper .bill-to,
            body.dark-mode .invoice-preview-container .ship-to,
            body.dark-mode .estimate-preview-container .ship-to,
            body.dark-mode .invoice-preview-wrapper .ship-to,
            body.dark-mode .estimate-preview-wrapper .ship-to {
                background: linear-gradient(135deg, #ffffff 0%, ' . $template['secondary_color'] . ' 100%) !important;
                border-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .document-details-box,
            body.dark-mode .estimate-preview-container .document-details-box,
            body.dark-mode .invoice-preview-wrapper .document-details-box,
            body.dark-mode .estimate-preview-wrapper .document-details-box {
                background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%) !important;
                border-color: ' . $template['accent_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .separator,
            body.dark-mode .estimate-preview-container .separator,
            body.dark-mode .invoice-preview-wrapper .separator,
            body.dark-mode .estimate-preview-wrapper .separator {
                background: linear-gradient(135deg, ' . $template['accent_color'] . ' 0%, transparent 100%) !important;
            }
            body.dark-mode .invoice-preview-container .total-row,
            body.dark-mode .estimate-preview-container .total-row,
            body.dark-mode .invoice-preview-wrapper .total-row,
            body.dark-mode .estimate-preview-wrapper .total-row {
                background-color: ' . $template['secondary_color'] . ' !important;
            }
            body.dark-mode .invoice-preview-container .notes-section,
            body.dark-mode .estimate-preview-container .notes-section,
            body.dark-mode .invoice-preview-wrapper .notes-section,
            body.dark-mode .estimate-preview-wrapper .notes-section {
                background-color: ' . $template['notes_bg'] . ' !important;
                border-color: ' . $template['accent_color'] . ' !important;
            }
        ';
    }

    /**
     * Check if template exists
     *
     * @param string $templateId
     * @return bool
     */
    public static function templateExists($templateId)
    {
        return isset(self::$templates[$templateId]);
    }

    /**
     * Get template name
     *
     * @param string $templateId
     * @return string
     */
    public static function getTemplateName($templateId)
    {
        $template = self::getTemplate($templateId);
        return $template ? Yii::t('invoice', 'template_' . $templateId) : 'Unknown Template';
    }

    /**
     * Validate template ID
     *
     * @param string $templateId
     * @return string Valid template ID
     */
    public static function validateTemplateId($templateId)
    {
        return self::templateExists($templateId) ? $templateId : self::getDefaultTemplate();
    }
}