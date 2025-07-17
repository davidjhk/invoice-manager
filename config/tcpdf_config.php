<?php
/**
 * Custom TCPDF configuration for Invoice Manager
 * This file ensures proper temporary directory configuration
 * 
 * This file must be loaded BEFORE any TCPDF classes are instantiated
 * to ensure the correct temporary directory is used.
 */

// Use system temporary directory instead of hardcoded paths
$temp_dir = sys_get_temp_dir();
if (substr($temp_dir, -1) != '/') {
    $temp_dir .= '/';
}

// Ensure the temporary directory exists and is writable
if (!is_dir($temp_dir)) {
    mkdir($temp_dir, 0755, true);
}

// Force TCPDF to use external configuration
define('K_TCPDF_EXTERNAL_CONFIG', true);

// Define the cache path for TCPDF - this must be set before TCPDF autoconfig runs
define('K_PATH_CACHE', $temp_dir);

// Define other essential configurations
define('PDF_PAGE_FORMAT', 'LETTER');
define('PDF_PAGE_ORIENTATION', 'P');
define('PDF_CREATOR', 'Invoice Manager');
define('PDF_UNIT', 'mm');
define('PDF_MARGIN_HEADER', 5);
define('PDF_MARGIN_FOOTER', 15);
define('PDF_MARGIN_TOP', 27);
define('PDF_MARGIN_BOTTOM', 25);
define('PDF_MARGIN_LEFT', 15);
define('PDF_MARGIN_RIGHT', 15);
define('PDF_FONT_NAME_MAIN', 'helvetica');
define('PDF_FONT_SIZE_MAIN', 10);
define('PDF_FONT_NAME_DATA', 'helvetica');
define('PDF_FONT_SIZE_DATA', 8);
define('PDF_FONT_MONOSPACED', 'courier');
define('PDF_IMAGE_SCALE_RATIO', 1.25);
define('HEAD_MAGNIFICATION', 1.1);
define('K_CELL_HEIGHT_RATIO', 1.25);
define('K_TITLE_MAGNIFICATION', 1.3);
define('K_SMALL_RATIO', 2/3);
define('K_THAI_TOPCHARS', true);
define('K_TCPDF_CALLS_IN_HTML', false);
define('K_ALLOWED_TCPDF_TAGS', '');
define('K_TCPDF_THROW_EXCEPTION_ERROR', false);
define('K_TIMEZONE', 'UTC');
define('K_BLANK_IMAGE', '_blank.png');