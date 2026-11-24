<?php
$pageTitle = __('Browse Items');
echo head(array('title' => $pageTitle, 'bodyclass' => 'items browse'));
$view = nessia_browse_view();
?>

<h1><?php echo $pageTitle; ?> <span class="nessia-count"><?php echo __('(%s total)', $total_results); ?></span></h1>

<nav class="items-nav navigation secondary-nav" aria-label="<?php echo __('Items'); ?>">
    <?php echo public_nav_items(); ?>
</nav>

<?php echo nessia_type_pills(); ?>

<?php echo item_search_filters(); ?>

<?php if ($total_results > 0): ?>
<div class="nessia-browse-bar">
    <div id="sort-links">
        <span class="sort-label"><?php echo __('Sort by: '); ?></span>
        <?php echo browse_sort_links(array(
            __('Title')      => 'Dublin Core,Title',
            __('Creator')    => 'Dublin Core,Creator',
            __('Date Added') => 'added',
        )); ?>
    </div>
    <?php echo nessia_view_switcher(); ?>
</div>
<?php endif; ?>

<?php echo pagination_links(); ?>

<?php if ($view === 'table'): ?>

<div class="nessia-table-wrap">
    <table class="nessia-table">
        <thead>
            <tr>
                <th scope="col"><?php echo __('Title'); ?></th>
                <th scope="col"><?php echo __('Creator'); ?></th>
                <th scope="col"><?php echo __('Type'); ?></th>
                <th scope="col" class="nessia-col-date"><?php echo __('Date Added'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach (loop('items') as $item): ?>
            <tr>
                <td><?php echo link_to_item(metadata('item', array('Dublin Core', 'Title'))); ?></td>
                <td><?php echo metadata('item', array('Dublin Core', 'Creator')); ?></td>
                <td><?php echo metadata('item', 'Item Type Name'); ?></td>
                <td class="nessia-col-date"><?php echo nessia_display_date_added(); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php elseif ($view === 'grid'): ?>

<div class="nessia-shelf">
    <?php foreach (loop('items') as $item): ?>
    <?php $hasThumb = metadata('item', 'has thumbnail'); ?>
    <article class="nessia-shelf-item hentry">
        <?php
        if ($hasThumb) {
            $cover = item_image('thumbnail', array('alt' => '', 'loading' => 'lazy'));
        } else {
            $typeName = metadata('item', 'Item Type Name');
            $cover = '<span class="nessia-cover-placeholder"><span>' . html_escape($typeName ? $typeName : __('Item')) . '</span></span>';
        }
        echo link_to_item($cover, array('class' => 'nessia-cover', 'tabindex' => '-1', 'aria-hidden' => 'true'));
        ?>
        <h2><?php echo link_to_item(metadata('item', array('Dublin Core', 'Title'))); ?></h2>
        <?php if ($creator = metadata('item', array('Dublin Core', 'Creator'))): ?>
        <p class="item-creator"><?php echo $creator; ?></p>
        <?php endif; ?>
        <?php fire_plugin_hook('public_items_browse_each', array('view' => $this, 'item' => $item)); ?>
    </article>
    <?php endforeach; ?>
</div>

<?php else: ?>

<?php if ($featuredHtml = random_featured_items(1)): ?>
<div id="featured-item">
    <h2><?php echo __('Featured Item'); ?></h2>
    <?php echo $featuredHtml; ?>
</div>
<?php endif; ?>

<div class="items-list">
    <?php foreach (loop('items') as $item): ?>
    <?php $hasThumb = metadata('item', 'has thumbnail'); ?>
    <div class="item hentry<?php echo $hasThumb ? ' has-thumb' : ''; ?>">
        <?php if ($hasThumb): ?>
        <div class="item-img">
            <?php echo link_to_item(item_image('thumbnail', array('alt' => '')), array('tabindex' => '-1', 'aria-hidden' => 'true')); ?>
        </div>
        <?php endif; ?>

        <div class="item-body">
        <h2><?php echo link_to_item(metadata('item', array('Dublin Core', 'Title')), array('class' => 'permalink')); ?></h2>

        <?php if ($creator = metadata('item', array('Dublin Core', 'Creator'))): ?>
        <p class="item-creator"><?php echo $creator; ?></p>
        <?php endif; ?>

        <?php if ($description = metadata('item', array('Dublin Core', 'Description'), array('snippet' => 250))): ?>
        <div class="item-description">
            <?php echo $description; ?>
        </div>
        <?php endif; ?>

        <?php if (metadata('item', 'has tags')): ?>
        <div class="tags">
            <?php echo tag_string('items', 'items/browse', ' '); ?>
        </div>
        <?php endif; ?>

        <?php fire_plugin_hook('public_items_browse_each', array('view' => $this, 'item' => $item)); ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<?php echo pagination_links(); ?>

<div id="outputs">
    <span class="outputs-label"><?php echo __('Output Formats'); ?></span>
    <?php echo output_format_list(false); ?>
</div>

<?php fire_plugin_hook('public_items_browse', array('items' => $items, 'view' => $this)); ?>

<?php echo foot(); ?>
