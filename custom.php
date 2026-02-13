<?php
/**
 * NESSIA Theme — Custom Helper Functions
 *
 * Near Eastern and Semitic Information Archive
 * https://nessia.org
 *
 * @package NESSIA
 * @version 1.2.4
 */

/**
 * Display the site tagline.
 *
 * @return string|null HTML for the tagline paragraph.
 */
function nessia_display_tagline()
{
    $tagline = get_theme_option('site_tagline');
    if ($tagline) {
        return '<p class="nessia-tagline">' . html_escape($tagline) . '</p>';
    }
    return '<p class="nessia-tagline">Exploring Semitic Religion, Language and Heritage</p>';
}

/**
 * Display the multilingual site names (Arabic, Hebrew, Aramaic).
 *
 * @return string HTML block with RTL script names.
 */
function nessia_multilingual_names()
{
    // Omeka returns null for checkbox options that haven't been explicitly
    // saved yet via the admin panel. We treat null (unsaved) the same as "1"
    // so the names display by default out of the box.
    $opt = get_theme_option('show_multilingual_names');
    if ($opt === '0') {
        return '';
    }

    $names = array(
        'ar' => 'أرشيف معلومات الشرق الأدنى والسامية',
        'he' => 'ארכיון מידע המזרח הקרוב והשמי',
        'arc' => 'ܐܪܟܝܒ ܝܕ̈ܥܬܐ ܕܡܕܢܚܐ ܩܪܝܒܐ ܘܫܡܝ̈ܐ',
    );

    $html = '<div class="nessia-multilingual-names">';
    $i = 0;
    foreach ($names as $lang => $name) {
        if ($i > 0) {
            $html .= '<span class="nessia-sep" aria-hidden="true">•</span>';
        }
        $langEsc = html_escape($lang);
        $nameEsc = html_escape($name);
        $html .= '<span class="nessia-lang nessia-lang-' . $langEsc . '" lang="' . $langEsc . '" dir="rtl">' . $nameEsc . '</span>';
        $i++;
    }
    $html .= '</div>';

    return $html;
}

/**
 * Get the current colour scheme class.
 *
 * @return string CSS class for the colour scheme.
 */
function nessia_colour_scheme()
{
    $scheme = get_theme_option('colour_scheme');
    $allowed = array('gold', 'indigo', 'sand');
    if (!$scheme || !in_array($scheme, $allowed, true)) {
        $scheme = 'gold';
    }
    return 'nessia-scheme-' . $scheme;
}

/**
 * Display the NESSIA logo (custom upload or default SVG).
 *
 * @return string HTML for the logo.
 */
function nessia_logo()
{
    $logo = theme_logo();
    if ($logo) {
        return $logo;
    }
    $logoPath = img('nessia/logo.svg');
    return '<img src="' . html_escape($logoPath) . '" alt="NESSIA" class="nessia-default-logo" />';
}

/**
 * Format the date added for an item.
 *
 * @param string $format Date format string.
 * @param object|null $item The item record.
 * @return string Formatted date.
 */
function nessia_display_date_added($format = 'j F Y', $item = null)
{
    if (!$item) {
        $item = get_current_record('item');
    }
    $dateAdded = metadata($item, 'added');
    if (!$dateAdded) {
        return '';
    }
    $ts = strtotime($dateAdded);
    if ($ts === false) {
        return '';
    }
    return date($format, $ts);
}

/**
 * Get the header background image URL if set.
 *
 * @return string|null URL of the header background image.
 */
function nessia_header_bg()
{
    $bg = get_theme_option('header_bg');
    if ($bg) {
        $storage = Zend_Registry::get('storage');
        return $storage->getUri($storage->getPathByType($bg, 'theme_uploads'));
    }
    return null;
}
