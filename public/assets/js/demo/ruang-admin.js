(function ($) {
    "use strict"; // Start of use strict

    // Toggle the side navigation
    // Catatan: handler body.sidebar-toggled / .sidebar-mobile-open sepenuhnya
    // di-handle oleh script di sidebar.blade.php (responsif untuk desktop & mobile).
    // Di sini kita hanya memastikan sub-menu collapse tertutup saat toggle
    // di desktop.
    $(document).on('click', '#sidebarToggle, #sidebarToggleTop', function (e) {
        // Hanya relevan untuk desktop; untuk mobile, sidebar.blade.php yang handle
        if ($(window).width() > 768 && $('body').hasClass('sidebar-toggled')) {
            $('.sidebar .collapse').collapse('hide');
        }
    });

    // Close any open menu accordions when window is resized below 768px
    $(window).resize(function () {
        if ($(window).width() < 768) {
            $('.sidebar .collapse').collapse('hide');
        };
    });

    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function (e) {
        if ($(window).width() > 768) {
            var e0 = e.originalEvent,
                delta = e0.wheelDelta || -e0.detail;
            this.scrollTop += (delta < 0 ? 1 : -1) * 30;
            e.preventDefault();
        }
    });

    // Scroll to top button appear
    $(document).on('scroll', function () {
        var scrollDistance = $(this).scrollTop();
        if (scrollDistance > 100) {
            $('.scroll-to-top').fadeIn();
        } else {
            $('.scroll-to-top').fadeOut();
        }
    });

    // Smooth scrolling using jQuery easing
    $(document).on('click', 'a.scroll-to-top', function (e) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: ($($anchor.attr('href')).offset().top)
        }, 1000, 'easeInOutExpo');
        e.preventDefault();
    });

})(jQuery); // End of use strict

// Modal Javascript

$(document).ready(function () {
    $("#myBtn").click(function () {
        $('.modal').modal('show');
    });

    $("#modalLong").click(function () {
        $('.modal').modal('show');
    });

    $("#modalScroll").click(function () {
        $('.modal').modal('show');
    });

    $('#modalCenter').click(function () {
        $('.modal').modal('show');
    });
});

// Popover Javascript

$(function () {
    $('[data-toggle="popover"]').popover()
});
$('.popover-dismiss').popover({
    trigger: 'focus'
});


// Version in Sidebar

var version = document.getElementById('version-ruangadmin');

if (version) {
    version.innerHTML = "Version 1.1";
}
