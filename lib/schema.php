<?php
/**
 * NESSIA Theme — schema.org structured data (JSON-LD).
 *
 * One @graph per page, printed in <head> by nessia_schema_script():
 *
 *   #archive   ArchiveOrganization (the site's owner and holding archive)
 *   #website   WebSite with a SearchAction
 *   #webpage   WebPage, CollectionPage, ItemPage, AboutPage, ContactPage,
 *              SearchResultsPage, depending on the page
 *   #breadcrumb BreadcrumbList (the same trail as the page banner)
 *   #record    the item (CreativeWork subtype + ArchiveComponent) or the
 *              collection (Collection + ArchiveComponent)
 *   #list      ItemList of the records shown on browse and collection pages
 *
 * Dublin Core → schema.org: Title name, Description description,
 * Creator author/creator, Contributor contributor, Publisher publisher,
 * Date dateCreated, Subject about/keywords, Language inLanguage,
 * Rights license/copyrightNotice, Identifier identifier, Relation
 * isRelatedTo, Coverage spatialCoverage/temporalCoverage, Source
 * isBasedOn, Format encodingFormat.
 *
 * @package NESSIA
 */

/** Absolute URL for a site path. */
function nessia_schema_abs($path)
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $base = rtrim(WEB_ROOT, '/');
    $root = (string) parse_url($base, PHP_URL_PATH);
    if ($root !== '' && strpos($path, $root) === 0) {
        $path = substr($path, strlen($root));
    }
    return $base . '/' . ltrim($path, '/');
}

/** Plain-text values of a Dublin Core element on a record. */
function nessia_schema_dc($record, $element)
{
    $out = array();
    foreach ((array) metadata($record, array('Dublin Core', $element), array('all' => true, 'no_filter' => true)) as $v) {
        $v = trim(html_entity_decode(strip_tags((string) $v), ENT_QUOTES, 'UTF-8'));
        if ($v !== '') {
            $out[] = $v;
        }
    }
    return $out;
}

/** One value, several values as a list, or null. */
function nessia_schema_one_or_many(array $values)
{
    if (!$values) {
        return null;
    }
    return count($values) === 1 ? $values[0] : $values;
}

/** Persons or organisations named in a Dublin Core element. */
function nessia_schema_agents(array $names)
{
    $out = array();
    foreach ($names as $name) {
        $out[] = array('@type' => 'Person', 'name' => $name);
    }
    return nessia_schema_one_or_many($out);
}

/** ISO 8601 date if the text starts with one (2024, 2024-05, 2024-05-08). */
function nessia_schema_date($text)
{
    if (preg_match('/^\s*(\d{4}(?:-\d{2}(?:-\d{2})?)?)/', (string) $text, $m)) {
        return $m[1];
    }
    $ts = strtotime((string) $text);
    return $ts ? date('Y-m-d', $ts) : null;
}

/**
 * schema.org types for an Omeka item type.
 *
 * @return string[]
 */
function nessia_schema_item_types($itemTypeName)
{
    $map = array(
        'text'               => 'ScholarlyArticle',
        'document'           => 'DigitalDocument',
        'manuscript'         => 'Manuscript',
        'book'               => 'Book',
        'article'            => 'Article',
        'still image'        => 'Photograph',
        'image'              => 'Photograph',
        'photograph'         => 'Photograph',
        'map'                => 'Map',
        'sound'              => 'AudioObject',
        'oral history'       => 'AudioObject',
        'moving image'       => 'VideoObject',
        'video'              => 'VideoObject',
        'website'            => 'WebSite',
        'dataset'            => 'Dataset',
        'software'           => 'SoftwareApplication',
        'lesson plan'        => 'LearningResource',
        'interactive resource' => 'CreativeWork',
        'physical object'    => 'CreativeWork',
        'event'              => 'CreativeWork',
        'person'             => 'CreativeWork',
        'email'              => 'Message',
        'hyperlink'          => 'WebPage',
    );
    $key = strtolower(trim((string) $itemTypeName));
    $main = isset($map[$key]) ? $map[$key] : 'CreativeWork';
    return array($main, 'ArchiveComponent');
}

