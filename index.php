<?php
if (nessia_tools_requested()) {
    include dirname(__FILE__) . '/common/nessia-tools.php';
    return;
}
?>
<?php echo head(array('bodyid' => 'home', 'bodyclass' => 'two-col')); ?>

<?php if ($homepageText = nessia_option('homepage_text')): ?>
<div id="intro" class="nessia-intro">
    <?php echo $homepageText; ?>
</div>
<?php elseif ($siteDescription = option('description')): ?>
<div id="intro" class="nessia-intro">
    <p><?php echo html_escape($siteDescription); ?></p>
</div>
<?php endif; ?>

<?php echo nessia_explore_topics(); ?>

<?php fire_plugin_hook('public_home', array('view' => $this)); ?>

<?php if (($featured = random_featured_items(1))): ?>
<div id="featured-item">
    <h2><?php echo __('Featured Item'); ?></h2>
    <?php echo $featured; ?>
</div>
<?php endif; ?>

<?php
$recentCount = (int) nessia_option('homepage_recent_items');
if ($recentCount < 1) {
    $recentCount = 5;
}
$recentItems = get_recent_items($recentCount);
?>
<?php if ($recentItems): ?>
<div id="recent-items">
    <h2><?php echo __('Recently Added Items'); ?></h2>
    <div class="items-list">
        <?php foreach (loop('items', $recentItems) as $item): ?>
        <?php $hasThumb = metadata('item', 'has thumbnail'); ?>
        <div class="item hentry<?php echo $hasThumb ? ' has-thumb' : ''; ?>">
            <?php if ($hasThumb): ?>
            <div class="item-img">
                <?php echo link_to_item(item_image('thumbnail', array('alt' => '')), array('tabindex' => '-1', 'aria-hidden' => 'true')); ?>
            </div>
            <?php endif; ?>
            <div class="item-body">
                <h3><?php echo link_to_item(metadata('item', array('Dublin Core', 'Title')), array('class' => 'permalink')); ?></h3>
                <?php if ($creator = metadata('item', array('Dublin Core', 'Creator'))): ?>
                <p class="item-creator"><?php echo $creator; ?></p>
                <?php endif; ?>
                <?php if ($description = metadata('item', array('Dublin Core', 'Description'), array('snippet' => 200))): ?>
                <div class="item-description"><?php echo $description; ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <p class="view-items-link"><?php echo link_to_items_browse(__('View all items')); ?></p>
</div>
<?php endif; ?>

<?php echo foot(); ?>
