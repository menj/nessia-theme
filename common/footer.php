        </div><!-- end #content -->

        <?php echo nessia_mission_band(); ?>

        <footer role="contentinfo">
            <div class="nessia-footer-inner">

                <div class="nessia-footer-cols">
                    <div class="nessia-footer-brand">
                        <a class="nessia-footer-logo" href="<?php echo html_escape(url('/')); ?>">
                            <?php echo nessia_logo(); ?>
                            <span><?php echo html_escape(option('site_title')); ?></span>
                        </a>
                        <?php if ($siteDescription = option('description')): ?>
                        <p><?php echo html_escape($siteDescription); ?></p>
                        <?php else: ?>
                        <p><?php echo html_escape(__('Exploring Semitic Religion, Language and Heritage')); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if ($nessiaExplore = nessia_footer_explore()): ?>
                    <nav class="nessia-footer-col" aria-label="<?php echo __('Explore'); ?>">
                        <h2><?php echo __('Explore'); ?></h2>
                        <ul><?php echo $nessiaExplore; ?></ul>
                    </nav>
                    <?php endif; ?>

                    <nav id="bottom-nav" class="nessia-footer-col" aria-label="<?php echo __('Footer Navigation'); ?>">
                        <h2><?php echo __('Archive'); ?></h2>
                        <?php echo public_nav_main()->setMaxDepth(0); ?>
                    </nav>

                    <div class="nessia-footer-col nessia-footer-connect">
                        <h2><?php echo __('Connect'); ?></h2>
                        <?php echo nessia_social_links(); ?>
                        <div id="footer-text">
                            <?php
                            // footer_text is an HTML-enabled, admin-controlled field.
                            if ($footerText = nessia_option('footer_text')) {
                                echo $footerText;
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="nessia-footer-bottom">
                    <?php if (nessia_option('display_footer_copyright') && ($copyright = option('copyright'))): ?>
                    <p class="nessia-copyright"><?php echo html_escape($copyright); ?></p>
                    <?php else: ?>
                    <p class="nessia-copyright">&copy; <?php echo date('Y'); ?> <?php echo html_escape(option('site_title')); ?></p>
                    <?php endif; ?>
                    <p class="nessia-footer-credits"><?php echo __('Powered by <a href="http://omeka.org">Omeka</a>'); ?></p>
                </div>

            </div>
        </footer>

    </div><!-- end #wrap -->

</body>
</html>
