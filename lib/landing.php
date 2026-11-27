<?php
/**
 * NESSIA Theme — landing pages, overview and shortcodes.
 *
 * Images: every card and hero shows a picture. The picture comes from, in
 * order: the record's own image file, the newest image in the collection
 * (or its sub-collections), and finally a generated cover in the colour
 * scheme. No card is ever left blank.
 *
 * @package NESSIA
 */

/* ==========================================================================
   Images
   ========================================================================== */

/**
 * URL of an item's first image derivative.
 *
 * @param Item   $item
 * @param string $size fullsize|thumbnail|square_thumbnail
 * @return string|null
 */
function nessia_item_image_url($item, $size = 'fullsize')
{
    if (!$item) {
        return null;
    }
    foreach ($item->getFiles() as $file) {
        if ($file->hasThumbnail()) {
            return $file->getWebPath($size);
        }
    }
    return null;
}

/**
 * IDs of a collection and its sub-collections (Collection Tree), if any.
 *
 * @param int $collectionId
 * @return int[]
 */
function nessia_collection_family($collectionId)
{
    $ids = array((int) $collectionId);
    if (plugin_is_active('CollectionTree')) {
        $db = get_db();
        $children = $db->fetchCol(
            "SELECT collection_id FROM `{$db->prefix}collection_trees` WHERE parent_collection_id = ?",
            array((int) $collectionId)
        );
        foreach ($children as $child) {
            $ids[] = (int) $child;
        }
    }
    return $ids;
}

/**
 * Cover image for a collection: the newest public item with an image in
 * the collection or its sub-collections.
 *
 * @param Collection|int $collection
 * @param string $size
 * @return string|null
 */
function nessia_collection_cover($collection, $size = 'fullsize')
{
    static $cache = array();
    $id = is_object($collection) ? (int) $collection->id : (int) $collection;
    $key = $id . ':' . $size;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    foreach (nessia_collection_family($id) as $cid) {
        $items = get_records('Item', array('collection' => $cid, 'hasImage' => true, 'sort_field' => 'added', 'sort_dir' => 'd'), 1);
        if ($items && ($url = nessia_item_image_url($items[0], $size))) {
            return $cache[$key] = $url;
        }
    }
    return $cache[$key] = null;
}

/**
 * A picture for the whole site: a featured item's image, else the newest image.
 *
 * @return string|null
 */
function nessia_site_cover()
{
    static $url = false;
    if ($url !== false) {
        return $url;
    }
    $items = get_records('Item', array('featured' => 1, 'hasImage' => true, 'sort_field' => 'added', 'sort_dir' => 'd'), 1);
    if (!$items) {
        $items = get_records('Item', array('hasImage' => true, 'sort_field' => 'added', 'sort_dir' => 'd'), 1);
    }
    return $url = ($items ? nessia_item_image_url($items[0]) : null);
}

/**
 * <img> or the generated cover when there is no picture.
 *
 * @param string|null $url
 * @param string $label Shown on the generated cover.
 * @return string HTML
 */
function nessia_picture($url, $label = '')
{
    if ($url) {
        return '<img src="' . html_escape($url) . '" alt="" loading="lazy">';
    }
    return '<span class="nessia-generated-cover" aria-hidden="true"><span>' . html_escape($label) . '</span></span>';
}

/**
 * Inline style for a background picture, or '' (the CSS pattern shows).
 */
function nessia_bg($url)
{
    return $url ? ' style="background-image:url(\'' . html_escape($url) . '\')"' : '';
}

/* ==========================================================================
   Data helpers
   ========================================================================== */

/**
 * Number of public items in a collection family.
 */
function nessia_collection_count($collectionId)
{
    $db = get_db();
    $ids = nessia_collection_family($collectionId);
    return (int) $db->fetchOne(
        "SELECT COUNT(*) FROM {$db->Item} WHERE public = 1 AND collection_id IN (" . implode(',', array_map('intval', $ids)) . ")"
    );
}

/**
 * Item types and counts within a set of collections (or the whole archive).
 *
 * @param int[]|null $collectionIds
 * @return array[] rows with id, name, n
 */
function nessia_type_counts($collectionIds = null)
{
    $db = get_db();
    $where = 'i.public = 1';
    if ($collectionIds) {
        $where .= ' AND i.collection_id IN (' . implode(',', array_map('intval', $collectionIds)) . ')';
    }
    return $db->fetchAll(
        "SELECT it.id, it.name, COUNT(i.id) AS n FROM {$db->Item} i
         JOIN {$db->ItemType} it ON it.id = i.item_type_id
         WHERE $where GROUP BY it.id, it.name ORDER BY n DESC, it.name"
    );
}

