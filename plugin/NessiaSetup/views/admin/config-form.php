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

<h2><?php echo __('Readable addresses'); ?></h2>

<div class="field">
    <div class="two columns alpha"><label><?php echo __('How it works'); ?></label></div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('The Clean Url plugin builds addresses such as /comparative-religion/al-tawhid from each record\'s Dublin Core Identifier. This step fills in a readable Identifier, made from the title, for every collection and item that has none. Records that already have an Identifier are left alone.'); ?></p>
    </div>
</div>

<div class="field">
    <div class="two columns alpha"><label><?php echo __('Preview'); ?></label></div>
    <div class="inputs five columns omega">
        <?php if (empty($slugPreview['rows'])): ?>
        <p><?php echo __('Every collection and item already has an Identifier.'); ?></p>
        <?php else: ?>
        <p><?php echo __('%1$s record(s) would get an Identifier; %2$s already have one.', count($slugPreview['rows']), (int) $slugPreview['skipped']); ?></p>
        <table>
            <thead><tr><th><?php echo __('Record'); ?></th><th><?php echo __('Title'); ?></th><th><?php echo __('Identifier'); ?></th></tr></thead>
            <tbody>
            <?php foreach (array_slice($slugPreview['rows'], 0, 100) as $row): ?>
                <tr>
                    <td><?php echo html_escape(__($row['type']) . ' #' . $row['id']); ?></td>
                    <td><?php echo html_escape($row['title']); ?></td>
                    <td><code><?php echo html_escape($row['slug']); ?></code></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (count($slugPreview['rows']) > 100): ?>
        <p class="explanation"><?php echo __('…and %s more.', count($slugPreview['rows']) - 100); ?></p>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha"><label for="nessia_setup_slugs"><?php echo __('Write Identifiers'); ?></label></div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('Writes the Identifiers listed above. Back up the database first.'); ?></p>
        <?php echo get_view()->formCheckbox('nessia_setup_slugs', '1', array('checked' => false)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha"><label for="nessia_setup_auto_slug"><?php echo __('Name new records automatically'); ?></label></div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('When a collection or item is saved without an Identifier, give it one from its title.'); ?></p>
        <?php echo get_view()->formCheckbox('nessia_setup_auto_slug', '1', array('checked' => get_option('nessia_setup_auto_slug') === '1')); ?>
    </div>
</div>

<?php if ($slugReport && ($sr = json_decode($slugReport, true))): ?>
<div class="field">
    <div class="two columns alpha"><label><?php echo __('Last run'); ?></label></div>
    <div class="inputs five columns omega"><p><?php echo html_escape($sr['time']); ?>: <?php echo __('%s Identifier(s) written.', (int) $sr['count']); ?></p></div>
</div>
<?php endif; ?>

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