/** The archive (organisation) node. */
function nessia_schema_archive_node()
{
    $node = array(
        '@type' => 'ArchiveOrganization',
        '@id'   => nessia_schema_abs('/') . '#archive',
        'name'  => strip_formatting(option('site_title')),
        'url'   => nessia_schema_abs('/'),
        'logo'  => array('@type' => 'ImageObject', 'url' => nessia_schema_abs(img('nessia/logo.svg'))),
    );
    if ($d = trim(strip_formatting((string) option('description')))) {
        $node['description'] = $d;
    }
    $alt = array(
        'أرشيف معلومات الشرق الأدنى والسامية',
        'ארכיון מידע המזרח הקרוב והשמי',
        'ܐܪܟܝܒ ܝܕ̈ܥܬܐ ܕܡܕܢܚܐ ܩܪܝܒܐ ܘܫܡܝ̈ܐ',
    );
    $node['alternateName'] = array_merge(array('NESSIA'), $alt);
    $sameAs = array();
    foreach (preg_split('/\R/', (string) nessia_option('social_links')) as $line) {
        if (preg_match('#^https?://#i', trim($line))) {
            $sameAs[] = trim($line);
        }
    }
    if ($sameAs) {
        $node['sameAs'] = $sameAs;
    }
    $donate = trim((string) nessia_option('donate_url'));
    if ($donate !== '') {
        $node['potentialAction'] = array('@type' => 'DonateAction', 'target' => nessia_schema_abs($donate), 'recipient' => array('@id' => $node['@id']));
    }
    return $node;
}

/** The website node with sitelinks search. */
function nessia_schema_website_node()
{
    return array(
        '@type'     => 'WebSite',
        '@id'       => nessia_schema_abs('/') . '#website',
        'url'       => nessia_schema_abs('/'),
        'name'      => strip_formatting(option('site_title')),
        'publisher' => array('@id' => nessia_schema_abs('/') . '#archive'),
        'inLanguage' => get_html_lang(),
        'potentialAction' => array(
            '@type'       => 'SearchAction',
            'target'      => array('@type' => 'EntryPoint', 'urlTemplate' => nessia_schema_abs(url('search')) . '?query={search_term_string}'),
            'query-input' => 'required name=search_term_string',
        ),
    );
}

/** Media objects for an item's files; first image becomes the item image. */
function nessia_schema_files($item, array &$node)
{
    $media = array();
    foreach ($item->getFiles() as $file) {
        $mime = (string) $file->mime_type;
        $type = 'MediaObject';
        if (strpos($mime, 'image/') === 0) {
            $type = 'ImageObject';
        } elseif (strpos($mime, 'audio/') === 0) {
            $type = 'AudioObject';
        } elseif (strpos($mime, 'video/') === 0) {
            $type = 'VideoObject';
        }
        // PDFs stay MediaObject (encodingFormat application/pdf): only
        // MediaObject types carry contentUrl and contentSize.
        $m = array(
            '@type'          => $type,
            'contentUrl'     => nessia_schema_abs($file->getWebPath('original')),
            'encodingFormat' => $mime,
            'name'           => (string) $file->original_filename,
        );
        if ($file->size) {
            $m['contentSize'] = (int) $file->size . ' B';
        }
        if ($file->hasThumbnail()) {
            $m['thumbnailUrl'] = nessia_schema_abs($file->getWebPath('thumbnail'));
            if (empty($node['image'])) {
                $node['image'] = nessia_schema_abs($file->getWebPath('fullsize'));
            }
        }
        $media[] = $m;
    }
    if (!$media) {
        return;
    }
    // Audio and video records carry their own file as contentUrl.
    if (in_array($node['@type'][0], array('AudioObject', 'VideoObject'), true)) {
        foreach ($media as $m) {
            if ($m['@type'] === $node['@type'][0]) {
                $node['contentUrl'] = $m['contentUrl'];
                $node['encodingFormat'] = $m['encodingFormat'];
                break;
            }
        }
    }
    $node['associatedMedia'] = count($media) === 1 ? $media[0] : $media;
}

