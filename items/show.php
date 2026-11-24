<?php echo head(array(
    'title' => metadata('Item', array('Dublin Core', 'Title')),
    'bodyclass' => 'items show'
)); ?>

<h1><?php echo metadata('Item', 'rich_title', array('no_escape' => true)); ?></h1>

<?php if (metadata('item', 'has files')): ?>
<div id="itemfiles" class="element">
    <div class="element-text"><?php echo files_for_item(); ?></div>
</div>
<?php endif; ?>

<div class="item hentry">
    <div class="item-meta">
        <?php echo all_element_texts('item'); ?>

        <?php echo fire_plugin_hook('public_items_show', array('view' => $this, 'item' => $item)); ?>
    </div>
</div>

<div id="show-sidebar">
    <dl>
        <dt><?php echo __('Date Added'); ?></dt>
        <dd><?php echo nessia_display_date_added(); ?></dd>

        <?php if (metadata('item', 'Collection Name')): ?>
        <dt><?php echo __('Collection'); ?></dt>
        <dd><?php echo link_to_collection_for_item(); ?></dd>
        <?php endif; ?>

        <?php if (metadata('item', 'Item Type Name')): ?>
        <dt><?php echo __('Item Type'); ?></dt>
        <dd><?php echo metadata('item', 'Item Type Name'); ?></dd>
        <?php endif; ?>

        <?php if (metadata('item', 'has tags')): ?>
        <dt><?php echo __('Tags'); ?></dt>
        <dd><?php echo tag_string('item', 'items/browse', ' '); ?></dd>
        <?php endif; ?>

        <dt><?php echo __('Citation'); ?></dt>
        <dd><?php echo metadata('item', 'citation', array('no_escape' => true)); ?></dd>
    </dl>
</div>

<ul class="item-pagination navigation">
    <li id="previous-item" class="previous">
        <?php echo link_to_previous_item_show(); ?>
    </li>
    <li id="next-item" class="next">
        <?php echo link_to_next_item_show(); ?>
    </li>
</ul>

<?php echo foot(); ?>
