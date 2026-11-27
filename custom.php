<?php
/**
 * NESSIA Theme — Custom Helper Functions
 *
 * Near Eastern and Semitic Information Archive
 * https://nessia.org
 *
 * @package NESSIA
 * @version 1.6.0
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
        'homepage_recent_items'    => '8',
        'homepage_text'            => '',
        'footer_text'              => '',
        'display_footer_copyright' => '1',
        'use_advanced_search'      => '1',
        'social_links'             => '',
        'image_lightbox'           => '1',
        'browse_view'              => 'grid',
        'mission_text'             => 'Preserving the sources of <strong>Semitic religion, language and heritage</strong> for scholars, students and every curious reader.',
        'mission_button_label'     => 'Browse the archive',
        'mission_button_url'       => '',
        'donate_url'               => '',
        'donate_label'             => 'Donate',
        'auto_categories'          => '1',
        'auto_identifiers'         => '1',
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
    $allowed = array('gold', 'indigo', 'sand', 'kapitan');
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
 * Category structure from the theme's data/categories.php.
 *
 * @return array
 */
function nessia_categories()
{
    static $categories = null;
    if ($categories !== null) {
        return $categories;
    }
    $paths = array(dirname(__FILE__) . '/data/categories.php');
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
function nessia_category_collection_id($title)
{
    nessia_category_url($title); // fills the lookup
    $map = nessia_category_url(null, true);
    $key = strtolower(trim((string) $title));
    return isset($map[$key]) ? $map[$key] : null;
}

function nessia_category_url($title, $returnMap = false)
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
    if ($returnMap) {
        return $byTitle;
    }
    $key = strtolower(trim($title));
    if (isset($byTitle[$key])) {
        $c = get_record_by_id('Collection', $byTitle[$key]);
        return $c ? record_url($c, 'show') : url('collections/show/' . $byTitle[$key]);
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
        $url = nessia_category_url($cat['title']);
        $cid = nessia_category_collection_id($cat['title']);
        $cover = $cid ? nessia_collection_cover($cid) : null;
        $html .= '<li class="' . ($cover ? 'has-image' : 'no-image') . '">'
            . '<a class="nessia-topic-img" href="' . html_escape($url) . '" tabindex="-1" aria-hidden="true">' . nessia_picture($cover, __($cat['title'])) . '</a>'
            . '<div class="nessia-topic-body"><a class="nessia-topic-title" href="' . html_escape($url) . '">'
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
        $html .= '</div></li>';
    }
    $html .= '</ul></section>';
    return $html;
}

/* ==========================================================================
   Page banner
   ========================================================================== */

/**
 * Banner shown above the content on every page except the homepage:
 * page title plus a breadcrumb trail.
 *
 * @param string|null $title     Page title passed to head().
 * @param string|null $bodyid
 * @param string|null $bodyclass
 * @return string HTML or empty string.
 */
function nessia_page_banner($title, $bodyid = null, $bodyclass = null)
{
    if (!$title || $bodyid === 'home') {
        return '';
    }
    $classes = ' ' . (string) $bodyclass . ' ';
    $crumbs = array(array(__('Home'), url('/')));

    if (strpos($classes, ' items ') !== false && strpos($classes, ' show ') !== false) {
        $crumbs[] = array(__('Items'), url('items/browse'));
    } elseif (strpos($classes, ' collections ') !== false && strpos($classes, ' show ') !== false) {
        $crumbs[] = array(__('Collections'), url('collections/browse'));
    } elseif (strpos($classes, ' exhibits ') !== false && strpos($classes, ' browse ') === false) {
        $crumbs[] = array(__('Exhibits'), url('exhibits'));
    }

    $html = '<div class="nessia-page-banner"><div class="nessia-page-banner-inner">'
        . '<nav class="nessia-crumbs" aria-label="' . html_escape(__('Breadcrumb')) . '"><ol>';
    foreach ($crumbs as $crumb) {
        $html .= '<li><a href="' . html_escape($crumb[1]) . '">' . html_escape($crumb[0]) . '</a></li>';
    }
    $html .= '<li aria-current="page">' . html_escape(strip_formatting($title)) . '</li></ol></nav>'
        . '<p class="nessia-page-title" aria-hidden="true">' . html_escape(strip_formatting($title)) . '</p>'
        . '</div></div>';
    return $html;
}

