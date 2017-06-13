function updatePushy() {
  if (window.matchMedia('(max-width: 768px)').matches) {
    $( "#top" ).addClass("mobile");
    $( ".menu-btn" ).fadeIn();
  } else {
    $( "#top" ).removeClass("mobile").removeClass("pushy-open-right");
    $( ".menu-btn" ).fadeOut();
  }
}
function pushyInit() {
  var resizeTimer;
  updatePushy();
  $( window ).resize(function() {
    if (!resizeTimer) {
      resizeTimer = setTimeout(function () {
        updatePushy();
        resizeTimer = null;
      }, 10);
    }

  });
}
function scrollTo(target) {
  if (target) {
    $('html, body').animate({
      scrollTop: $(target).offset().top - $('#header .inside').outerHeight()
    }, 500);
  }
}
$(document).ready(function() {
    var headerHeight, introHeight;
    headerHeight =  $('#header').height();

    var stickyNav = function() {
        var scrollTop = $(window).scrollTop();

        if (scrollTop > headerHeight) {
            $('#header').addClass('sticky');
            $( ".scroll-top" ).fadeIn();
        } else {
            $('#header').removeClass('sticky');
            $( ".scroll-top" ).fadeOut();
        }
    };

    stickyNav();

    $(window).scroll(function() {
        stickyNav();
    });
    $('.home .home-nav li a')
      .click(function(event) {
        event.preventDefault();
        var href = $(this).attr('href');
        var target = href.substring(href.indexOf("#"));
        scrollTo(target);
      }
    );
    $('.scroll-top')
      .click(function(event) {
        event.preventDefault();
        scrollTo("#intro");
      }
    );
    pushyInit();
    new WOW().init();
});
