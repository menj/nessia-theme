<?php echo head(array(
    'title' => metadata('Item', array('Dublin Core', 'Title')),
    'bodyclass' => 'items show'
)); ?>

<h1><?php echo metadata('Item', 'rich_title', array('no_escape' => true)); ?></h1>

<?php
$nessiaFiles = $item->getFiles();
$nessiaMedia = nessia_item_media_type($nessiaFiles);
?>
<?php if ($nessiaFiles): ?>
<div id="itemfiles" class="element nessia-media-<?php echo $nessiaMedia; ?>">
    <?php if ($nessiaMedia === 'audio'): ?>
    <div class="nessia-audio-head">
        <span class="nessia-audio-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
        </span>
        <div class="nessia-audio-meta">
            <span class="nessia-audio-label"><?php echo __('Audio recording'); ?></span>
            <?php if ($creator = metadata('item', array('Dublin Core', 'Creator'))): ?>
            <span class="nessia-audio-creator"><?php echo $creator; ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <div class="element-text"><?php echo files_for_item(); ?></div>
    <?php if (in_array($nessiaMedia, array('audio', 'video'), true)): ?>
    <ul class="nessia-file-downloads">
        <?php foreach ($nessiaFiles as $file): ?>
        <li><a href="<?php echo html_escape($file->getWebPath('original')); ?>" download><?php echo __('Download'); ?> <span class="nessia-file-name"><?php echo html_escape($file->original_filename); ?></span></a></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
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
