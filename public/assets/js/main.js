/**
 * Markham Infiniti – homepage scripts (jQuery)
 *  - Model showroom slider (arrows + swipe, responsive cards per view)
 *  - small helpers for placeholder links
 */
$(function () {
    'use strict';

    // ------------------------------------------------------------------
    // Model showroom slider
    // ------------------------------------------------------------------
    var $frame = $('.showroom-frame');
    var $track = $('#showroomTrack');
    var $cards = $track.children('.model-card');
    var $prev = $('#showroomPrev');
    var $next = $('#showroomNext');

    if ($cards.length) {
        var index = 0;
        var perView = 4;

        // how many cards fit at the current width (matches Bootstrap breakpoints)
        var getPerView = function () {
            var w = window.innerWidth;
            if (w >= 1200) return 4;
            if (w >= 768) return 3;
            if (w >= 576) return 2;
            return 1;
        };

        var maxIndex = function () {
            return Math.max(0, $cards.length - perView);
        };

        var render = function () {
            index = Math.min(Math.max(index, 0), maxIndex());
            $track.css('transform', 'translateX(' + (-index * (100 / perView)) + '%)');

            var scrollable = maxIndex() > 0;
            $prev.add($next).toggleClass('hidden', !scrollable);
            $prev.prop('disabled', index === 0);
            $next.prop('disabled', index >= maxIndex());
        };

        var layout = function () {
            perView = getPerView();
            $frame.css('--per-view', perView);
            $track.css('--per-view', perView);
            render();
        };

        $prev.on('click', function () { index--; render(); });
        $next.on('click', function () { index++; render(); });

        // touch swipe
        var startX = null;
        $track.on('touchstart', function (e) {
            startX = e.originalEvent.touches[0].clientX;
        }).on('touchend', function (e) {
            if (startX === null) return;
            var dx = e.originalEvent.changedTouches[0].clientX - startX;
            startX = null;
            if (Math.abs(dx) < 40) return;
            index += dx < 0 ? 1 : -1;
            render();
        });

        var resizeTimer;
        $(window).on('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(layout, 120);
        });

        layout();
    }

    // ------------------------------------------------------------------
    // Links that have no destination page in this task
    // ------------------------------------------------------------------
    $('a[href="#"]').on('click', function (e) {
        e.preventDefault();
    });
});