/** Fields shared by items and collections. */
function nessia_schema_dc_fields($record, array &$node)
{
    if ($v = nessia_schema_dc($record, 'Description')) {
        $node['description'] = mb_substr($v[0], 0, 5000);
    }
    if ($v = nessia_schema_dc($record, 'Creator')) {
        $node['creator'] = nessia_schema_agents($v);
        if ($record instanceof Item) {
            $node['author'] = $node['creator'];
        }
    }
    if ($v = nessia_schema_dc($record, 'Contributor')) {
        $node['contributor'] = nessia_schema_agents($v);
    }
    if ($v = nessia_schema_dc($record, 'Publisher')) {
        $node['publisher'] = array('@type' => 'Organization', 'name' => $v[0]);
    }
    if ($v = nessia_schema_dc($record, 'Date')) {
        if ($d = nessia_schema_date($v[0])) {
            $node['dateCreated'] = $d;
        }
        if (count($v) > 1 || !$d) {
            $node['temporalCoverage'] = nessia_schema_one_or_many($v);
        }
    }
    if ($v = nessia_schema_dc($record, 'Subject')) {
        $node['about'] = array_map(function ($s) { return array('@type' => 'Thing', 'name' => $s); }, $v);
        $node['keywords'] = implode(', ', $v);
    }
    if ($v = nessia_schema_dc($record, 'Language')) {
        $node['inLanguage'] = nessia_schema_one_or_many($v);
    }
    if ($v = nessia_schema_dc($record, 'Rights')) {
        if (preg_match('#^https?://#i', $v[0])) {
            $node['license'] = $v[0];
        } else {
            $node['copyrightNotice'] = $v[0];
        }
    }
    if ($v = nessia_schema_dc($record, 'Identifier')) {
        $node['identifier'] = nessia_schema_one_or_many($v);
    }
    if ($v = nessia_schema_dc($record, 'Relation')) {
        $node['isRelatedTo'] = nessia_schema_one_or_many($v);
    }
    if ($v = nessia_schema_dc($record, 'Source')) {
        $node['isBasedOn'] = nessia_schema_one_or_many($v);
    }
    if ($v = nessia_schema_dc($record, 'Coverage')) {
        $node['spatialCoverage'] = nessia_schema_one_or_many($v);
    }
    if (!empty($record->added)) {
        $node['dateCreated'] = isset($node['dateCreated']) ? $node['dateCreated'] : date('c', strtotime($record->added));
    }
    if (!empty($record->modified)) {
        $node['dateModified'] = date('c', strtotime($record->modified));
    }
    $node['holdingArchive'] = array('@id' => nessia_schema_abs('/') . '#archive');
}

/** Node for an item. */
function nessia_schema_item_node($item, $id = null)
{
    $url = record_url($item, 'show', true);
    $node = array(
        '@type' => nessia_schema_item_types(metadata($item, 'Item Type Name')),
        '@id'   => $id ? $id : $url . '#record',
        'url'   => $url,
        'name'  => strip_formatting(metadata($item, array('Dublin Core', 'Title'))),
    );
    if ($node['name'] === '') {
        $node['name'] = __('Untitled');
    }
    nessia_schema_dc_fields($item, $node);
    nessia_schema_files($item, $node);
    if ($item->collection_id && ($c = get_record_by_id('Collection', $item->collection_id))) {
        $node['isPartOf'] = array('@type' => array('Collection', 'ArchiveComponent'), '@id' => record_url($c, 'show', true) . '#record', 'name' => strip_formatting(metadata($c, array('Dublin Core', 'Title'))));
    }
    if (metadata($item, 'has tags')) {
        $tags = array();
        foreach ($item->getTags() as $tag) {
            $tags[] = $tag->name;
        }
        $all = array_merge(isset($node['keywords']) ? explode(', ', $node['keywords']) : array(), $tags);
        $seen = array();
        foreach ($all as $k) {
            $seen[mb_strtolower(trim($k))] = isset($seen[mb_strtolower(trim($k))]) ? $seen[mb_strtolower(trim($k))] : trim($k);
        }
        $node['keywords'] = implode(', ', array_filter($seen));
    }
    if (!empty($node['author']) && $node['@type'][0] === 'ScholarlyArticle' && empty($node['headline'])) {
        $node['headline'] = mb_substr($node['name'], 0, 110);
    }
    return $node;
}