/**
 * Public collections that are not sub-collections, with counts and covers.
 *
 * @return array[] id, title, description, count, cover, children (array of same)
 */
function nessia_collection_tree()
{
    static $tree = null;
    if ($tree !== null) {
        return $tree;
    }
    $db = get_db();
    $parents = array();
    if (plugin_is_active('CollectionTree')) {
        foreach ($db->fetchAll("SELECT parent_collection_id AS p, collection_id AS c FROM `{$db->prefix}collection_trees`") as $r) {
            $parents[(int) $r['c']] = (int) $r['p'];
        }
    }
    $nodes = array();
    foreach (get_records('Collection', array('public' => 1, 'sort_field' => 'Dublin Core,Title'), 0) as $c) {
        $nodes[$c->id] = array(
            'id'          => (int) $c->id,
            'title'       => metadata($c, array('Dublin Core', 'Title')),
            'description' => metadata($c, array('Dublin Core', 'Description'), array('snippet' => 180)),
            'url'         => record_url($c, 'show'),
            'record'      => $c,
            'children'    => array(),
        );
    }
    $tree = array();
    foreach ($nodes as $id => $node) {
        $p = isset($parents[$id]) ? $parents[$id] : 0;
        if ($p && isset($nodes[$p])) {
            $nodes[$p]['children'][] = &$nodes[$id];
        }
    }
    foreach ($nodes as $id => $node) {
        if (empty($parents[$id]) || !isset($nodes[$parents[$id]])) {
            $tree[] = &$nodes[$id];
        }
    }
    foreach ($tree as &$n) {
        $n['count'] = nessia_collection_count($n['id']);
        $n['cover'] = nessia_collection_cover($n['id']);
    }
    unset($n);
    return $tree;
}

/**
 * Archive figures.
 */
function nessia_stats()
{
    $db = get_db();
    $lang = $db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Language');
    $subj = $db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Subject');
    return array(
        'items'       => total_records('Item'),
        'collections' => total_records('Collection'),
        'files'       => (int) $db->fetchOne("SELECT COUNT(*) FROM {$db->File} f JOIN {$db->Item} i ON i.id = f.item_id WHERE i.public = 1"),
        'subjects'    => $subj ? (int) $db->fetchOne("SELECT COUNT(DISTINCT text) FROM {$db->ElementText} WHERE record_type = 'Item' AND element_id = ?", array($subj->id)) : 0,
        'languages'   => $lang ? (int) $db->fetchOne("SELECT COUNT(DISTINCT text) FROM {$db->ElementText} WHERE record_type = 'Item' AND element_id = ?", array($lang->id)) : 0,
    );
}

/**
 * Values of a Dublin Core element across public items, with counts.
 */
function nessia_element_counts($elementName, $limit = 12)
{
    $db = get_db();
    $el = $db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', $elementName);
    if (!$el) {
        return array();
    }
    return $db->fetchAll(
        "SELECT et.text AS name, COUNT(DISTINCT et.record_id) AS n
         FROM {$db->ElementText} et JOIN {$db->Item} i ON i.id = et.record_id AND i.public = 1
         WHERE et.record_type = 'Item' AND et.element_id = ?
         GROUP BY et.text ORDER BY n DESC, et.text LIMIT " . (int) $limit,
        array($el->id)
    );
}

/**
 * Items grouped by century from Dublin Core Date (the first year found).
 *
 * @return array century label => count, oldest first
 */
function nessia_timeline()
{
    $buckets = array();
    foreach (nessia_element_counts('Date', 2000) as $row) {
        if (!preg_match('/(\d{3,4})/', $row['name'], $m)) {
            continue;
        }
        $year = (int) $m[1];
        $c = (int) floor(($year - 1) / 100) + 1;
        $buckets[$c] = (isset($buckets[$c]) ? $buckets[$c] : 0) + (int) $row['n'];
    }
    ksort($buckets);
    $out = array();
    foreach ($buckets as $c => $n) {
        $suffix = ($c % 100 >= 11 && $c % 100 <= 13) ? 'th' : (array(1 => 'st', 2 => 'nd', 3 => 'rd')[$c % 10] ?? 'th');
        $out[__('%s century', $c . $suffix)] = $n;
    }
    return $out;
}

