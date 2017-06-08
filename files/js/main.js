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
            $('.scroll-top').removeClass('ontop');
        } else {
            $('#header').removeClass('sticky');
            $('.scroll-top').addClass('ontop');
        }
    };

    stickyNav();

    $(window).scroll(function() {
        stickyNav();
    });
    $('.home-nav li a')
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
});