/** Node for a collection, with its items as hasPart. */
function nessia_schema_collection_node($collection)
{
    $url = record_url($collection, 'show', true);
    $node = array(
        '@type' => array('Collection', 'ArchiveComponent'),
        '@id'   => $url . '#record',
        'url'   => $url,
        'name'  => strip_formatting(metadata($collection, array('Dublin Core', 'Title'))),
    );
    nessia_schema_dc_fields($collection, $node);
    if (function_exists('nessia_collection_count')) {
        $node['collectionSize'] = nessia_collection_count($collection->id);
    }
    if (function_exists('nessia_collection_cover') && ($img = nessia_collection_cover($collection))) {
        $node['image'] = nessia_schema_abs($img);
    }
    $parts = array();
    foreach (get_records('Item', array('collection' => $collection->id, 'sort_field' => 'added', 'sort_dir' => 'd'), 50) as $item) {
        $parts[] = array('@id' => record_url($item, 'show', true) . '#record', 'name' => strip_formatting(metadata($item, array('Dublin Core', 'Title'))), 'url' => record_url($item, 'show', true));
    }
    if ($parts) {
        $node['hasPart'] = $parts;
    }
    if (function_exists('nessia_collection_tree')) {
        foreach (nessia_collection_tree() as $n) {
            foreach ($n['children'] as $child) {
                if ($child['id'] === (int) $collection->id) {
                    $node['isPartOf'] = array('@id' => nessia_schema_abs($n['url']) . '#record', 'name' => $n['title']);
                }
            }
        }
    }
    return $node;
}

/**
 * Build the @graph for the current page.
 *
 * @param string|null $title Title passed to head().
 * @return array
 */
