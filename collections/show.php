<?php
/**
 * NESSIA — collection landing page.
 * Hero with the collection's newest picture, figures, sub-collections,
 * featured and newest items, what it holds by type, and neighbouring topics.
 */
$collection = get_current_record('collection');
$title = metadata($collection, array('Dublin Core', 'Title'));
$description = metadata($collection, array('Dublin Core', 'Description'));
$family = nessia_collection_family($collection->id);
$count = nessia_collection_count($collection->id);
$cover = nessia_collection_cover($collection);
$types = nessia_type_counts($family);

// Sub-collections and siblings from the tree.
$children = array();
$siblings = array();
foreach (nessia_collection_tree() as $node) {
    if ($node['id'] === (int) $collection->id) {
        $children = $node['children'];
    }
    foreach ($node['children'] as $child) {
        if ($child['id'] === (int) $collection->id) {
            $siblings = array_values(array_filter($node['children'], function ($c) use ($collection) { return $c['id'] !== (int) $collection->id; }));
            $parent = $node;
        }
    }
}
if (!$siblings && !$children) {
    $siblings = array_values(array_filter(nessia_collection_tree(), function ($c) use ($collection) { return $c['id'] !== (int) $collection->id; }));
}

echo head(array('title' => $title, 'bodyclass' => 'collections show nessia-landing'));
?>

<h1><?php echo $title; ?></h1>

<section class="nessia-hero nessia-hero--collection<?php echo $cover ? ' has-image' : ''; ?>"<?php echo nessia_bg($cover); ?>>
    <div class="nessia-hero-inner">
        <?php if (!empty($parent)): ?>
        <p class="nessia-hero-kicker"><a href="<?php echo html_escape($parent['url']); ?>"><?php echo html_escape($parent['title']); ?></a></p>
        <?php else: ?>
        <p class="nessia-hero-kicker"><?php echo __('Collection'); ?></p>
        <?php endif; ?>
        <h2 class="nessia-hero-title"><?php echo $title; ?></h2>
        <?php if ($description): ?>
        <div class="nessia-hero-text"><?php echo $description; ?></div>
        <?php endif; ?>
        <ul class="nessia-hero-facts">
            <li><strong><?php echo number_format($count); ?></strong> <?php echo __(plural('item', 'items', $count)); ?></li>
            <?php foreach (array_slice($types, 0, 3) as $t): ?>
            <li><strong><?php echo (int) $t['n']; ?></strong> <?php echo html_escape(__($t['name'])); ?></li>
            <?php endforeach; ?>
        </ul>
        <a class="nessia-hero-btn" href="<?php echo html_escape(url('items/browse', array('collection' => $collection->id))); ?>"><?php echo __('Browse this collection'); ?></a>
    </div>
</section>

<?php if ($children): ?>
<?php echo nessia_block_collections($children, __('Inside %s', strip_formatting($title))); ?>
<?php endif; ?>

<?php
$featured = get_records('Item', array('collection' => $collection->id, 'featured' => 1, 'sort_field' => 'added', 'sort_dir' => 'd'), 4);
if ($featured) {
    echo nessia_block_items($featured, __('Highlights'));
}
$recent = get_records('Item', array('collection' => $collection->id, 'sort_field' => 'added', 'sort_dir' => 'd'), 8);
echo nessia_block_items($recent, __('Newest in this collection'),
    $count > count($recent) ? url('items/browse', array('collection' => $collection->id)) : '', __('See all %s', $count));
if (!$recent && !$children) {
    echo '<p class="nessia-empty">' . __('Items will appear here as they are added.') . '</p>';
}
?>

<?php if (count($types) > 1): ?>
<section class="nessia-section">
    <h2 class="nessia-section-title"><?php echo __('What it holds'); ?></h2>
    <ul class="nessia-chips">
        <?php foreach ($types as $t): ?>
        <li><a href="<?php echo html_escape(url('items/browse', array('collection' => $collection->id, 'type' => $t['id']))); ?>"><?php echo html_escape(__($t['name'])); ?> <span><?php echo (int) $t['n']; ?></span></a></li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>

<?php fire_plugin_hook('public_collections_show', array('view' => $this, 'collection' => $collection)); ?>

<?php if ($siblings): ?>
<?php echo nessia_block_collections(array_slice($siblings, 0, 6), __('Related topics')); ?>
<?php endif; ?>

<?php echo foot(); ?>
