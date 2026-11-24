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
    <?php if ($description = option('description')): ?>
    <meta name="description" content="<?php echo html_escape($description); ?>" />
    <?php endif; ?>

    <title><?php echo strip_formatting(option('site_title')); echo isset($title) ? ' | ' . strip_formatting($title) : ''; ?></title>

    <?php echo auto_discovery_link_tags(); ?>

    <!-- Plugin Hooks -->
    <?php fire_plugin_hook('public_head', array('view' => $this)); ?>

    <!-- Stylesheets -->
    <?php
    queue_css_file(array('normalize', 'nessia'));
    queue_css_url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Noto+Serif:ital,wght@0,400;0,700;1,400&family=Noto+Sans+Arabic:wght@300;400;500&family=Noto+Sans+Hebrew:wght@300;400;500&family=Noto+Sans+Syriac:wght@400&display=swap');
    echo head_css();
    ?>

    <!-- JavaScripts -->
    <?php queue_js_file(array('globals', 'vendor/jquery-accessibleMegaMenu', 'nessia')); ?>
    <?php echo head_js(); ?>
</head>
<?php echo body_tag(array('id' => @$bodyid, 'class' => @$bodyclass)); ?>
    <a href="#content" id="skipnav"><?php echo __('Skip to Main Content'); ?></a>
    <?php fire_plugin_hook('public_body', array('view' => $this)); ?>

<div id="wrap">

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
                    $formClass = ($showAdvanced) ? 'with-advanced' : '';
                    $searchFormHtml = search_form(array(
                        'show_advanced' => $showAdvanced,
                        'form_attributes' => array('class' => $formClass)
                    ));

                    $searchLabel = __('Search');
                    $advLabel    = __('Advanced search');

                    // --- Accessible submit label ---
                    // 1) <input type="submit">: ensure value + aria-label.
                    $searchFormHtml = preg_replace(
                        '/(<input\s+[^>]*type=["\']submit["\'][^>]*)(?:\s+value=["\'][^"\']*["\'])?([^>]*>)/i',
                        '$1 value="' . html_escape($searchLabel) . '" aria-label="' . html_escape($searchLabel) . '"$2',
                        $searchFormHtml,
                        1
                    );

                    // 2) <button type="submit"></button>: inject text + aria-label.
                    $searchFormHtml = preg_replace(
                        '/(<button\s+[^>]*type=["\']submit["\'])([^>]*>)(\s*?)<\/button>/i',
                        '$1 aria-label="' . html_escape($searchLabel) . '"$2' . html_escape($searchLabel) . '</button>',
                        $searchFormHtml,
                        1
                    );

                    // --- Replace "Advanced Search" link with an accessible toggle button ---
                    // Omeka typically outputs: <div class="advanced"><a href="...">Advanced Search</a></div>
                    // We replace the <a> with a <button> that has proper ARIA attributes.
                    $searchFormHtml = preg_replace(
                        '/<div\s+class=["\']advanced["\'][^>]*>\s*<a\b[^>]*>[^<]*<\/a>\s*<\/div>/i',
                        '<button type="button" class="advanced-toggle" id="advanced-toggle"'
                        . ' aria-controls="advanced-form" aria-expanded="false">'
                        . html_escape($advLabel) . '</button>',
                        $searchFormHtml,
                        1
                    );

                    // --- Add accessibility attributes to #advanced-form ---
                    // Set role, aria-labelledby, and hidden (default closed).
                    $searchFormHtml = preg_replace(
                        '/(<(?:div|fieldset)\s+[^>]*id=["\']advanced-form["\'])([^>]*>)/i',
                        '$1 role="group" aria-labelledby="advanced-toggle" hidden$2',
                        $searchFormHtml,
                        1
                    );

                    echo $searchFormHtml;
                ?>
            </div>
        </div>
    </header>

    <nav id="primary-nav" role="navigation" aria-label="<?php echo __('Navigation'); ?>">
        <?php echo public_nav_main(); ?>
    </nav>

    <div id="content" role="main" tabindex="-1">
        <?php fire_plugin_hook('public_content_top', array('view' => $this)); ?>