/* ==========================================================================
   Reusable blocks (used by templates and shortcodes)
   ========================================================================== */

/**
 * Full-width hero with a picture.
 */
function nessia_block_hero(array $a)
{
    $a += array('title' => '', 'text' => '', 'button' => '', 'link' => '', 'image' => '', 'kicker' => '');
    $img = $a['image'] !== '' ? $a['image'] : nessia_site_cover();
    $html = '<section class="nessia-hero' . ($img ? ' has-image' : '') . '"' . nessia_bg($img) . '><div class="nessia-hero-inner">';
    if ($a['kicker'] !== '') {
        $html .= '<p class="nessia-hero-kicker">' . html_escape($a['kicker']) . '</p>';
    }
    if ($a['title'] !== '') {
        $html .= '<h2 class="nessia-hero-title">' . html_escape($a['title']) . '</h2>';
    }
    if ($a['text'] !== '') {
        $html .= '<p class="nessia-hero-text">' . html_escape($a['text']) . '</p>';
    }
    if ($a['button'] !== '') {
        $link = $a['link'] !== '' ? $a['link'] : url('items/browse');
        $html .= '<a class="nessia-hero-btn" href="' . html_escape($link) . '">' . html_escape($a['button']) . '</a>';
    }
    return $html . '</div></section>';
}

/**
 * Collection cards with cover pictures.
 *
 * @param array[] $nodes from nessia_collection_tree()
 */
function nessia_block_collections(array $nodes, $heading = '')
{
    if (!$nodes) {
        return '';
    }
    $html = '<section class="nessia-section">';
    if ($heading !== '') {
        $html .= '<h2 class="nessia-section-title">' . html_escape($heading) . '</h2>';
    }
    $html .= '<ul class="nessia-collection-cards">';
    foreach ($nodes as $n) {
        $count = isset($n['count']) ? $n['count'] : nessia_collection_count($n['id']);
        $cover = isset($n['cover']) ? $n['cover'] : nessia_collection_cover($n['id']);
        $html .= '<li><a class="nessia-ccard" href="' . html_escape($n['url']) . '">'
            . '<span class="nessia-ccard-img">' . nessia_picture($cover, $n['title']) . '</span>'
            . '<span class="nessia-ccard-body"><span class="nessia-ccard-title">' . html_escape($n['title']) . '</span>'
            . '<span class="nessia-ccard-meta">' . __(plural('%s item', '%s items', $count), $count) . '</span>';
        if (!empty($n['description'])) {
            $html .= '<span class="nessia-ccard-text">' . html_escape(strip_formatting($n['description'])) . '</span>';
        }
        $html .= '</span></a></li>';
    }
    return $html . '</ul></section>';
}

/**
 * Shelf of items with pictures.
 *
 * @param Item[] $items
 */
function nessia_block_items(array $items, $heading = '', $moreUrl = '', $moreLabel = '')
{
    if (!$items) {
        return '';
    }
    $html = '<section class="nessia-section">';
    if ($heading !== '' || $moreUrl !== '') {
        $html .= '<div class="nessia-section-head">';
        if ($heading !== '') {
            $html .= '<h2 class="nessia-section-title">' . html_escape($heading) . '</h2>';
        }
        if ($moreUrl !== '') {
            $html .= '<a class="nessia-section-more" href="' . html_escape($moreUrl) . '">' . html_escape($moreLabel ? $moreLabel : __('See all')) . ' →</a>';
        }
        $html .= '</div>';
    }
    $html .= '<div class="nessia-shelf">';
    foreach ($items as $item) {
        $title = metadata($item, array('Dublin Core', 'Title'));
        $creator = metadata($item, array('Dublin Core', 'Creator'));
        $type = metadata($item, 'Item Type Name');
        $img = nessia_item_image_url($item, 'thumbnail');
        $url = record_url($item, 'show');
        $html .= '<article class="nessia-shelf-item"><a class="nessia-cover" href="' . html_escape($url) . '" tabindex="-1" aria-hidden="true">'
            . ($img ? '<img src="' . html_escape($img) . '" alt="" loading="lazy">'
                    : '<span class="nessia-cover-placeholder"><span>' . html_escape($type ? $type : __('Item')) . '</span></span>')
            . '</a><h3><a href="' . html_escape($url) . '">' . $title . '</a></h3>'
            . ($creator ? '<p class="item-creator">' . $creator . '</p>' : '')
            . '</article>';
    }
    return $html . '</div></section>';
}

/**
 * Large feature cards for featured items.
 */
