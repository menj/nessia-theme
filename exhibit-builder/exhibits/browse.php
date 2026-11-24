<?php
$title = __('Browse Exhibits');
echo head(array('title' => $title, 'bodyclass' => 'exhibits browse'));

$exhibitTotal = isset($total_results) ? (int) $total_results : count($exhibits);
?>

<h1><?php echo $title; ?> <span class="nessia-count"><?php echo __('(%s total)', $exhibitTotal); ?></span></h1>

<nav class="navigation secondary-nav" aria-label="<?php echo __('Exhibits'); ?>">
    <?php echo nav(array(
        array('label' => __('Browse All'), 'uri' => url('exhibits')),
        array('label' => __('Browse by Tag'), 'uri' => url('exhibits/tags')),
    )); ?>
</nav>

<?php if ($exhibitTotal > 0): ?>

<div id="sort-links">
    <span class="sort-label"><?php echo __('Sort by: '); ?></span>
    <?php echo browse_sort_links(array(
        __('Title')      => 'title',
        __('Date Added') => 'added',
    )); ?>
</div>

<?php echo pagination_links(); ?>

<div class="items-list exhibits-list">
    <?php foreach (loop('exhibit') as $exhibit): ?>
    <?php $exhibitImage = record_image($exhibit, 'thumbnail', array('alt' => '')); ?>
    <div class="item exhibit<?php echo $exhibitImage ? ' has-thumb' : ''; ?><?php echo $exhibit->featured ? ' is-featured' : ''; ?>">
        <?php if ($exhibitImage): ?>
        <div class="item-img">
            <?php echo exhibit_builder_link_to_exhibit($exhibit, $exhibitImage, array('tabindex' => '-1', 'aria-hidden' => 'true')); ?>
        </div>
        <?php endif; ?>

        <div class="item-body">
            <?php if ($exhibit->featured): ?>
            <span class="nessia-badge"><?php echo __('Featured'); ?></span>
            <?php endif; ?>

            <h2><?php echo exhibit_builder_link_to_exhibit($exhibit); ?></h2>

            <?php if ($exhibitDescription = metadata('exhibit', 'description', array('no_escape' => true))): ?>
            <div class="item-description"><?php echo $exhibitDescription; ?></div>
            <?php endif; ?>

            <?php if ($exhibitTags = tag_string('exhibit', 'exhibits', ' ')): ?>
            <div class="tags"><?php echo $exhibitTags; ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php echo pagination_links(); ?>

<?php else: ?>
<p class="nessia-empty"><?php echo __('There are no exhibits available yet.'); ?></p>
<?php endif; ?>

<?php echo foot(); ?>
