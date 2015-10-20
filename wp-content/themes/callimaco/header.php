<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package WordPress
 * @subpackage Callimaco
 * @since Callimaco 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<!--[if lt IE 9]>
	<script src="<?php echo esc_url( get_template_directory_uri() ); ?>/js/html5.js"></script>
	<![endif]-->
	<script>(function(){document.documentElement.className='js'})();</script>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <div id="fb-root"></div>
<script>
 function isArray(a) {
  return Object.prototype.toString.call(a) === "[object Array]";
}

function make(desc) {
  if (!isArray(desc)) {
    return make.call(this, Array.prototype.slice.call(arguments));
  }

  var name = desc[0],
  attributes = desc[1],

  el = document.createElement(name),
  attr,
  i,
  start = 1;
  if (typeof attributes === "object" && attributes !== null && !isArray(attributes)) {
    for (attr in attributes) {
      el[attr] = attributes[attr];
    }
    start = 2;
  }

  for (i = start; i < desc.length; i++) {
    if (isArray(desc[i])) {
      el.appendChild(make(desc[i]));
    } else {
      el.appendChild(document.createTextNode(desc[i]));
    }
  }

  return el;
}
window.fbAsyncInit = function () {
  FB.init({
    appId : 1555133888082231,
    xfbml : false,
    version : 'v2.1'
  });

  FB.api('/51581894441/posts', 'get', {
    access_token : '1555133888082231|sfaZY_stixvZ1GYklQg5xLAb2ZQ'
  }, function (response) {
    if (!response || response.error) {
      alert('Error occured');
    } else {
      FBdata = response.data;
      postByLikes = FBdata.filter(function (e) {
          return e.likes
        }).sort(function (a, b) {
          return parseFloat(b.likes.data.length) - parseFloat(a.likes.data.length)
        });

      $('.social .single').append('<div class="fb-post" data-href="https://www.facebook.com/51581894441/posts/' + postByLikes.shift().id.split('_')[1] + '"data-width="300"></div>');
      var popID = postByLikes.slice(0, 5).map(function (e) {
          return e.id
        });
      FBdata = FBdata.filter(function (e) {
          return popID.indexOf(e.id) === -1
        });
      $('.single  .fb_iframe_widget span').height(Math.ceil($('.single .fb_iframe_widget iframe').height() * 0.8));

      $('.single  .fb_iframe_widget span').width(Math.ceil($('.single  .fb_iframe_widget iframe').width() * 0.8));

      $('.social .popular li').append(function (i) {
        return makePost(postByLikes[i])
      });
      $('.social .recent li').append(function (i) {
        return makePost(FBdata[i])
      })
      $('.text-fill').dotdotdot();
      FB.XFBML.parse(document.getElementsByClassName('social')[1], function () {})

    }
  });

};
(function (d, s, id) {
  var js,
  fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) {
    return;
  }
  js = d.createElement(s);
  js.id = id;
  js.async = true;
  js.src = "//connect.facebook.net/it_IT/sdk.js";
  fjs.parentNode.insertBefore(js, fjs);
}
  (document, 'script', 'facebook-jssdk'));
