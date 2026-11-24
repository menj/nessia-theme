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

<h2><?php echo __('Reorganise'); ?></h2>

<div class="field">
    <div class="two columns alpha"><label><?php echo __('Current categories'); ?></label></div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Use these exact names on the left side of the migrate mapping in categories.php.'); ?></p>
        <p><strong><?php echo __('Collections'); ?></strong></p>
        <ul>
        <?php foreach ($inventory['collections'] as $row): ?>
            <li><?php echo html_escape($row['name'] !== null ? $row['name'] : '#' . $row['id']); ?> — <?php echo (int) $row['items']; ?></li>
        <?php endforeach; ?>
        </ul>
        <p><strong><?php echo __('Tags'); ?></strong></p>
        <ul>
        <?php foreach ($inventory['tags'] as $row): ?>
            <li><?php echo html_escape($row['name']); ?> — <?php echo (int) $row['items']; ?></li>
        <?php endforeach; ?>
        </ul>
        <p><strong><?php echo __('Subjects'); ?></strong></p>
        <ul>
        <?php foreach ($inventory['subjects'] as $row): ?>
            <li><?php echo html_escape($row['name']); ?> — <?php echo (int) $row['items']; ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
</div>

<div class="field">
    <div class="two columns alpha"><label><?php echo __('Preview'); ?></label></div>
    <div class="inputs five columns omega">
        <ul>
        <?php foreach ($preview as $line): ?>
            <li><?php echo html_escape($line); ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="nessia_setup_migrate"><?php echo __('Apply reorganisation'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation" style="color:#a00;"><strong><?php echo __('Back up the database first. This cannot be undone.'); ?></strong>
        <?php echo __('Runs exactly the preview above.'); ?></p>
        <?php echo get_view()->formCheckbox('nessia_setup_migrate', '1', array('checked' => false)); ?>
    </div>
</div>

<?php if ($migrationReport && ($m = json_decode($migrationReport, true))): ?>
<div class="field">
    <div class="two columns alpha"><label><?php echo __('Last reorganisation'); ?></label></div>
    <div class="inputs five columns omega">
        <p><?php echo html_escape($m['time']); ?></p>
        <ul>
        <?php foreach ($m['lines'] as $line): ?>
            <li><?php echo html_escape($line); ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>
