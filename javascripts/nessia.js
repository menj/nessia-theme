/**
 * NESSIA Theme — Main JavaScript
 *
 * Near Eastern and Semitic Information Archive
 * https://nessia.org
 *
 * @package NESSIA
 * @version 1.3.1
 */

var NessiaTheme = (function ($) {
    'use strict';

    /**
     * Initialise all theme components.
     */
    var i18n = { search: 'Search', menu: 'Menu', image: 'Image %1 of %2' };

    function _loadStrings() {
        var wrap = document.getElementById('wrap');
        if (!wrap) return;
        i18n.search = wrap.getAttribute('data-i18n-search') || i18n.search;
        i18n.menu = wrap.getAttribute('data-i18n-menu') || i18n.menu;
        i18n.image = wrap.getAttribute('data-i18n-image') || i18n.image;
    }

    function _prefersReducedMotion() {
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function init() {
        _loadStrings();
        _setupMobileNav();
        _setupSmoothScroll();
        _setupSearchToggle();
        _expandFileViewers();
        _setupHeaderShrink();
        _setupAdvancedSearch();
        _setupLightbox();
    }

    /**
     * Mobile navigation toggle.
     */
    function _setupMobileNav() {
        var $nav = $('#primary-nav');
        if (!$nav.length) {
            return;
        }
        var $toggle = $('<button type="button" class="nessia-nav-toggle" aria-controls="primary-nav" aria-expanded="false">' +
            '<span class="nessia-hamburger" aria-hidden="true"></span>' +
            '</button>');
        $toggle.attr('aria-label', i18n.menu);

        $nav.before($toggle);

        $toggle.on('click', function () {
            var expanded = $(this).attr('aria-expanded') === 'true';
            $(this).attr('aria-expanded', String(!expanded));
            $nav.toggleClass('nessia-nav-open');
            $(this).toggleClass('nessia-nav-toggle-active');
        });
    }

    /**
     * Smooth scroll for anchor links.
     */
    function _setupSmoothScroll() {
        $('a[href^="#"]').not('#skipnav').on('click', function (e) {
            var href = this.getAttribute('href');
            if (href.length < 2) return;
            var target;
            try { target = $(href); } catch (err) { return; }
            if (!target.length) return;
            e.preventDefault();
            var headerH = $('header[role="banner"]').outerHeight() || 0;
            var navH = $('#primary-nav').outerHeight() || 0;
            var top = target.offset().top - (headerH + navH) - 16;
            if (_prefersReducedMotion()) {
                window.scrollTo(0, top);
            } else {
                $('html, body').animate({ scrollTop: top }, 400);
            }
        });
    }

    /**
     * Search bar focus/blur effects.
     */
    function _setupSearchToggle() {
        var $searchInput = $('#search-container input[type="text"]');
        if (!$searchInput.length) {
            return;
        }
        $searchInput.on('focus', function () {
            $(this).closest('#search-container').addClass('nessia-search-focused');
        }).on('blur', function () {
            $(this).closest('#search-container').removeClass('nessia-search-focused');
        });
    }

    /**
     * Header shrink on scroll.
     */
    
    /**
     * Force file viewers (iframe, embed, object) in #itemfiles to fill their
     * container. Omeka plugins often set inline width/height attributes that
     * prevent CSS from taking effect.
     */
    function _expandFileViewers() {
        var $container = $('#itemfiles');
        if (!$container.length) return;

        if (!$container.is('.nessia-media-document, .nessia-media-mixed')) return;

        $container.find('iframe, embed, object').each(function () {
            this.removeAttribute('width');
            this.removeAttribute('height');
            this.style.width = '100%';
            this.style.minHeight = '80vh';
        });
    }

    function _setupHeaderShrink() {
        var $header = $('header[role="banner"]');
        if (!$header.length) {
            return;
        }
        var scrollThreshold = 60;

        var ticking = false;

        function update() {
            $header.toggleClass('nessia-header-compact', window.pageYOffset > scrollThreshold);
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                ticking = true;
                window.requestAnimationFrame(update);
            }
        }, { passive: true });
        update();
    }

    // Advanced Search panel toggle — accessible, uses HTML hidden attribute.
    function _setupAdvancedSearch() {
        var container = document.getElementById('search-container');
        if (!container) return;

        var panel = document.getElementById('advanced-form');
        // Accept our PHP-injected button, or fall back to Omeka's link variant.
        var toggle = document.getElementById('advanced-toggle');

        if (!panel || !toggle) return;

        // Ensure correct ARIA on the toggle regardless of source element.
        toggle.setAttribute('aria-controls', 'advanced-form');
        toggle.setAttribute('aria-expanded', 'false');

        // Ensure panel starts hidden.
        panel.hidden = true;

        function focusFirst() {
            var focusable = panel.querySelector(
                'input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled])'
            );
            if (focusable) focusable.focus();
        }

        function openPanel() {
            panel.hidden = false;
            toggle.setAttribute('aria-expanded', 'true');
            focusFirst();
        }

        function closePanel(restoreFocus) {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
            if (restoreFocus !== false) toggle.focus();
        }

        // Toggle on click.
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            if (panel.hidden) {
                openPanel();
            } else {
                closePanel();
            }
        });

        // Close on outside click.
        document.addEventListener('click', function (e) {
            if (panel.hidden) return;
            if (container.contains(e.target)) return;
            closePanel(false);
        });

        // Close on Escape.
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !panel.hidden) {
                closePanel();
            }
        });
    }

    /**
     * Open item images in Lightbox2 (if loaded). Omeka links each file
     * thumbnail to its file page or original; we point image links at the
     * full-size original and group them into one gallery per page.
     */
    function _setupLightbox() {
        if (!window.lightbox || typeof window.lightbox.option !== 'function') return;

        window.lightbox.option({
            wrapAround: true,
            albumLabel: i18n.image,
            fadeDuration: _prefersReducedMotion() ? 0 : 300,
            imageFadeDuration: _prefersReducedMotion() ? 0 : 300,
            resizeDuration: _prefersReducedMotion() ? 0 : 400
        });

        var title = $.trim($('#content h1').first().text());
        var imgExt = /\.(jpe?g|png|gif|webp|avif|bmp|tiff?)(\?.*)?$/i;

        $('#itemfiles a, .items.show #content .item-file a').each(function () {
            var $a = $(this);
            var $img = $a.find('img').first();
            if (!$img.length) return;

            var href = $a.attr('href') || '';
            if (!imgExt.test(href)) {
                // Thumbnail links to the file page: derive the original from the thumbnail src.
                var src = $img.attr('src') || '';
                var original = src.replace(/\/files\/(thumbnails|square_thumbnails|fullsize)\//, '/files/fullsize/');
                if (original === src || !imgExt.test(original)) return;
                href = original;
            }
            $a.attr({
                href: href,
                'data-lightbox': 'item-gallery',
                'data-title': $img.attr('alt') || title
            });
        });
    }

    return {
        init: init
    };

})(jQuery);

jQuery(function () {
    if (window.Omeka) {
        if (typeof Omeka.skipNav === 'function') Omeka.skipNav();
        if (typeof Omeka.megaMenu === 'function') Omeka.megaMenu('#primary-nav ul.navigation');
    }
    NessiaTheme.init();
});
