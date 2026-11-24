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
        'social_links'             => '',
        'image_lightbox'           => '1',
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

/**
 * Map of URL host fragments to icon names in images/social/.
 * Order matters: more specific hosts first.
 *
 * @return array
 */
function nessia_social_hosts()
{
    return array(
        'profiles.wordpress.org' => 'wordpress-profile',
        'academia.edu'   => 'academia',
        'orcid.org'      => 'orcid',
        'viaf.org'       => 'viaf',
        'isni.org'       => 'isni',
        'worldcat.org'   => 'oclc',
        'oclc.org'       => 'oclc',
        'wikidata.org'   => 'wikidata',
        'wikipedia.org'  => 'wikipedia',
        'goodreads.com'  => 'goodreads',
        'scribd.com'     => 'scribd',
        'issuu.com'      => 'issuu',
        'substack.com'   => 'substack',
        'medium.com'     => 'medium',
        'github.com'     => 'github',
        'x.com'          => 'x',
        'twitter.com'    => 'x',
        'facebook.com'   => 'facebook',
        'instagram.com'  => 'instagram',
        'threads.net'    => 'threads',
        'linkedin.com'   => 'linkedin',
        'youtube.com'    => 'youtube',
        'youtu.be'       => 'youtube',
        'tiktok.com'     => 'tiktok',
        'vimeo.com'      => 'vimeo',
        'soundcloud.com' => 'soundcloud',
        'spotify.com'    => 'spotify',
        'suno.com'       => 'suno',
        'suno.ai'        => 'suno',
        't.me'           => 'telegram',
        'telegram.me'    => 'telegram',
        'whatsapp.com'   => 'whatsapp',
        'wa.me'          => 'whatsapp',
        'signal.me'      => 'signal',
        'discord.gg'     => 'discord',
        'discord.com'    => 'discord',
        'reddit.com'     => 'reddit',
        'quora.com'      => 'quora',
        'pinterest.com'  => 'pinterest',
        'tumblr.com'     => 'tumblr',
        'bsky.app'       => 'bluesky',
        'flickr.com'     => 'flickr',
        'wordpress.com'  => 'wordpress',
        'behance.net'    => 'behance',
        'dribbble.com'   => 'dribbble',
        'twitch.tv'      => 'twitch',
        'line.me'        => 'line',
        'snapchat.com'   => 'snapchat',
        'wechat.com'     => 'wechat',
        'fiverr.com'     => 'fiverr',
    );
}

/**
 * Inline an icon from images/social/ so it inherits the text colour.
 *
 * @param string $name Icon file name without extension.
 * @return string SVG markup or empty string.
 */
function nessia_social_icon($name)
{
    static $cache = array();
    if (isset($cache[$name])) {
        return $cache[$name];
    }
    $path = dirname(__FILE__) . '/images/social/' . basename($name) . '.svg';
    $svg = is_readable($path) ? file_get_contents($path) : '';
    if ($svg) {
        $svg = preg_replace('/<title>.*?<\/title>/s', '', $svg);
        $svg = preg_replace('/fill="#0{3,6}"/i', 'fill="currentColor"', $svg);
        $svg = preg_replace('/<svg\b/', '<svg fill="currentColor" aria-hidden="true" focusable="false" width="18" height="18"', $svg, 1);
    }
    return $cache[$name] = $svg;
}

/**
 * Footer social / profile links from the "social_links" option
 * (one URL per line). Icons are picked from the URL's host; Mastodon
 * is detected by the /@user path pattern.
 *
 * @return string HTML list or empty string.
 */
