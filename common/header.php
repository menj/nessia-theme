<!DOCTYPE html>
<html lang="<?php echo get_html_lang(); ?>" class="<?php echo nessia_colour_scheme(); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <?php if ($author = option('author')): ?>
    <meta name="author" content="<?php echo html_escape($author); ?>" />
    <?php endif; ?>
    <?php if ($copyright = option('copyright')): ?>
    <meta name="copyright" content="<?php echo html_escape($copyright); ?>" />
    <?php endif; ?>
    <?php echo nessia_seo_meta(isset($title) ? $title : null); ?>

    <title><?php echo isset($title) ? strip_formatting($title) . ' | ' : ''; echo strip_formatting(option('site_title')); ?></title>

    <?php echo auto_discovery_link_tags(); ?>

    <!-- Plugin Hooks -->
    <?php fire_plugin_hook('public_head', array('view' => $this)); ?>

    <!-- Stylesheets -->
    <?php
    $nessiaLightbox = (bool) nessia_option('image_lightbox');
    queue_css_file($nessiaLightbox ? array('normalize', 'lightbox.min', 'nessia') : array('normalize', 'nessia'));
    queue_css_url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Noto+Serif:ital,wght@0,400;0,700;1,400&family=Noto+Sans+Arabic:wght@300;400;500&family=Noto+Sans+Hebrew:wght@300;400;500&family=Noto+Sans+Syriac:wght@400&display=swap');
    echo head_css();
    ?>

    <!-- JavaScripts -->
    <?php
    $nessiaJs = array('globals', 'vendor/jquery-accessibleMegaMenu');
    if ($nessiaLightbox) {
        $nessiaJs[] = 'vendor/lightbox.min';
    }
    $nessiaJs[] = 'nessia';
    queue_js_file($nessiaJs);
    ?>
    <?php echo head_js(); ?>
</head>
<?php
    $nessiaBanner = nessia_page_banner(isset($title) ? $title : null, @$bodyid, @$bodyclass);
    echo body_tag(array('id' => @$bodyid, 'class' => trim(@$bodyclass . ($nessiaBanner ? ' has-banner' : ''))));
?>
    <a href="#content" id="skipnav"><?php echo __('Skip to Main Content'); ?></a>
    <?php fire_plugin_hook('public_body', array('view' => $this)); ?>

<div id="wrap" data-i18n-search="<?php echo html_escape(__('Search')); ?>" data-i18n-menu="<?php echo html_escape(__('Menu')); ?>" data-i18n-image="<?php echo html_escape(__('Image %1 of %2')); ?>">

    <?php
        $headerBg = nessia_header_bg();
        $headerStyle = $headerBg ? ' style="background-image:url(\'' . html_escape($headerBg) . '\');"' : '';
    ?>
    <header role="banner"<?php echo $headerStyle; ?>>
        <?php fire_plugin_hook('public_header', array('view' => $this)); ?>

        <div class="nessia-header-inner">
            <div class="nessia-brand">
                <div class="nessia-logo">
                    <?php echo link_to_home_page(nessia_logo()); ?>
                </div>

                <div class="nessia-titles">
                    <h1 id="site-title">
                        <?php echo link_to_home_page(html_escape(option('site_title'))); ?>
                    </h1>
                    <?php echo nessia_multilingual_names(); ?>
                    <?php echo nessia_display_tagline(); ?>
                </div>
            </div>

            <div id="search-container" role="search">
                <?php
                    $showAdvanced = (bool) nessia_option('use_advanced_search');
                    echo search_form(array(
                        'show_advanced'   => $showAdvanced,
                        'form_attributes' => array('class' => $showAdvanced ? 'with-advanced' : ''),
                    ));
                ?>
            </div>
        </div>
    </header>

    <nav id="primary-nav" role="navigation" aria-label="<?php echo __('Navigation'); ?>">
        <?php
        // Donate sits at the right end of the main menu (hidden until a link is set).
        $nessiaMenu = (string) public_nav_main();
        if ($nessiaDonate = nessia_donate_button('nav')) {
            $pos = strrpos($nessiaMenu, '</ul>');
            if ($pos !== false) {
                $nessiaMenu = substr_replace($nessiaMenu, '<li class="nessia-nav-donate">' . $nessiaDonate . '</li></ul>', $pos, 5);
            }
        }
        echo $nessiaMenu;
        ?>
    </nav>

    <?php echo $nessiaBanner; ?>

    <div id="content" role="main" tabindex="-1">
        <?php fire_plugin_hook('public_content_top', array('view' => $this)); ?>
