        </div><!-- end #content -->

        <footer role="contentinfo">
            <div class="nessia-footer-inner">

                <nav id="bottom-nav" aria-label="<?php echo __('Footer Navigation'); ?>">
                    <?php echo public_nav_main(); ?>
                </nav>

                <div class="nessia-footer-meta">
                    <div id="footer-text">
                        <?php
                        // footer_text is configured as an HTML-enabled field (html-input) in config.ini.
                        // Output as-is (admin-controlled).
                        $footerText = get_theme_option('footer_text');
                        if ($footerText) {
                            echo $footerText;
                        }

                        $showCopyright = get_theme_option('display_footer_copyright');
                        if ($showCopyright !== '0' && ($copyright = option('copyright'))) {
                            echo '<p class="nessia-copyright">' . html_escape($copyright) . '</p>';
                        }
                        ?>
                    </div>

                    <div class="nessia-footer-credits">
                        <p><?php echo __('Powered by <a href="http://omeka.org">Omeka</a>'); ?> · <?php echo __('Theme by <a href="https://nessia.org">NESSIA</a>'); ?></p>
                    </div>
                </div>

            </div>
        </footer>

    </div><!-- end #wrap -->

    <script>
    jQuery(document).ready(function() {
        Omeka.showAdvancedForm();
        Omeka.skipNav();
        Omeka.megaMenu();
        NessiaTheme.init();
    });
    </script>

</body>
</html>
