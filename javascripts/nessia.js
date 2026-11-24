/**
 * NESSIA Theme — Main JavaScript
 *
 * Near Eastern and Semitic Information Archive
 * https://nessia.org
 *
 * @package NESSIA
 * @version 1.2.7
 */

var NessiaTheme = (function ($) {
    'use strict';

    /**
     * Initialise all theme components.
     */
    function init() {
        _setupMobileNav();
        _setupSmoothScroll();
        _setupSearchToggle();
        _ensureSearchSubmitLabel();
        _expandFileViewers();
        _setupHeaderShrink();
        _setupAdvancedSearch();
    }

    /**
     * Mobile navigation toggle.
     */
    function _setupMobileNav() {
        var $nav = $('#primary-nav');
        if (!$nav.length) {
            return;
        }
        var $toggle = $('<button class="nessia-nav-toggle" aria-label="Toggle navigation" aria-expanded="false">' +
            '<span class="nessia-hamburger"></span>' +
            '</button>');

        $nav.before($toggle);

        $toggle.on('click', function () {
            var expanded = $(this).attr('aria-expanded') === 'true';
            $(this).attr('aria-expanded', !expanded);
            $nav.toggleClass('nessia-nav-open');
            $(this).toggleClass('nessia-nav-toggle-active');
        });
    }

    /**
     * Smooth scroll for anchor links.
     */
    function _setupSmoothScroll() {
        $('a[href^="#"]').not('#skipnav').on('click', function (e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 400);
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
     * Ensure search controls have accessible labels.
     * Handles submit text, aria-label, and visually-hidden label for the input.
     */
    function _ensureSearchSubmitLabel() {
        var label = 'Search';

        // --- Submit input ---
        var $submitInput = $('#search-container input[type="submit"]');
        if ($submitInput.length) {
            if (!($submitInput.val() || '').trim()) {
                $submitInput.val(label);
            }
            if (!$submitInput.attr('aria-label')) {
                $submitInput.attr('aria-label', label);
            }
        }

        // --- Submit button ---
        var $submitBtn = $('#search-container button[type="submit"]');
        if ($submitBtn.length) {
            if (!($submitBtn.text() || '').trim()) {
                $submitBtn.text(label);
            }
            if (!$submitBtn.attr('aria-label')) {
                $submitBtn.attr('aria-label', label);
            }
        }

        // --- Ensure search text input has an accessible name ---
        var $searchInput = $('#search-container input[type="text"]');
        if ($searchInput.length) {
            var inputId = $searchInput.attr('id');
            if (!inputId) {
                inputId = 'search-q';
                $searchInput.attr('id', inputId);
            }
            // Only inject label if none is associated
            if (!$('label[for="' + inputId + '"]').length) {
                $('<label class="sr-only" for="' + inputId + '">' + label + '</label>')
                    .insertBefore($searchInput);
            }
        }
    }

    /**
     * Force file viewers (iframe, embed, object) in #itemfiles to fill their
     * container. Omeka plugins often set inline width/height attributes that
     * prevent CSS from taking effect.
     */
    function _expandFileViewers() {
        var $container = $('#itemfiles');
        if (!$container.length) return;

        if ($container.is('.nessia-media-audio, .nessia-media-video')) return;

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

        $(window).on('scroll', function () {
            if ($(this).scrollTop() > scrollThreshold) {
                $header.addClass('nessia-header-compact');
            } else {
                $header.removeClass('nessia-header-compact');
            }
        });
    }

    // Advanced Search panel toggle — accessible, uses HTML hidden attribute.
    function _setupAdvancedSearch() {
        var container = document.getElementById('search-container');
        if (!container) return;

        var panel = document.getElementById('advanced-form');
        // Accept our PHP-injected button, or fall back to Omeka's link variant.
        var toggle = document.getElementById('advanced-toggle')
            || container.querySelector('.advanced-toggle')
            || container.querySelector('.advanced a');

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

    return {
        init: init
    };

})(jQuery);