function nessia_schema_graph($title = null)
{
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $controller = $request ? $request->getControllerName() : '';
    $action = $request ? $request->getActionName() : '';
    $module = $request ? $request->getModuleName() : '';
    $pageUrl = rtrim(WEB_ROOT, '/') . ($request ? $request->getPathInfo() : '/');
    $home = nessia_schema_abs('/');

    $graph = array(nessia_schema_archive_node(), nessia_schema_website_node());
    $page = array(
        '@type'    => 'WebPage',
        '@id'      => $pageUrl . '#webpage',
        'url'      => $pageUrl,
        'name'     => $title ? strip_formatting($title) . ' | ' . strip_formatting(option('site_title')) : strip_formatting(option('site_title')),
        'isPartOf' => array('@id' => $home . '#website'),
        'inLanguage' => get_html_lang(),
    );
    $crumbs = array(array(__('Home'), $home));
    $record = null;

    if ($controller === 'items' && $action === 'show' && ($item = get_current_record('item', false))) {
        $record = nessia_schema_item_node($item);
        $page['@type'] = 'ItemPage';
        $page['url'] = $page['@id'] = null;
        $page['url'] = $record['url'];
        $page['@id'] = $record['url'] . '#webpage';
        $crumbs[] = array(__('Items'), nessia_schema_abs(url('items/browse')));
        if (!empty($record['image'])) {
            $page['primaryImageOfPage'] = array('@type' => 'ImageObject', 'url' => $record['image']);
        }
    } elseif ($controller === 'collections' && $action === 'show' && ($collection = get_current_record('collection', false))) {
        $record = nessia_schema_collection_node($collection);
        $page['@type'] = 'CollectionPage';
        $page['url'] = $record['url'];
        $page['@id'] = $record['url'] . '#webpage';
        $crumbs[] = array(__('Collections'), nessia_schema_abs(url('collections/browse')));
    } elseif (in_array($controller, array('items', 'collections'), true) && $action === 'browse') {
        $page['@type'] = 'CollectionPage';
        $records = $controller === 'items' ? get_loop_records('items', false) : get_loop_records('collections', false);
        if ($records) {
            $list = array('@type' => 'ItemList', '@id' => $pageUrl . '#list', 'numberOfItems' => count($records), 'itemListElement' => array());
            $pos = 1;
            foreach ($records as $r) {
                $list['itemListElement'][] = array('@type' => 'ListItem', 'position' => $pos++, 'url' => record_url($r, 'show', true), 'name' => strip_formatting(metadata($r, array('Dublin Core', 'Title'))));
            }
            $graph[] = $list;
            $page['mainEntity'] = array('@id' => $list['@id']);
        }
    } elseif ($controller === 'search') {
        $page['@type'] = 'SearchResultsPage';
    } elseif ($module === 'simple-pages' || $controller === 'page') {
        $slug = '';
        if (($sp = get_current_record('simple_pages_page', false))) {
            $slug = (string) $sp->slug;
        }
        if (preg_match('/about/i', $slug)) {
            $page['@type'] = 'AboutPage';
        } elseif (preg_match('/contact/i', $slug)) {
            $page['@type'] = 'ContactPage';
        } elseif ($slug === 'overview') {
            $page['@type'] = 'CollectionPage';
        }
    } elseif ($module === 'simple-contact-form' || $controller === 'contact') {
        $page['@type'] = 'ContactPage';
    } elseif ($pageUrl === $home || $pageUrl === rtrim($home, '/')) {
        $page['@type'] = isset($_GET['overview']) ? 'CollectionPage' : 'WebPage';
        $page['about'] = array('@id' => $home . '#archive');
    }

    if ($record) {
        $graph[] = $record;
        $page['mainEntity'] = array('@id' => $record['@id']);
    }
    if ($title && $pageUrl !== $home) {
        $crumbs[] = array(strip_formatting($title), $page['url']);
        $bc = array('@type' => 'BreadcrumbList', '@id' => $page['url'] . '#breadcrumb', 'itemListElement' => array());
        foreach ($crumbs as $i => $c) {
            $bc['itemListElement'][] = array('@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0], 'item' => $c[1]);
        }
        $graph[] = $bc;
        $page['breadcrumb'] = array('@id' => $bc['@id']);
    }
    $graph[] = $page;

    return array('@context' => 'https://schema.org', '@graph' => nessia_schema_clean($graph));
}

/** Drop empty values. */
function nessia_schema_clean($v)
{
    if (!is_array($v)) {
        return $v;
    }
    $out = array();
    foreach ($v as $k => $x) {
        $x = nessia_schema_clean($x);
        if ($x === null || $x === '' || $x === array()) {
            continue;
        }
        $out[$k] = $x;
    }
    return array_keys($out) === range(0, count($out) - 1) ? array_values($out) : $out;
}

/** <script type="application/ld+json"> for <head>. */
function nessia_schema_script($title = null)
{
    if (isset($_GET['nessia-tools'])) {
        return '';
    }
    try {
        $json = json_encode(nessia_schema_graph($title), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    } catch (Exception $e) {
        _log('NESSIA schema: ' . $e->getMessage(), Zend_Log::WARN);
        return '';
    }
    return $json ? '<script type="application/ld+json">' . $json . '</script>' . "\n" : '';
}
