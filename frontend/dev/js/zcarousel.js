(function(doc, win) {
    function useThisFunctionOnce() {
        // overwrite this function, so it will be executed only once
        useThisFunctionOnce = Function("");
        // real code below
        document.getElementsByTagName('body')[0].style.fontSize = window.getComputedStyle(docEl, null).getPropertyValue("font-size");
    }
    var docEl = doc.documentElement,
        recalc = function() {
            var clientHeight = docEl.clientHeight;
            if (!clientHeight) return;
            useThisFunctionOnce();
            docEl.style.fontSize = clientHeight + 'px';
            docEl.style.display = "none";
            docEl.clientHeight; // Force relayout - important to new Android devices
            docEl.style.display = "";
        };

    // Abort if browser does not support addEventListener
    if (!doc.addEventListener) return;

    // Test rem support
    var div = doc.createElement('div');
    div.setAttribute('style', 'font-size: 1rem');

    // Abort if browser does not recognize rem
    if (div.style.fontSize != "1rem") return;

    win.addEventListener('resize', recalc, false);
    doc.addEventListener('DOMContentLoaded', recalc, false);
})(document, window);

$(document).ready(function() {

    $(window).scroll(function() {
        if ($(this).scrollTop() > 90) {
            $('.page_header').addClass("sticky_el");
            $('.arrow').addClass('black_arr');
        } else {

            $('.page_header').removeClass("sticky_el");
            $('.arrow').removeClass('black_arr');
        }
    });
   function loadScript(src,callback){
  
    var script = document.createElement("script");
    script.type = "text/javascript";
    if(callback)script.onload=callback;
    document.getElementsByTagName("head")[0].appendChild(script);
    script.src = src;
  }
  
  
  loadScript('http://maps.googleapis.com/maps/api/js?v=3&sensor=false&callback=initialize');



    window.initialize=function() {


        // fornisce latitudine e longitudine
        var latlng = new google.maps.LatLng(42.745334, 12.738430);

        // imposta le opzioni di visualizzazione
        var options = {
            zoom: 16,
            center: new google.maps.LatLng(40.846925, 14.25177199999996),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        // crea l'oggetto mappa
        var map = new google.maps.Map(document.getElementById('googleMap'), options);
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(40.84703, 14.25205),
            map: map,
            title: "Libreria Dante & Descartes - Piazza del Gesù 14"
        });
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(40.84617, 14.25636),
            map: map,
            title: "Libreria Dante & Descartes - Via Mezzocannone 55"
        });
    }


function togglePM(el,type){
      var body = $("body");
      if(body.hasClass("side_menu_active")){
        $("body").removeClass("side_menu_active "+type+" overlay_active");
        $(el).removeClass("push_menu");
      }else{
        $("body").addClass("side_menu_active "+type+" overlay_active");
        $(el).addClass("push_menu");
        $(".sidemenu_overlay").fadeIn(function(){
          $(this).click(function(){
            $("body").removeClass("side_menu_active "+type+" overlay_active");
            $(el).removeClass("push_menu");
          })
        })
      }
    }

    $(".cart").click(function(e){
      e.preventDefault();
      togglePM(".side_menu","pushright");
    });
    $('.side-menu').click(function(e){
    e.preventDefault();
    togglePM(".side_menu","pushright");
  });

    $(' .hoverdir .product, .image_caption.hoverdir ').each(function() {
        $(this).hoverdir({
            speed: 400,
            hoverElem: '.product-details'
        });
    });

    
    $('.little').on('mouseenter', ".product", function(ev) {

        $(this).children('.product-details').css('overflow', 'visible');
    })
    $('.little').on('mouseleave', ".product", function(ev) {

        $(this).children('.product-details').css('overflow', 'hidden');
    })

    $('.carousel').carouFredSel({
        items: 1,
        height: 290.8,
        scroll: {
            pauseOnHover: true
        }
    });

    $(".novita li").hover(function() {
        $(this).find('.shadow').removeClass('sh1');
        $(this).find('.shadow').addClass('sh2');

        $(this).find('img').stop().animate({
            top: "-15px"
        }, function() {

        });
        $(this).find("h4").stop().animate({
            opacity: 1
        }, 300);
    }, function() {
        $(this).find('.shadow').removeClass('sh2');
        $(this).find('.shadow').addClass('sh1');
        $(this).find('img').stop().animate({
            top: "0"
        }, function() {

        });
        $(this).find("h4").stop().animate({
            opacity: 0
        }, 100)
    });

    $(document).on('click', '.product .button-quickview', function(e) {
        Custombox.open({
            effect: 'fadein',
            speed: 600,
            overlay: true,
            target: '#quick-view-modal'
        });

        e.preventDefault();
    });

    $('.text-fill').dotdotdot();

      $(".search_button_trigger").click(function(e){
      e.preventDefault();
      if($("body").hasClass("search_bar_active")){
        $("body").removeClass("search_bar_active");
        $(".search_bar").fadeOut(100,function(){
          $(".search_bar").addClass('closed')
          $(".primary_menu").fadeIn(100);
      
        });
      }else{
        $("body").addClass("search_bar_active");
        $(".primary_menu").fadeOut(100,function(){
          $(".search_bar").addClass('opened')
          $(".search_bar").fadeIn(100);
        });        
      }
    })

      var anch=$('.anchor').map(function(){return $(this).offset().top});


function clickArrow(e) {
  next=anch.filter(function(){return this > window.scrollY});
  if(next.length>0){
  $('html, body').animate({scrollTop: next[0]}, 2000);
  } else{
    $('html, body').animate({scrollTop: 0}, 2000);
  }
}
$('.arrow').click(clickArrow);

});
