        </div><!-- end #content -->

        <footer role="contentinfo">
            <div class="nessia-footer-inner">

                <nav id="bottom-nav" aria-label="<?php echo __('Footer Navigation'); ?>">
                    <?php echo public_nav_main(); ?>
                </nav>

                <?php echo nessia_social_links(); ?>

                <div class="nessia-footer-meta">
                    <div id="footer-text">
                        <?php
                        // footer_text is configured as an HTML-enabled field (html-input) in config.ini.
                        // Output as-is (admin-controlled).
                        $footerText = nessia_option('footer_text');
                        if ($footerText) {
                            echo $footerText;
                        }

                        if (nessia_option('display_footer_copyright') && ($copyright = option('copyright'))) {
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

</body>
</html>