function makePost(post) {
  var FBelement = make(["div",
        ['div', ['img', {
              src : post.picture ? post.picture : ''
            }
          ]],
        ['div',
          ['a', (aa = new Date(post.created_time), aa.toLocaleString())],
          ['div', ['p', post.message ? post.message : ''], ['p', post.name ? post.name : (post.caption ? post.caption : '')], ['p', post.description ? post.description : '']],
          ['div', ['span', ['a', 'See Translation']], ['span', ['a', 'Condividi']], ['span', ['a', 'Likes']]
          ]
        ]]);
  $(FBelement).addClass('FB_el');
  $(FBelement).find('img').addClass('block_img');
  $(FBelement).children('div')[0].className = 'img_container';
  $(FBelement).children('div')[1].className = 'block_body';
  $(FBelement).children('div').children('a').addClass('post__date');
  $(FBelement).children('div').children('div')[0].className = 'post__link text-fill';
  $(FBelement).children('div').children('div')[1].className = 'post__share';

  return FBelement
};

  </script>
   <div class="sidemenu_overlay"></div>
    <div class="side_menu push_menu">
      <div class="widget woocommerce widget_shopping_cart">
        <div class="widget_shopping_cart_content">
          <ul class="cart_list product_list_widget">
            <li class="empty">No products in the cart.</li>
          </ul>
          <!-- end product list -->
        </div>
      </div>
      <div class="widget WooCommerceMyAccountWidget">
        <div class="logout">
          <h2 class="widgettitle">Customer Login</h2>
          <form name="wma_login_form" id="wma_login_form" action="http://tokokoodemo.us/papirus/books/wp-login.php" method="post">
            <p class="login-username">
            <label for="user_login">Username</label> 
            <input name="log" id="user_login" class="input" value="" size="20" type="text" /></p>
            <p class="login-password">
            <label for="user_pass">Password</label> 
            <input name="pwd" id="user_pass" class="input" value="" size="20" type="password" /></p>
            <p class="login-submit">
            <input name="wp-submit" id="wp-submit" class="button-primary" value="Log In" type="submit" /> 
            <input name="redirect_to" value="http://tokokoodemo.us/papirus/books/" type="hidden" /></p>
          </form>
          <a class="woo-ma-link woo-ma-lost-pass" href="http://tokokoodemo.us/papirus/books/my-account/lost-password/">Lost
          password?</a>
          <p>
            <a class="woo-ma-button woo-ma-cart-link cart-link" href="http://tokokoodemo.us/papirus/books/cart/"
            title="View your shopping cart">View your shopping cart</a>
          </p>
        </div>
      </div>
    </div>
    <div class="site_content">
      <div id="quick-view-modal" hidden="hidden">
        <div class="modal-wrapper">
          <div class="product">
            <div class="modal-image">
              <img src="https://dl.dropboxusercontent.com/u/93960024/cdn/giank/images/g/a04.jpg" alt="" />
              <form id="" method="" action=""></form>
            </div>
            <div class="summary">
              <h1 class="titolo">Egon Schiele. L&#39;opera completa</h1>
              <div>
                <p class="prezzo">
                  <span class="amount">150 €</span>
                </p>
              </div>
              <div class="description">
                <p>In definitiva l&#39; opera completa di Egon Schiele rappresenta la stoica consapevolezza di una sensibilità che
                esercita attraverso l&#39; arte il sano nichilismo di non produrre immagini salvifiche per il mondo, l&#39;
                edonismo creativo di chi non si lascia colpevolizzare dall&#39; economia di una morale da adulti, per questo
                conciliante. La morte ha stroncato la giovane vita di Schiele lasciandoci una riserva di immagini capaci di esibire
                le spoglie narcisistiche di un erotismo che, seppure mutilato, rappresenta l&#39; unico deterrente per delirare
                alla fine di un&#39; epoca ridotta alla pornografia del frammento.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="page" class="hfeed site">
        <header class="page_header">
          <div class="header-wrap">
            <div class="brand">
            <div class="logoimg"><img src="./wp-content/uploads/2015/05/DD_logo_def2-150x150.png" height="150" width="150" alt=""></div>
    
              <h1 class="bigbrand">
                <a href="#">DANTE &amp; DESCARTES</a>
              </h1>
              <h2 class="litlebrand">
                <a href="#">LIBRERIA</a>

            </div>
            <section class="settings">
              <nav class="settings-nav">
                <ul>
                  <li>
                    <a href="#">ITA</a>
                  </li>
                  <li>
                    <a href="#">€</a>
                  </li>
                  <li>
                    <img src="https://dl.dropboxusercontent.com/u/93960024/cdn/giank/images/pay_pal_logo.png" alt=""
                    class="paypal" />
                  </li>
                </ul>
                <ul>
                  <li>
                    <a href="#">SIGN IN</a>
                  </li>
                  <li>
                    <a href="#">ACCOUNT</a>
                  </li>
                </ul>
                <ul>
                  <li>
                    <a href="#">LOG IN</a>
                  </li>
                  <li class="last">
                    <a class="fb-connect">LOG IN FACEBOOK</a>
                  </li>
                </ul>
              </nav>
            </section>
            <section class="menu primary_menu">
              <nav>
                <ul>
                  <li>
                    <a href="#search-form">Catalogo</a>
                  </li>
                  <li>
                    <a href="#">Edizioni</a>
                  </li>
                  <li>
                    <a href="#blog">Blog</a>
                  </li>
                  <li>
                    <a href="#contact">Dove Siamo</a>
                  </li>
                </ul>
              </nav>
            </section>
            <section class="action">
              <ul>
                <li class="cart">
                  <a href="#"></a>
                </li>
                <li class="search-form-menu">
                  <div class="search_bar">
                    <form id="search-bar" method="" action="">
                      <input name="s" id="top_S" placeholder="Cerca nel nostro catalogo" type="text" class="field txt" />
                    </form>
                  </div>
                  <a href="#" class="search_button_trigger"></a>
                </li>
              </ul>
              <div class="arrow"></div>
            </section>
          </div>
        </header>
        <div id="content" class="site-content"></div>
      </div>
    </div>