function nessia_block_featured($limit = 3)
{
    $items = get_records('Item', array('featured' => 1, 'sort_field' => 'added', 'sort_dir' => 'd'), (int) $limit);
    if (!$items) {
        return '';
    }
    $html = '<section class="nessia-section"><h2 class="nessia-section-title">' . __('Featured') . '</h2><div class="nessia-features">';
    foreach ($items as $item) {
        $title = metadata($item, array('Dublin Core', 'Title'));
        $desc = metadata($item, array('Dublin Core', 'Description'), array('snippet' => 200));
        $img = nessia_item_image_url($item);
        if (!$img && $item->collection_id) {
            $img = nessia_collection_cover($item->collection_id);
        }
        $html .= '<a class="nessia-feature" href="' . html_escape(record_url($item, 'show')) . '">'
            . '<span class="nessia-feature-img">' . nessia_picture($img, metadata($item, 'Item Type Name')) . '</span>'
            . '<span class="nessia-feature-body"><span class="nessia-feature-title">' . $title . '</span>'
            . ($desc ? '<span class="nessia-feature-text">' . strip_formatting($desc) . '</span>' : '')
            . '</span></a>';
    }
    return $html . '</div></section>';
}

/**
 * Figures row.
 */
function nessia_block_stats()
{
    $s = nessia_stats();
    $labels = array(
        'items'       => __('Items'),
        'collections' => __('Collections'),
        'files'       => __('Files'),
        'subjects'    => __('Subjects'),
        'languages'   => __('Languages'),
    );
    $html = '<section class="nessia-stats" aria-label="' . html_escape(__('The archive in figures')) . '">';
    foreach ($labels as $k => $label) {
        if ($k !== 'items' && $k !== 'collections' && empty($s[$k])) {
            continue;
        }
        $html .= '<div class="nessia-stat"><span class="nessia-stat-n">' . number_format((int) $s[$k]) . '</span><span class="nessia-stat-l">' . $label . '</span></div>';
    }
    return $html . '</section>';
}

/**
 * Pull quote.
 */
function nessia_block_quote(array $a)
{
    $a += array('text' => '', 'source' => '');
    if ($a['text'] === '') {
        return '';
    }
    return '<figure class="nessia-quote"><blockquote>' . html_escape($a['text']) . '</blockquote>'
        . ($a['source'] !== '' ? '<figcaption>' . html_escape($a['source']) . '</figcaption>' : '') . '</figure>';
}

/**
 * Picture gallery of items with images (opens in the lightbox).
 */
function nessia_block_gallery(array $a)
{
    $params = array('hasImage' => true, 'sort_field' => 'added', 'sort_dir' => 'd');
    if (!empty($a['collection'])) {
        $c = nessia_find_collection($a['collection']);
        if ($c) {
            $params['collection'] = $c->id;
        }
    }
    if (!empty($a['tag'])) {
        $params['tags'] = $a['tag'];
    }
    $items = get_records('Item', $params, isset($a['limit']) ? max(1, min(48, (int) $a['limit'])) : 12);
    if (!$items) {
        return '';
    }
    $html = '<section class="nessia-section">'
        . (!empty($a['title']) ? '<h2 class="nessia-section-title">' . html_escape($a['title']) . '</h2>' : '')
        . '<div class="nessia-gallery">';
    foreach ($items as $item) {
        $full = nessia_item_image_url($item, 'fullsize');
        $sq = nessia_item_image_url($item, 'square_thumbnail');
        $title = strip_formatting(metadata($item, array('Dublin Core', 'Title')));
        $html .= '<a href="' . html_escape($full) . '" data-lightbox="nessia-gallery" data-title="' . html_escape($title) . '">'
            . '<img src="' . html_escape($sq) . '" alt="' . html_escape($title) . '" loading="lazy"></a>';
    }
    return $html . '</div></section>';
}

/**
 * Find a collection by ID or exact title.
 */
function nessia_find_collection($ref)
{
    if (ctype_digit((string) $ref)) {
        return get_record_by_id('Collection', (int) $ref);
    }
    foreach (nessia_collection_tree() as $n) {
        if (strcasecmp($n['title'], $ref) === 0) {
            return $n['record'];
        }
        foreach ($n['children'] as $ch) {
            if (strcasecmp($ch['title'], $ref) === 0) {
                return $ch['record'];
            }
        }
    }
    return null;
}

/**
 * The whole overview page body.
 */
