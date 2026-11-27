<?php
if (nessia_tools_requested()) {
    include dirname(__FILE__) . '/common/nessia-tools.php';
    return;
}
// Archive overview at ?overview, for sites without the Simple Pages plugin.
if (isset($_GET['overview'])) {
    echo head(array('title' => __('Archive overview'), 'bodyclass' => 'nessia-overview-page nessia-landing'));
    echo '<h1>' . __('Archive overview') . '</h1>';
    echo nessia_block_overview();
    echo foot();
    return;
}
?>
<?php echo head(array('bodyid' => 'home', 'bodyclass' => 'two-col nessia-landing')); ?>

<?php
$homeText = trim(strip_formatting((string) nessia_option('homepage_text')));
if ($homeText === '') {
    $homeText = trim(strip_formatting((string) option('description')));
}
echo nessia_block_hero(array(
    'kicker' => __('The archive'),
    'title'  => strip_formatting(option('site_title')),
    'text'   => $homeText,
    'button' => __('Explore the archive'),
    'link'   => nessia_overview_url(),
));
?>

<?php echo nessia_block_stats(); ?>

<?php echo nessia_explore_topics(); ?>

<?php echo nessia_block_featured(3); ?>

<?php fire_plugin_hook('public_home', array('view' => $this)); ?>


<?php
$recentCount = (int) nessia_option('homepage_recent_items');
if ($recentCount < 1) {
    $recentCount = 8;
}
$recentItems = get_recent_items($recentCount);
?>
<?php echo nessia_block_items($recentItems, __('Recently added'), url('items/browse'), __('Browse all items')); ?>

<?php echo foot(); ?>
