<?php
$counts = null;
if ($report && ($decoded = json_decode($report, true))) {
    $counts = $decoded;
}
?>
<div class="field">
    <div class="two columns alpha">
        <label for="nessia_setup_run"><?php echo __('Run setup again'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Creates any missing collections, subject terms and tags from categories.php. Existing entries are left untouched.'); ?></p>
        <?php echo get_view()->formCheckbox('nessia_setup_run', '1', array('checked' => false)); ?>
    </div>
</div>

<?php if ($counts): ?>
<div class="field">
    <div class="two columns alpha"><label><?php echo __('Last run'); ?></label></div>
    <div class="inputs five columns omega">
        <p><?php echo html_escape($counts['time']); ?></p>
        <ul>
            <li><?php echo __('Collections created: %s', (int) $counts['counts']['collections']); ?></li>
            <li><?php echo __('Collections nested (Collection Tree): %s', (int) $counts['counts']['nested']); ?></li>
            <li><?php echo __('Subject terms added (Simple Vocab): %s', (int) $counts['counts']['subjects']); ?></li>
            <li><?php echo __('Tags created: %s', (int) $counts['counts']['tags']); ?></li>
        </ul>
    </div>
</div>
<?php endif; ?>

<div class="field">
    <div class="two columns alpha"><label><?php echo __('Categories'); ?></label></div>
    <div class="inputs five columns omega">
        <ul>
        <?php foreach ($categories['collections'] as $c): ?>
            <li><strong><?php echo html_escape($c['title']); ?></strong>
            <?php if (!empty($c['children'])): ?>
                — <?php echo html_escape(implode(', ', array_map(function ($x) { return $x['title']; }, $c['children']))); ?>
            <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
        <p class="explanation"><?php echo __('Subjects: %s', html_escape(implode(', ', $categories['subjects']))); ?></p>
        <p class="explanation"><?php echo __('Tags: %s', html_escape(implode(', ', $categories['tags']))); ?></p>
    </div>
</div>