function nessia_block_overview()
{
    $html = nessia_block_stats();
    $html .= nessia_block_collections(nessia_collection_tree(), __('Collections'));

    $types = nessia_type_counts();
    $timeline = nessia_timeline();
    $langs = nessia_element_counts('Language', 10);
    if ($types || $timeline || $langs) {
        $html .= '<section class="nessia-section"><h2 class="nessia-section-title">' . __('What the archive holds') . '</h2><div class="nessia-facets">';
        if ($types) {
            $max = max(array_map(function ($r) { return (int) $r['n']; }, $types));
            $html .= '<div class="nessia-facet"><h3>' . __('By type') . '</h3><ul class="nessia-bars">';
            foreach ($types as $t) {
                $html .= '<li><a href="' . html_escape(url('items/browse', array('type' => $t['id']))) . '"><span class="nessia-bar-l">' . html_escape(__($t['name'])) . '</span><span class="nessia-bar"><span style="width:' . round(100 * $t['n'] / $max) . '%"></span></span><span class="nessia-bar-n">' . (int) $t['n'] . '</span></a></li>';
            }
            $html .= '</ul></div>';
        }
        if ($timeline) {
            $max = max($timeline);
            $html .= '<div class="nessia-facet"><h3>' . __('By date of the source') . '</h3><ul class="nessia-bars">';
            foreach ($timeline as $label => $n) {
                $html .= '<li><span class="nessia-bar-l">' . html_escape($label) . '</span><span class="nessia-bar"><span style="width:' . round(100 * $n / $max) . '%"></span></span><span class="nessia-bar-n">' . (int) $n . '</span></li>';
            }
            $html .= '</ul></div>';
        }
        if ($langs) {
            $html .= '<div class="nessia-facet"><h3>' . __('By language') . '</h3><ul class="nessia-chips">';
            foreach ($langs as $l) {
                $html .= '<li><a href="' . html_escape(url('items/browse', array('search' => $l['name']))) . '">' . html_escape($l['name']) . ' <span>' . (int) $l['n'] . '</span></a></li>';
            }
            $html .= '</ul></div>';
        }
        $html .= '</div></section>';
    }

    $html .= nessia_block_items(get_recent_items(8), __('Recently added'), url('items/browse'), __('Browse all items'));
    return $html;
}

/* ==========================================================================
   Shortcodes for Simple Pages and exhibits
   ========================================================================== */

function nessia_sc_items($args)
{
    $params = array('sort_field' => 'added', 'sort_dir' => 'd');
    if (!empty($args['collection']) && ($c = nessia_find_collection($args['collection']))) {
        $params['collection'] = $c->id;
    }
    if (!empty($args['type'])) {
        $params['type'] = $args['type'];
    }
    if (!empty($args['tag'])) {
        $params['tags'] = $args['tag'];
    }
    if (!empty($args['featured'])) {
        $params['featured'] = 1;
    }
    $limit = isset($args['limit']) ? max(1, min(48, (int) $args['limit'])) : 8;
    return nessia_block_items(get_records('Item', $params, $limit), isset($args['title']) ? $args['title'] : '',
        isset($args['more']) ? $args['more'] : '', isset($args['more_label']) ? $args['more_label'] : '');
}

function nessia_sc_collections($args)
{
    $nodes = nessia_collection_tree();
    if (!empty($args['parent']) && ($p = nessia_find_collection($args['parent']))) {
        foreach ($nodes as $n) {
            if ($n['id'] === (int) $p->id) {
                $nodes = $n['children'];
                break;
            }
        }
    }
    return nessia_block_collections($nodes, isset($args['title']) ? $args['title'] : '');
}

function nessia_register_shortcodes()
{
    if (!function_exists('add_shortcode')) {
        return;
    }
    add_shortcode('nessia_hero', function ($a) { return nessia_block_hero((array) $a); });
    add_shortcode('nessia_items', 'nessia_sc_items');
    add_shortcode('nessia_collections', 'nessia_sc_collections');
    add_shortcode('nessia_topics', function () { return nessia_explore_topics(); });
    add_shortcode('nessia_stats', function () { return nessia_block_stats(); });
    add_shortcode('nessia_featured', function ($a) { return nessia_block_featured(isset($a['limit']) ? (int) $a['limit'] : 3); });
    add_shortcode('nessia_quote', function ($a) { return nessia_block_quote((array) $a); });
    add_shortcode('nessia_gallery', function ($a) { return nessia_block_gallery((array) $a); });
    add_shortcode('nessia_overview', function () { return nessia_block_overview(); });
}