/* ==========================================================================
   Browse views & filters
   ========================================================================== */

/**
 * Current browse view: grid (default), list or table.
 *
 * @return string
 */
function nessia_browse_view()
{
    $view = isset($_GET['view']) ? (string) $_GET['view'] : (string) nessia_option('browse_view');
    return in_array($view, array('grid', 'list', 'table'), true) ? $view : 'grid';
}

/**
 * Current URL with some query parameters replaced (null removes one).
 *
 * @param array $changes
 * @return string
 */
function nessia_url_with(array $changes)
{
    $params = $_GET;
    foreach ($changes as $key => $value) {
        if ($value === null) {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }
    unset($params['page']);
    $path = strtok($_SERVER['REQUEST_URI'], '?');
    return $path . ($params ? '?' . http_build_query($params) : '');
}

/**
 * Grid / list / table switcher.
 *
 * @return string HTML
 */
function nessia_view_switcher()
{
    $current = nessia_browse_view();
    $views = array(
        'grid'  => array(__('Grid'), '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>'),
        'list'  => array(__('List'), '<rect x="3" y="4" width="6" height="6"/><line x1="12" y1="6" x2="21" y2="6"/><line x1="12" y1="9" x2="18" y2="9"/><rect x="3" y="14" width="6" height="6"/><line x1="12" y1="16" x2="21" y2="16"/><line x1="12" y1="19" x2="18" y2="19"/>'),
        'table' => array(__('Table'), '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>'),
    );
    $html = '<div class="nessia-view-switch" role="group" aria-label="' . html_escape(__('View')) . '">';
    foreach ($views as $key => $v) {
        $html .= '<a href="' . html_escape(nessia_url_with(array('view' => $key))) . '"'
            . ($key === $current ? ' class="is-active" aria-current="true"' : '')
            . ' title="' . html_escape($v[0]) . '">'
            . '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">' . $v[1] . '</svg>'
            . '<span class="sr-only">' . html_escape($v[0]) . '</span></a>';
    }
    return $html . '</div>';
}

/**
 * Filter pills: All + each item type that has public items.
 *
 * @return string HTML
 */
function nessia_type_pills()
{
    $db = get_db();
    $rows = $db->fetchAll(
        "SELECT it.id, it.name, COUNT(i.id) AS n
         FROM {$db->ItemType} it
         JOIN {$db->Item} i ON i.item_type_id = it.id AND i.public = 1
         GROUP BY it.id, it.name
         HAVING n > 0
         ORDER BY it.name"
    );
    if (count($rows) < 2) {
        return '';
    }
    $active = isset($_GET['type']) ? (int) $_GET['type'] : 0;
    $html = '<nav class="nessia-pills" aria-label="' . html_escape(__('Filter by type')) . '"><ul>';
    $html .= '<li><a href="' . html_escape(nessia_url_with(array('type' => null))) . '"'
        . (!$active ? ' class="is-active" aria-current="true"' : '') . '>' . __('All') . '</a></li>';
    foreach ($rows as $row) {
        $isActive = $active === (int) $row['id'];
        $html .= '<li><a href="' . html_escape(nessia_url_with(array('type' => $row['id']))) . '"'
            . ($isActive ? ' class="is-active" aria-current="true"' : '') . '>'
            . html_escape(__($row['name'])) . ' <span class="nessia-pill-count">' . (int) $row['n'] . '</span></a></li>';
    }
    return $html . '</ul></nav>';
}

/* ==========================================================================
   Mission band & footer
   ========================================================================== */

/**
 * Statement band above the footer. Text supports <strong> for emphasis.
 *
 * @return string HTML
 */
function nessia_mission_band()
{
    $text = trim((string) nessia_option('mission_text'));
    if ($text === '' || $text === '0') {
        return '';
    }
    $text = strip_tags($text, '<strong><em><b><i><br>');
    $label = trim((string) nessia_option('mission_button_label'));
    $url = trim((string) nessia_option('mission_button_url'));
    if ($url === '') {
        $url = url('items/browse');
    }
    $html = '<section class="nessia-mission"><div class="nessia-mission-inner">'
        . '<p class="nessia-mission-text">' . $text . '</p>';
    $buttons = '';
    if ($label !== '') {
        $buttons .= '<a class="nessia-mission-btn" href="' . html_escape($url) . '">' . html_escape($label) . '</a>';
    }
    $buttons .= nessia_donate_button('band');
    if ($buttons !== '') {
        $html .= '<div class="nessia-mission-actions">' . $buttons . '</div>';
    }
    return $html . '</div></section>';
}

/**
 * Footer "Explore" links from the top-level categories.
 *
 * @return string HTML list items
 */
function nessia_footer_explore()
{
    $categories = nessia_categories();
    $html = '';
    foreach ($categories['collections'] as $cat) {
        $html .= '<li><a href="' . html_escape(nessia_category_url($cat['title'])) . '">' . html_escape(__($cat['title'])) . '</a></li>';
    }
    return $html;
}

/**
 * Donate button (header and mission band). Hidden until a link is set
 * in Theme Settings, Footer & Support, "Donate Link".
 *
 * @param string $context 'header' or 'band'
 * @return string HTML
 */
function nessia_donate_button($context = 'header')
{
    $url = trim((string) nessia_option('donate_url'));
    if ($url === '') {
        return '';
    }
    if (!preg_match('#^(https?:)?//#i', $url) && strpos($url, '/') !== 0) {
        $url = url($url);
    }
    $label = trim((string) nessia_option('donate_label'));
    if ($label === '') {
        $label = __('Donate');
    }
    $svg = file_get_contents(dirname(__FILE__) . '/images/donate.svg');
    $svg = preg_replace('/<svg\b/', '<svg aria-hidden="true" focusable="false" width="20" height="20"', $svg, 1);
    $external = preg_match('#^(https?:)?//#i', $url) && strpos($url, (string) parse_url(WEB_ROOT, PHP_URL_HOST)) === false;
    return '<a class="nessia-donate nessia-donate--' . html_escape($context) . '" href="' . html_escape($url) . '"'
        . ($external ? ' target="_blank" rel="noopener"' : '') . '>'
        . $svg . '<span class="nessia-donate-label">' . html_escape($label) . '</span></a>';
}

/* ==========================================================================
   SEO: canonical URL, description, Open Graph
   ========================================================================== */

/**
 * Meta tags for <head>: canonical link, description and Open Graph.
 * On item and collection pages the canonical is the record's own URL,
 * which becomes the clean address once the Clean Url plugin is active.
 *
 * @param string|null $title Page title passed to head().
 * @return string HTML
 */
function nessia_seo_meta($title = null)
{
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $controller = $request ? $request->getControllerName() : '';
    $action = $request ? $request->getActionName() : '';
    $siteTitle = strip_formatting(option('site_title'));
    $description = trim(strip_formatting((string) option('description')));
    $canonical = null;
    $image = null;
    $type = 'website';

    $record = null;
    if ($action === 'show' && $controller === 'items') {
        $record = get_current_record('item', false);
    } elseif ($action === 'show' && $controller === 'collections') {
        $record = get_current_record('collection', false);
    }

    if ($record) {
        $canonical = record_url($record, 'show', true);
        $desc = metadata($record, array('Dublin Core', 'Description'), array('no_escape' => true));
        if ($desc) {
            $description = strip_formatting($desc);
        }
        if ($record instanceof Item) {
            $type = 'article';
            $file = $record->getFile(0);
            if ($file && $file->hasThumbnail()) {
                $image = $file->getWebPath('fullsize');
            }
        }
    } else {
        // Other pages: the current address without tracking or sort parameters.
        $params = $_GET;
        foreach (array_keys($params) as $key) {
            if (!in_array($key, array('page', 'type', 'collection', 'tags', 'search'), true)) {
                unset($params[$key]);
            }
        }
        // WEB_ROOT is the configured site address, so a spoofed Host header cannot leak in.
        $canonical = rtrim(WEB_ROOT, '/') . ($request ? $request->getPathInfo() : '/');
        if ($params) {
            $canonical .= '?' . http_build_query($params);
        }
    }

    if (function_exists('mb_substr') && mb_strlen($description) > 160) {
        $description = rtrim(mb_substr($description, 0, 157)) . '…';
    }
    $pageTitle = $title ? strip_formatting($title) . ' | ' . $siteTitle : $siteTitle;

    $html = '';
    if (isset($_GET['nessia-tools'])) {
        return '<meta name="robots" content="noindex, nofollow">' . "\n";
    }
    if ($canonical) {
        $html .= '<link rel="canonical" href="' . html_escape($canonical) . '">' . "\n";
        $html .= '    <meta property="og:url" content="' . html_escape($canonical) . '">' . "\n";
    }
    if ($description !== '') {
        $html .= '    <meta name="description" content="' . html_escape($description) . '">' . "\n";
        $html .= '    <meta property="og:description" content="' . html_escape($description) . '">' . "\n";
    }
    $html .= '    <meta property="og:site_name" content="' . html_escape($siteTitle) . '">' . "\n";
    $html .= '    <meta property="og:title" content="' . html_escape($pageTitle) . '">' . "\n";
    $html .= '    <meta property="og:type" content="' . $type . '">' . "\n";
    if ($image) {
        $html .= '    <meta property="og:image" content="' . html_escape($image) . '">' . "\n";
        $html .= '    <meta name="twitter:card" content="summary_large_image">' . "\n";
    } else {
        $html .= '    <meta name="twitter:card" content="summary">' . "\n";
    }
    return $html;
}


/* ==========================================================================
   Landing pages, overview and shortcodes (lib/landing.php)
   ========================================================================== */

require_once dirname(__FILE__) . '/lib/landing.php';
require_once dirname(__FILE__) . '/lib/schema.php';
nessia_register_shortcodes();

/* ==========================================================================
   Built-in setup (lib/NessiaSetup.php)
   ========================================================================== */

/**
 * @return NessiaSetup
 */
function nessia_setup()
{
    static $setup = null;
    if ($setup === null) {
        require_once dirname(__FILE__) . '/lib/NessiaSetup.php';
        $setup = new NessiaSetup;
    }
    return $setup;
}

/**
 * Background work on public page loads. Each step is guarded so it costs
 * one option read when there is nothing to do, and errors never reach the
 * visitor.
 *
 * 1. Categories: created once per categories.php version.
 * 2. Identifiers: records without a Dublin Core Identifier get one from
 *    their title, 25 at a time, checked at most every 5 minutes.
 */
function nessia_background_setup()
{
    static $done = false;
    if ($done || is_admin_theme()) {
        return;
    }
    $done = true;

    try {
        $setup = nessia_setup();

        if (nessia_option('auto_categories')) {
            // The suffix re-runs setup when the theme adds new setup steps (pages-1: landing pages).
            $hash = md5(md5_file(dirname(__FILE__) . '/data/categories.php') . '|pages-1');
            if (get_option('nessia_categories_hash') !== $hash) {
                // Concurrent first visits: only the request holding the lock
                // seeds, and it re-checks the hash once it has the lock.
                $setup->withLock(function () use ($setup, $hash) {
                    if (get_option('nessia_categories_hash') !== $hash) {
                        $setup->seed();
                        set_option('nessia_categories_hash', $hash);
                    }
                });
            }
        }

        if (nessia_option('auto_identifiers')) {
            $last = (int) get_option('nessia_identifiers_checked');
            if (time() - $last > 300) {
                // The lock is the atomic claim; the timestamp is re-read
                // inside it so a request that waited behind another skips.
                $setup->withLock(function () use ($setup) {
                    if (time() - (int) get_option('nessia_identifiers_checked') > 300) {
                        set_option('nessia_identifiers_checked', (string) time());
                        $setup->slugs(false, 25);
                    }
                });
            }
        }
    } catch (Exception $e) {
        _log('NESSIA theme setup: ' . $e->getMessage(), Zend_Log::WARN);
    }
}

/* ==========================================================================
   Private tools page: https://your-site/?nessia-tools (super users only)
   ========================================================================== */

/**
 * @return bool True when the tools page was asked for by a super user.
 */
function nessia_tools_requested()
{
    if (!isset($_GET['nessia-tools'])) {
        return false;
    }
    $user = current_user();
    return $user && $user->role === 'super';
}

/**
 * Per-session token that every tools form must send back.
 *
 * @return string
 */
function nessia_tools_token()
{
    $session = new Zend_Session_Namespace('nessia_tools');
    if (empty($session->token)) {
        $session->token = bin2hex(random_bytes(16));
    }
    return $session->token;
}

/**
 * Run a posted tools action.
 *
 * @return array|null array('action' => ..., 'lines' => array()) or null
 */
function nessia_tools_handle_post()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['nessia_action'])) {
        return null;
    }
    if (!isset($_POST['nessia_token']) || !hash_equals(nessia_tools_token(), (string) $_POST['nessia_token'])) {
        return array('action' => 'error', 'lines' => array(__('The form expired. Reload the page and try again.')));
    }
    $setup = nessia_setup();
    $busy = array('action' => 'error', 'lines' => array(__('Setup is already running in another request. Try again in a moment.')));
    switch ($_POST['nessia_action']) {
        case 'seed':
            if ($setup->withLock(function () use ($setup) { $setup->seed(); return true; }, 10) === false) {
                return $busy;
            }
            $report = json_decode((string) get_option('nessia_setup_report'), true);
            $c = isset($report['counts']) ? $report['counts'] : array();
            return array('action' => 'seed', 'lines' => array(
                __('Collections created: %s', isset($c['collections']) ? (int) $c['collections'] : 0),
                __('Collections nested: %s', isset($c['nested']) ? (int) $c['nested'] : 0),
                __('Subject terms added: %s', isset($c['subjects']) ? (int) $c['subjects'] : 0),
                __('Tags created: %s', isset($c['tags']) ? (int) $c['tags'] : 0),
                __('Landing pages created: %s', isset($c['pages']) ? (int) $c['pages'] : 0),
            ));
        case 'identifiers':
            $result = $setup->withLock(function () use ($setup) { return $setup->slugs(false); }, 10);
            if ($result === false) {
                return $busy;
            }
            return array('action' => 'identifiers', 'lines' => array(__('%s Identifier(s) written.', count($result['rows']))));
        case 'migrate':
            // New categories must exist before items move into them.
            $lines = $setup->withLock(function () use ($setup) { $setup->seed(); return $setup->migrate(false); }, 10);
            return $lines === false ? $busy : array('action' => 'migrate', 'lines' => $lines);
    }
    return null;
}


/**
 * Address of the archive overview: the Simple Page "overview" when the
 * theme created it, otherwise the homepage with ?overview.
 */
function nessia_overview_url()
{
    if (plugin_is_active('SimplePages')) {
        $page = get_db()->getTable('SimplePagesPage')->findBySql('slug = ? AND is_published = 1', array('overview'), true);
        if ($page) {
            return url($page->slug);
        }
    }
    return url('/') . '?overview';
}