function nessia_social_links()
{
    $raw = (string) nessia_option('social_links');
    if (trim($raw) === '') {
        return '';
    }
    $hosts = nessia_social_hosts();
    $items = '';
    foreach (preg_split('/\R/', $raw) as $line) {
        $url = trim($line);
        if ($url === '' || !preg_match('#^https?://#i', $url)) {
            continue;
        }
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^(www|m)\./', '', $host);
        $icon = null;
        foreach ($hosts as $needle => $name) {
            if ($host === $needle || substr($host, -strlen('.' . $needle)) === '.' . $needle) {
                $icon = $name;
                break;
            }
        }
        if (!$icon && preg_match('#^/@[^/]+/?$#', (string) parse_url($url, PHP_URL_PATH))) {
            $icon = 'mastodon';
        }
        $svg = $icon ? nessia_social_icon($icon) : '';
        $label = $icon ? ucwords(str_replace('-', ' ', $icon)) : $host;
        if (!$svg) {
            $svg = '<span class="nessia-social-fallback" aria-hidden="true">' . html_escape(strtoupper(substr($host, 0, 1))) . '</span>';
        }
        $items .= '<li><a href="' . html_escape($url) . '" rel="me noopener" target="_blank" title="' . html_escape($label) . '">'
            . $svg . '<span class="sr-only">' . html_escape($label) . '</span></a></li>';
    }
    return $items ? '<ul class="nessia-social" aria-label="' . html_escape(__('Profiles')) . '">' . $items . '</ul>' : '';
}

/**
 * Category structure: from the NessiaSetup plugin if installed,
 * otherwise the theme's bundled data/categories.php.
 *
 * @return array
 */
function nessia_categories()
{
    static $categories = null;
    if ($categories !== null) {
        return $categories;
    }
    $paths = array(
        PLUGIN_DIR . '/NessiaSetup/categories.php',
        dirname(__FILE__) . '/data/categories.php',
    );
    foreach ($paths as $path) {
        if (is_readable($path)) {
            $loaded = include $path;
            if (is_array($loaded)) {
                return $categories = $loaded;
            }
        }
    }
    return $categories = array('collections' => array(), 'subjects' => array(), 'tags' => array());
}

/**
 * URL for a category: its collection page if a public collection with
 * that exact title exists, otherwise a keyword search for the name.
 *
 * @param string $title
 * @return string
 */
function nessia_category_url($title)
{
    static $byTitle = null;
    if ($byTitle === null) {
        $byTitle = array();
        $db = get_db();
        $titleElement = $db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Title');
        if ($titleElement) {
            $rows = $db->fetchAll(
                "SELECT et.record_id, et.text FROM {$db->ElementText} et
                 JOIN {$db->Collection} c ON c.id = et.record_id
                 WHERE et.record_type = 'Collection' AND et.element_id = ? AND c.public = 1",
                array($titleElement->id)
            );
            foreach ($rows as $row) {
                $byTitle[strtolower(trim($row['text']))] = (int) $row['record_id'];
            }
        }
    }
    $key = strtolower(trim($title));
    if (isset($byTitle[$key])) {
        return url('collections/show/' . $byTitle[$key]);
    }
    return url('items/browse', array('search' => $title));
}

/**
 * "Explore by topic" block: collection tiles with sub-topic chips.
 *
 * @return string HTML
 */
function nessia_explore_topics()
{
    $categories = nessia_categories();
    if (empty($categories['collections'])) {
        return '';
    }
    $html = '<section id="explore-topics" aria-labelledby="explore-topics-title">'
        . '<h2 id="explore-topics-title">' . __('Explore by topic') . '</h2>'
        . '<ul class="nessia-topic-grid">';
    foreach ($categories['collections'] as $cat) {
        $html .= '<li><a class="nessia-topic-title" href="' . html_escape(nessia_category_url($cat['title'])) . '">'
            . html_escape(__($cat['title'])) . '</a>';
        if (!empty($cat['description'])) {
            $html .= '<p>' . html_escape(__($cat['description'])) . '</p>';
        }
        if (!empty($cat['children'])) {
            $html .= '<ul class="nessia-topic-children">';
            foreach ($cat['children'] as $child) {
                $html .= '<li><a href="' . html_escape(nessia_category_url($child['title'])) . '">'
                    . html_escape(__($child['title'])) . '</a></li>';
            }
            $html .= '</ul>';
        }
        $html .= '</li>';
    }
    $html .= '</ul></section>';
    return $html;
}
