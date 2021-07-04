"use strict";
/*===============================================*/

/*  PRE LOADING
 /*===============================================*/

$(window).on('load', function () {
  /* ------------------------------------- */

  /* *.PrelOader .......................... */

  /* ------------------------------------- */
  $('.preloader').delay(500).fadeOut('slow');
});
$(document).ready(function () {
  /* ------------------------------------- */

  /* *.Splitting .......................... */

  /* ------------------------------------- */
  Splitting();
  /* ------------------------------------- */

  /* *.WOW JS .......................... */

  /* ------------------------------------- */

  var wow = new WOW({
    animateClass: 'animated',
    offset: 10,
    mobile: true
  });
  wow.init();
  /* ------------------------------------- */

  /* *.Parallax .......................... */

  /* ------------------------------------- */

  $('.jarallax').jarallax({
    speed: 0.6
  });
  /* ------------------------------------- */

  /* *. Mobile Menu.......................... */

  /* ------------------------------------- */

  $('.mobile-menu__btn').on('click', function (event) {
    event.preventDefault();
    $(this).toggleClass('active');
    $('.mobile-menu-overlay').toggleClass('active');
    $('.mobile-menu').toggleClass('active');
  });
  $('.mobile-menu-overlay').on('click', function (event) {
    event.preventDefault();
    $(this).toggleClass('active');
    $('.mobile-menu__btn').toggleClass('active');
    $('.mobile-menu').toggleClass('active');
  });
  /* ------------------------------------- */

  /* *. Smooth Scroll To Anchor.......................... */

  /* ------------------------------------- */

  $('a.ease[href^="#"]').on('click', function (event) {
    var $anchor = $(this);
    $('html, body').stop().animate({
      scrollTop: $($anchor.attr('href')).offset().top
    }, 1500, 'easeInOutExpo');
    event.preventDefault();
  });
  /* ------------------------------------- */

  /* *.Sticky Header & Back to top ........ */

  /* ------------------------------------- */

  $(window).on('scroll', function () {
    if ($(window).width() > 992) {
      if ($(window).scrollTop() < 200) {
        $(".header").removeClass('header--sticky');
        $('#scrollTopBtn').removeClass('active');
      } else {
        $(".header").addClass('header--sticky');
        $('#scrollTopBtn').addClass('active');
      }
    }
  });
  /* ------------------------------------- */

  /* *.popup Video Button ........ */

  /* ------------------------------------- */

  $('.btn-video').magnificPopup({
    disableOn: 700,
    type: 'iframe',
    mainClass: 'mfp-fade',
    removalDelay: 160,
    preloader: true,
    fixedContentPos: false
  });
  /* ------------------------------------- */

  /* *.Brand Carousel .......................... */

  /* ------------------------------------- */

  $(".brand-carousel").owlCarousel({
    loop: true,
    autoplay: true,
    smartSpeed: 450,
    autoplayHoverPause: true,
    dots: false,
    nav: false,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1,
        nav: true
      },
      800: {
        items: 2,
        nav: true
      },
      1100: {
        nav: true,
        items: 5
      }
    },
    items: 5
  });
  /* ------------------------------------- */

  /* *.Testimonial Carousel .......................... */

  /* ------------------------------------- */

  $(".testimonial-one-carousel").owlCarousel({
    loop: true,
    autoplay: true,
    smartSpeed: 450,
    autoplayHoverPause: true,
    dots: true,
    nav: false,
    responsiveClass: true,
    items: 1
  });
  $(".store-carousel").owlCarousel({
    loop: true,
    autoplay: true,
    smartSpeed: 450,
    autoplayHoverPause: true,
    dots: true,
    nav: false,
    responsiveClass: true,
    responsive: {
      0: {
        items: 1
      },
      800: {
        items: 2
      },
      1100: {
        items: 3
      }
    },
    items: 3
  });
  /* ------------------------------------- */

  /* *.Portfolio Masonry ........ */

  /* ------------------------------------- */

  $('.grid').masonry({
    itemSelector: '.grid-item',
    columnWidth: '.grid-sizer',
    percentPosition: true
  });
  /* ------------------------------------- */

  /* *.Portfolio Filter ........ */

  /* ------------------------------------- */

  $('.portfolio-categories').on('click', 'li', function (e) {
    e.preventDefault();
    $('.portfolio-categories li').removeClass('active');
    $(this).closest('li').addClass('active');
  });
  var filterizd = $('.portfolio-container');

  if (filterizd.length > 0) {
    filterizd.imagesLoaded(function () {
      filterizd.filterizr({
        layout: 'sameWidth'
      });
    });
  }
  /* ==============================================
        pop up
       ============================================== */


  $('.filtr-container').magnificPopup({
    delegate: 'a',
    type: 'image',
    tLoading: 'Loading image #%curr%...',
    mainClass: 'mfp-img-mobile',
    gallery: {
      enabled: true,
      navigateByImgClick: true,
      preload: [0, 1] // Will preload 0 - before current, and 1 after the current image

    },
    image: {
      tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
      titleSrc: function titleSrc(item) {
        return item.el.attr('title');
      }
    },
    zoom: {
      enabled: true,
      duration: 300,
      // don't foget to change the duration also in CSS
      opener: function opener(element) {
        return element.find('img');
      }
    }
  });
});