<?php
/**
 * Language Configuration
 * Handles loading Arabic language file and providing translation functions
 */

// Set language to Arabic only
$_SESSION['lang'] = 'ar';

// Load the Arabic language file
$lang_file = __DIR__ . '/ar.php';

// Include the language file
require_once($lang_file);

/**
 * Translate a string from the language file
 * @param string $key The translation key
 * @return string The translated text or the key itself if translation not found
 */
function __($key) {
    global $lang;
    return isset($lang[$key]) ? $lang[$key] : $key;
}

// Helper function for the direction (RTL for Arabic, LTR for English)
function get_direction() {
    return $_SESSION['lang'] == 'ar' ? 'rtl' : 'ltr';
}

// Helper function for text alignment (right for Arabic, left for English)
function get_align() {
    return $_SESSION['lang'] == 'ar' ? 'right' : 'left';
}

// Helper function for opposite alignment (left for Arabic, right for English)
function get_opposite_align() {
    return $_SESSION['lang'] == 'ar' ? 'left' : 'right';
}
?>
