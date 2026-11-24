<?php
/**
 * NESSIA Theme — Custom Helper Functions
 *
 * Near Eastern and Semitic Information Archive
 * https://nessia.org
 *
 * @package NESSIA
 * @version 1.3.0
 */

/**
 * Default values for every theme option, so the theme works out of the box
 * before the settings form has ever been saved.
 *
 * @return array
 */
function nessia_option_defaults()
{
    return array(
        'colour_scheme'            => 'gold',
        'site_tagline'             => 'Exploring Semitic Religion, Language and Heritage',
        'show_multilingual_names'  => '1',
        'homepage_recent_items'    => '5',
        'homepage_text'            => '',
        'footer_text'              => '',
        'display_footer_copyright' => '1',
        'use_advanced_search'      => '1',
    );
}

/**
 * Read a theme option, falling back to its default when unsaved (null).
 *
 * @param string $name Option name.
 * @return mixed
 */
function nessia_option($name)
{
    $value = get_theme_option($name);
    if ($value === null) {
        $defaults = nessia_option_defaults();
        return isset($defaults[$name]) ? $defaults[$name] : null;
    }
    return $value;
}

/**
 * Display the site tagline.
 *
 * @return string|null HTML for the tagline paragraph.
 */
function nessia_display_tagline()
{
    $tagline = nessia_option('site_tagline');
    if ($tagline === '' || $tagline === null) {
        $defaults = nessia_option_defaults();
        $tagline = $defaults['site_tagline'];
    }
    return '<p class="nessia-tagline">' . html_escape(__($tagline)) . '</p>';
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
    if (!nessia_option('show_multilingual_names')) {
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
    $scheme = nessia_option('colour_scheme');
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
 * @param string|null $format Zend_Date format (null = locale default).
 * @param object|null $item The item record.
 * @return string Formatted date.
 */
function nessia_display_date_added($format = null, $item = null)
{
    if (!$item) {
        $item = get_current_record('item');
    }
    $dateAdded = metadata($item, 'added');
    if (!$dateAdded) {
        return '';
    }
    return $format ? format_date($dateAdded, $format) : format_date($dateAdded);
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


/**
 * Classify an item's files so the viewer can be styled to suit them.
 *
 * @param array $files File records.
 * @return string audio|video|image|document|mixed
 */
function nessia_item_media_type($files)
{
    $types = array();
    foreach ((array) $files as $file) {
        $mime = (string) $file->mime_type;
        if (strpos($mime, 'audio/') === 0) {
            $types['audio'] = true;
        } elseif (strpos($mime, 'video/') === 0) {
            $types['video'] = true;
        } elseif (strpos($mime, 'image/') === 0) {
            $types['image'] = true;
        } else {
            $types['document'] = true;
        }
    }
    if (count($types) === 1) {
        return key($types);
    }
    return $types ? 'mixed' : 'document';
}
