<?php
/**
 * NESSIA — private tools page (?nessia-tools on the homepage, super users only).
 * Replaces the old NessiaSetup plugin's Configure screen.
 */
$result = nessia_tools_handle_post();
$setup = nessia_setup();
$inventory = $setup->inventory();
$preview = $setup->migrate(true);
// Preview lists the first 100; 'missing' still counts them all.
$slugPreview = $setup->slugs(true, 100);
$token = nessia_tools_token();
$categories = nessia_categories();

echo head(array('title' => __('Archive tools'), 'bodyclass' => 'nessia-tools-page'));
?>
<h1><?php echo __('Archive tools'); ?></h1>

<p class="nessia-tools-lead"><?php echo __('Only super users can see this page. Back up the database before using the buttons marked with a warning.'); ?></p>

<?php if ($result): ?>
<div class="nessia-tools-result<?php echo $result['action'] === 'error' ? ' is-error' : ''; ?>" role="status">
    <ul><?php foreach ($result['lines'] as $line): ?><li><?php echo html_escape($line); ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<nav class="nessia-tools-tabs" aria-label="<?php echo __('Tools'); ?>">
    <a href="#tool-categories"><?php echo __('Categories'); ?></a>
    <a href="#tool-addresses"><?php echo __('Readable addresses'); ?></a>
    <a href="#tool-reorganise"><?php echo __('Reorganise'); ?></a>
</nav>

<section id="tool-categories" class="nessia-tool">
    <h2><?php echo __('Categories'); ?></h2>
    <p><?php echo __('From the theme file data/categories.php. Created automatically when "Create Default Categories" is on; this button runs it now. Nothing is deleted.'); ?></p>
    <ul class="nessia-tool-list">
    <?php foreach ($categories['collections'] as $c): ?>
        <li><strong><?php echo html_escape($c['title']); ?></strong>
        <?php if (!empty($c['children'])): ?>: <?php echo html_escape(implode(', ', array_map(function ($x) { return $x['title']; }, $c['children']))); ?><?php endif; ?>
        </li>
    <?php endforeach; ?>
    </ul>
    <form method="post">
        <input type="hidden" name="nessia_token" value="<?php echo html_escape($token); ?>">
        <button type="submit" name="nessia_action" value="seed"><?php echo __('Create missing categories'); ?></button>
    </form>
</section>

<section id="tool-addresses" class="nessia-tool">
    <h2><?php echo __('Readable addresses'); ?></h2>
    <p><?php echo __('Dublin Core Identifiers that the Clean Url plugin turns into addresses. With "Readable Addresses" on, they are filled in automatically, 25 records every few minutes.'); ?></p>
    <?php if (empty($slugPreview['rows'])): ?>
        <p class="nessia-tool-ok"><?php echo __('Every collection and item has an Identifier.'); ?></p>
    <?php else: ?>
        <p><?php echo __('%1$s record(s) still need an Identifier; %2$s already have one.', (int) $slugPreview['missing'], (int) $slugPreview['skipped']); ?></p>
        <div class="nessia-table-wrap">
        <table class="nessia-table">
            <thead><tr><th><?php echo __('Record'); ?></th><th><?php echo __('Title'); ?></th><th><?php echo __('Identifier'); ?></th></tr></thead>
            <tbody>
            <?php foreach ($slugPreview['rows'] as $row): ?>
                <tr><td><?php echo html_escape(__($row['type']) . ' #' . $row['id']); ?></td><td><?php echo html_escape($row['title']); ?></td><td><code><?php echo html_escape($row['slug']); ?></code></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if ($slugPreview['missing'] > count($slugPreview['rows'])): ?>
        <p class="nessia-tools-lead"><?php echo __('Showing the first %1$s of %2$s.', count($slugPreview['rows']), (int) $slugPreview['missing']); ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="nessia_token" value="<?php echo html_escape($token); ?>">
            <button type="submit" name="nessia_action" value="identifiers"><?php echo __('Write all Identifiers now'); ?></button>
        </form>
    <?php endif; ?>
</section>

<section id="tool-reorganise" class="nessia-tool">
    <h2><?php echo __('Reorganise'); ?></h2>
    <p><?php echo __('Map old names to new ones in the "migrate" section of data/categories.php, then check the preview here.'); ?></p>

    <div class="nessia-tool-cols">
        <div>
            <h3><?php echo __('Collections'); ?></h3>
            <ul class="nessia-tool-list"><?php foreach ($inventory['collections'] as $r): ?><li><?php echo html_escape($r['name'] !== null ? $r['name'] : '#' . $r['id']); ?> <span>(<?php echo (int) $r['items']; ?>)</span></li><?php endforeach; ?></ul>
        </div>
        <div>
            <h3><?php echo __('Tags'); ?></h3>
            <ul class="nessia-tool-list"><?php foreach ($inventory['tags'] as $r): ?><li><?php echo html_escape($r['name']); ?> <span>(<?php echo (int) $r['items']; ?>)</span></li><?php endforeach; ?></ul>
        </div>
        <div>
            <h3><?php echo __('Subjects'); ?></h3>
            <ul class="nessia-tool-list"><?php foreach ($inventory['subjects'] as $r): ?><li><?php echo html_escape($r['name']); ?> <span>(<?php echo (int) $r['items']; ?>)</span></li><?php endforeach; ?></ul>
        </div>
    </div>

    <h3><?php echo __('Preview'); ?></h3>
    <ul class="nessia-tool-list"><?php foreach ($preview as $line): ?><li><?php echo html_escape($line); ?></li><?php endforeach; ?></ul>

    <form method="post" onsubmit="return confirm('<?php echo html_escape(__('Apply the reorganisation? This cannot be undone.')); ?>');">
        <input type="hidden" name="nessia_token" value="<?php echo html_escape($token); ?>">
        <p class="nessia-tool-warn"><?php echo __('Back up the database first. This cannot be undone.'); ?></p>
        <button type="submit" name="nessia_action" value="migrate" class="nessia-tool-danger"><?php echo __('Apply reorganisation'); ?></button>
    </form>
</section>

<?php echo foot(); ?>
