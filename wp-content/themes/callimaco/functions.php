<?php
/*
*
*
*     DEBUG
*
*
*/
if(!function_exists('_log')){
  function _log( $message ) {
    if( WP_DEBUG === true ){
      if( is_array( $message ) || is_object( $message ) ){
        error_log( var_dump( $message ) );
      } else {
        error_log( $message );
      }
    }
  }
}


add_action( 'add_meta_boxes', 
	function(){
		add_meta_box(
			'birgire_debug',
			'Product Attributes - Debug',
			'birgire_meta_box_callback',
			'product'
		);
	}
);

/*
*
*
*     THEME INIT
*
*
*/
function theme_init(){
  cmlc_woocommerce();
    add_action( 'wp_enqueue_scripts', 'callimaco_scripts' );    
    add_action('login_enqueue_scripts', 'clmc_admin_theme_style');
    add_action('admin_enqueue_scripts', 'cmlc_admin_edit_script');
    add_filter( 'woocommerce_register_post_type_product', 'cmlc_change_product_label' );
    add_filter('show_admin_bar', '__return_false',999);
    //add_action( 'plugins_loaded', 'clmc_create_custom_product_type' );
    //add_filter( 'product_type_selector', 'clmc_add_custom_product_type' );
    add_action( 'init', 'create_consigli_taxonomies', 0 );
  if ( current_user_can( 'gestisci_negozio' ) ) {


    add_action( 'admin_head-index.php', 'clmc_dashboard_columns' );

    add_action( 'admin_menu', 'cmlc_change_menu_label',999 );
    add_action( 'wp_before_admin_bar_render', 'clmc_admin_bar_render',999 );

    remove_action('in_admin_footer', 'wp_ozh_adminmenu_footer'); 
  
    add_filter( 'woocommerce_display_admin_footer_text', 'filter_woocommerce_display_admin_footer_text', 10, 1 );
    add_filter('admin_footer_text', 'remove_footer_admin');
    add_filter('update_footer', 'wooadmin_right_admin_footer_text_output', 11); //right side
    add_filter( 'contextual_help', 'clmc_remove_help_tabs', 999, 3 );
    add_filter('screen_options_show_screen', 'remove_screen_options', 10, 2);
    
    add_filter('gettext', 'wooadmin_rename_admin_menu_items');
    add_filter('ngettext', 'wooadmin_rename_admin_menu_items');
   
  }
 add_filter( 'manage_edit-product_columns', 'cmlc_book_columns',15 );
    add_action( 'manage_product_posts_custom_column', 'cmlc_render_book_columns_data', 10, 2 );

  remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
  remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
  add_filter( 'login_headerurl', 'clmc_login_logo_url' );
  add_filter( 'login_headertitle', 'clmc_login_logo_url_title' );
  // add_filter('login_message', 'clmc_login_message');

  add_action( 'add_meta_boxes' , 'remove_wp_meta_boxes', 40 );
  add_action( 'after_setup_theme', 'clmc_init_metaboxes' );

  
  add_filter('default_title', 'clmc_default_title_filter');
  add_action( "updated_post_meta", 'clmc_save_custom_meta', 10, 4 );
  add_action( 'save_post', 'clmc_update_custom_meta' );


}
theme_init();
function clmc_admin_theme_style(){
wp_enqueue_style('my-admin-theme',  get_stylesheet_directory_uri().'/wp-login.css'); 
}

function callimaco_scripts() {
  wp_enqueue_style( 'callimaco', get_stylesheet_uri() );
  wp_enqueue_script( 'callimaco-script', get_template_directory_uri() . '/js/functions.js', array(  ), '20140616', true );
}

function cmlc_woocommerce(){
  add_action('woocommerce_before_main_content', 'my_theme_wrapper_start', 10);
  add_action('woocommerce_after_main_content', 'my_theme_wrapper_end', 10);
  add_action( 'after_setup_theme', 'woocommerce_support' );
}
function cmlc_admin_edit_script($hook) {
  if(current_user_can('gestisci_negozio'))
    wp_enqueue_style('my-admin-theme',  get_stylesheet_directory_uri().'/wp-admin.css'); 
wp_enqueue_script('my-admin-scripts',  get_stylesheet_directory_uri().'/methods1.js',array('jquery'),'',true);
    
  if( $hook != 'edit.php' && $hook != 'post-new.php' && $hook != 'post.php') 
    return;
 
  wp_register_script( 'selectize', '/wp-content/themes/callimaco/js/selectize.min.js',array( 'jquery' ),'',true );
  wp_enqueue_script( 'selectize');
  wp_register_script( 'methods', '/wp-content/themes/callimaco/js/methods.js',array( 'jquery','selectize' ),'',true );
  wp_enqueue_script( 'methods');
  wp_register_style( 'selectize', '/wp-content/themes/callimaco/selectize.css' );
  wp_enqueue_style( 'selectize');
}


function cmlc_change_product_label( $args ){
    $labels = array(
        'name'               => __( 'Libri', 'callimaco' ),
        'singular_name'      => __( 'Libro', 'callimaco' ),
        'menu_name'          => _x( 'Libri', 'Admin menu name', 'callimaco' ),
        'add_new'            => __( 'Aggiungi Libro', 'callimaco' ),
        'add_new_item'       => __( 'Aggiungi nuovo Libro', 'callimaco' ),
        'edit'               => __( 'Modifica', 'callimaco' ),
        'edit_item'          => __( 'Modfica Libro', 'callimaco' ),
        'new_item'           => __( 'Nuovo Libro', 'callimaco' ),
        'view'               => __( 'View Book', 'callimaco' ),
        'view_item'          => __( 'View Book', 'callimaco' ),
        'search_items'       => __( 'Search Books', 'callimaco' ),
        'not_found'          => __( 'No Books found', 'callimaco' ),
        'not_found_in_trash' => __( 'No Books found in trash', 'callimaco' ),
        'parent'             => __( 'Parent Book', 'callimaco' )
    );

  $supports=array( 'post_title', 'thumbnail',  'page-attributes', 'publicize' );
  $args['supports']=$supports;
    $args['labels'] = $labels;
    $args['description'] = __( 'This is where you can add new  Book to your store.', 'callimaco' );
    return $args;
}


function clmc_add_custom_product_type( $types ){
    $types[ 'libro' ] = __( 'Libro' );
    return $types;
}

function clmc_create_custom_product_type(){
     // declare the product class
     class Libro extends WC_Product{
        public function __construct( $product ) {
           $this->product_type = 'libro';
           parent::__construct( $product );
           // add additional functions here
        }
    }
}

function create_consigli_taxonomies(){
  $labels = array(
    'name'                       => _x( 'Consigli', 'taxonomy general name' ),
    'singular_name'              => _x( 'Consiglio', 'taxonomy singular name' ),
    'search_items'               => __( 'Cerca Consiglio' ),
    
    'all_items'                  => __( 'Tutti i Consigli' ),
    'parent_item'                => null,
    'parent_item_colon'          => null,
    'edit_item'                  => __( 'Edita Consiglio' ),
    'update_item'                => __( 'Update Writer' ),
    'add_new_item'               => __( 'Add New Writer' ),
    'new_item_name'              => __( 'New Writer Name' ),
    'separate_items_with_commas' => __( 'Separate writers with commas' ),
    'add_or_remove_items'        => __( 'Add or remove writers' ),
    'choose_from_most_used'      => __( 'Choose from the most used writers' ),
    'not_found'                  => __( 'No writers found.' ),
    'menu_name'                  => __( 'Consigli' ),
  );

  $args = array(
    'hierarchical'          => false,
    'labels'                => $labels,
    'show_ui'               => true,
    'show_admin_column'     => true,

    'query_var'             => true,
    'rewrite'               => array( 'slug' => 'consiglio' ),
  );

  register_taxonomy( 'consiglio', 'product', $args );
}

/*
*
*
*         WOOCOMMERCE THEME INTEGRATION
*
*
*/

function my_theme_wrapper_start() {
  echo '<section id="main">';
}

function my_theme_wrapper_end() {
  echo '</section>';
}

function woocommerce_support() {
    add_theme_support( 'woocommerce' );
}
/*
*
*
*         ADMIN THEME
*
*
*/

function clmc_dashboard_columns() {
    add_screen_option(
        'layout_columns',
        array(
            'max'     => 2,
            'default' => 1
        )
    );
}

 

function cmlc_change_menu_label() {
    global $menu;
    global $submenu;
     
     $menu[70][0] = 'Clienti';
     unset($menu[75]); //Strumenti
     $media=$menu[10];
     $commenti=$menu[25];
     $clienti=$menu[70];
     add_submenu_page( 'index.php', $media[0], $media[0], 'upload_files', 'upload.php' );
     add_submenu_page( 'index.php', $commenti[0], $commenti[0], 'edit_posts', 'edit-comments.php' );
     add_submenu_page( 'woocommerce',$clienti[0], $clienti[0], 'gestisci_negozio', 'users.php' );
     unset($menu[10]);
     unset($menu[25]);
     unset($menu[70]);
     // $menu['woocommerce'][0][2] ='edit.php?post_type=shop_order';
     // $menu[55.5][2] ='edit.php?post_type=shop_order';
     foreach ($submenu['edit.php?post_type=product'] as $key => $value) {
        if($key !=10 && $key!=5) unset($submenu['edit.php?post_type=product'][$key]);
      } 
    remove_menu_page( 'edit.php' );
    $remove_wc_subm = array( 'wc-settings', 'wc-status', 'wc-addons', );
    foreach ( $remove_wc_subm as $submenu_slug ) {
        remove_submenu_page( 'woocommerce', $submenu_slug );
    }
    //    clmc_rename_woocoomerce();
}
function cmlc_rename_woocoomerce() 
{
    global $menu;

    // Pinpoint menu item
    $woo = recursive_array_search_php_91365( 'WooCommerce', $menu );

    // Validate
    if( !$woo )
        return;

    $menu[$woo][0] = 'Callimaco';

}

// http://www.php.net/manual/en/function.array-search.php#91365
function recursive_array_search_php_91365( $needle, $haystack ) 
{
    foreach( $haystack as $key => $value ) 
    {
        $current_key = $key;
        if( 
            $needle === $value 
            OR ( 
                is_array( $value )
                && recursive_array_search_php_91365( $needle, $value ) !== false 
            )
        ) 
        {
            return $current_key;
        }
    }
    return false;
}

function clmc_admin_bar_render(){
     global $wp_admin_bar;   
    if ( !is_object( $wp_admin_bar ) )
        return;

    $wp_admin_bar->remove_menu('wp-logo');
    $wp_admin_bar->remove_menu('about');
    $wp_admin_bar->remove_menu('wporg');
    $wp_admin_bar->remove_menu('documentation');
    $wp_admin_bar->remove_menu('support-forums');
    $wp_admin_bar->remove_menu('feedback');
 
// To remove Site name/View Site submenu and Edit menu from front end
   
    $wp_admin_bar->remove_menu('view-site');
    $wp_admin_bar->remove_menu('edit');
 
// To remove Update Icon/Menu
    $wp_admin_bar->remove_menu('updates');
 
// To remove Comments Icon/Menu
    $wp_admin_bar->remove_menu('comments');
 
// To remove ' New ' Menu
    $wp_admin_bar->remove_menu('new-content');
 
// To remove ' Howdy,user ' Menu completely and Search field from front end
   //$wp_admin_bar->remove_menu(' top-secondary ');
    $wp_admin_bar->remove_menu('search'); 
 
// To remove ' Howdy,user ' subMenus 
   /*
   $wp_admin_bar->remove_menu(' user-actions ');
   $wp_admin_bar->remove_menu(' user-info ');
   $wp_admin_bar->remove_menu(' edit-profile ');   
   $wp_admin_bar->remove_menu(' logout ');  
   */ 

}


function filter_woocommerce_display_admin_footer_text( $id_wc_pages ) 
{
    // make filter magic happen here...
    return array();
};
        
function remove_footer_admin () {
  echo bloginfo('name').' - Pannello di ammistrazione   <br> powered by &copy; 2015 - Callimaco, dedicato ai libri By Luca Castellone';
}

function wooadmin_right_admin_footer_text_output($text) {
    $text = 'Version 1.0';
    return $text;
}

function clmc_remove_help_tabs($old_help, $screen_id, $screen){
    $screen->remove_help_tabs();
    return $old_help;
}

function remove_screen_options($display_boolean, $wp_screen_object){
  $blacklist = array('post.php', 'post-new.php', 'index.php', 'edit.php');
  if (in_array($GLOBALS['pagenow'], $blacklist)) {
    $wp_screen_object->render_screen_layout();
    $wp_screen_object->render_per_page_options();
    return false;
  } else {
    return true;
  }
}

function wooadmin_rename_admin_menu_items( $menu ) {
    // $dxsitename = bloginfo('name');
    // $menu = str_ireplace( 'original name', 'new name', $menu );
    $menu = str_ireplace( 'Woocommerce', 'Callimaco', $menu );
    // return $menu array
    return $menu;
}

/*
*
*
*     CUSTOM lOGIN
*
*
*/
function clmc_login_logo_url() {
    return get_bloginfo( 'url' );
}


function clmc_login_logo_url_title() {
    return 'Dante & Descartes';
}

function clmc_login_message() {
$message = "DANTE & DESCARTES";
return $message;
}


function cmlc_book_columns($columns){
    global $post;
    $data = get_post_meta( $post->ID );
    //remove column
    $unset=array('tags','sku','is_in_stock','product_tag','featured','product_type');
    foreach ($unset as  $value) {
      unset( $columns[$value] );
    }
    //add column 
    $set=array('Autore','Ins. da','Status','ISBN','Anno','Depos.','Tipo Cat.','Vetrina','Altre Info');    
   foreach ($set as  $value) {
      $columns[$value] = __($value); 
    }
    return $columns;
}



function cmlc_render_book_columns_data( $column, $postid ) {
    $product = wc_get_product( $postid );
    $product_ID = $product->id;
    $post=get_post( $postid); 
    switch ( $column ) {
        case 'Ins. da' :
            $terms=$post->post_author;
              if ( is_string( $terms ) )
                echo $terms;
            else
                echo ' - ';
            break;
//            $terms = get_the_term_list( $post_id , 'book_author' , '' , ',' , '' );

        case 'Autore' :
              $autore=  explode(",", $product->get_attribute('pa_autore')); 
              $traduttore=explode(",", $product->get_attribute('pa_traduttore')); 
              $curatore=  explode(",", $product->get_attribute('pa_curatore')); 
              $autore_cat=$product->get_attribute('pa_autore_catalogo');
              
              $tip='';
             if(isset($autore)) {
                foreach ($autore as $key => $value) {
                  if  ($value !='')$tip.='<strong> Autore:  </strong>'.$value.'<br>';
                }
             }
             if(isset($traduttore)) {
              foreach ($traduttore as $key => $value) {
                if  ($value !='')$tip.='<strong> Traduttore:  </strong>'. $value. '<br>';
               }
             }
             if(isset($curatore))  {
              foreach ($curatore as $key => $value) {
                if  ($value !='') $tip.='<strong> Curatore:  </strong>'. $value.'<br>';             
              }
               
            }
             if(isset($autore_cat) && $autore_cat !='') $tip.='<strong> Autore(catalogo):  </strong>'.$autore_cat.'<br>';
            echo '<i data-tip="'.$tip.'" class="infotip1 dashicons dashicons-book"></i>';
             
            break;
        case 'Status' :
            $terms= $post->post_status; 
               if ( is_string( $terms ) )
                echo $terms;
            else
                echo ' - ';
            break;
         case 'ISBN' :
            $terms= $product->get_attribute('pa_isbn'); 
               if ( is_string( $terms ) )
                echo $terms;
            else
                echo ' - ';
            break;
          case 'Anno' :
            $terms= $product->get_attribute('pa_a_publ'); 
             if ( is_string( $terms ) )
                echo $terms;
            else
                echo ' - ';
            break;
          case 'Depos.' :
           $terms= $product->get_attribute('pa_deposito'); 
              if ( is_string( $terms ) )
                echo $terms;
            else
                echo ' - ';
            break;
          case 'Tipo Cat.' :
            $terms= $product->get_attribute('pa_libro_type'); 
               if ( is_string( $terms ) )
                echo $terms;
            else
                echo ' - ';
            break;
          case 'Altre Info' :
              $sottotitolo= $product->get_attribute('pa_sottotitolo'); 
              $editore= $product->get_attribute('pa_editore'); 
              $lingua= $product->get_attribute('pa_lingua'); 
              $collana= $product->get_attribute('pa_collana'); 
              $condizioni= $product->get_attribute('pa_condizione'); 
              $c_comm= $product->get_attribute('pa_c_cond'); 
              $quarta= $product->get_attribute('pa_quarta'); 
              $n_pag= $product->get_attribute('pa_n_pag'); 
              $sinossi= $product->get_attribute('pa_sinossi'); 
              $recenzione= $product->get_attribute('pa_recenzione'); 
              $soffietto= $product->get_attribute('pa_soffietto'); 
              $n_artg= $product->get_attribute('pa_numero_argomento'); 
              $desc_cat= $product->get_attribute('pa_descrizione_catalogo'); 
              $particolari= $product->get_attribute('pa_particolari'); 
              $formato= $product->get_attribute('pa_formato'); 
              $tip='';
              if(isset($sottotitolo) && $sottotitolo !='') $tip.='<strong> Sottotitolo:  </strong>'.$sottotitolo.'<br>';
              if(isset($editore) && $editore !='') $tip.='<strong> Editore:  </strong>'. $editore. '<br>';
              if(isset($lingua) && $lingua !='') $tip.='<strong> Lingua:  </strong>'. $lingua.'<br>';             
              if(isset($collana) && $collana !='') $tip.='<strong> Collana:  </strong>'. $collana .'<br>';
              if(isset($condizioni) && $condizioni !='') $tip.='<strong> Condizioni:  </strong>'. $condizioni ;
              if(isset($c_comm) && $c_comm !='') $tip.='<strong> Commento Condizione:  </strong>'. $c_comm .'<br>';
              if(isset($quarta) && $quarta !='') $tip.='<strong> Quarta:  </strong>'. $quarta .'<br>';
              if(isset($n_pag) && $n_pag !='') $tip.=' <strong> Numero pagine:  </strong>'. $n_pag .'<br>';
              if(isset($sinossi) && $sinossi !='') $tip.='<strong> Sinossi:  </strong>'. $sinossi .'<br>';
              if(isset($recenzione) && $recenzione !='') $tip.='<strong> Recenzione:  </strong>'. $recenzione .'<br>';
              if(isset($soffietto) && $soffietto !='') $tip.=' <strong>Soffietto:  </strong>'. $soffietto .'<br>';
              if(isset($n_artg) && $n_artg !='') $tip.='<strong> Numero argomento:  </strong>'. $n_artg .'<br>';
              if(isset($desc_cat) && $desc_cat !='') $tip.='<strong> Descrizione Catalogo:  </strong>'.$desc_cat .'<br>';
              if(isset($particolari) && $particolari !='') {
                $particolari1=explode(",", $particolari);
                $tip.='<strong> Particolari: </strong>';
                 foreach ($particolari1 as $key => $value) {
                  $tip.=$particolari1 .'<br>';
                }
              }
              if(isset($formato) && $formato !=''  ) $tip.='<strong> Formato:  </strong>'. $formato.'';
              echo '<i data-tip="'.htmlspecialchars($tip, ENT_QUOTES).'" class="infotip1 dashicons dashicons-info"></i>';
              break;
    }
    
}



 


/*
add_action( 'admin_bar_menu', 'remove_wp_nodes', 999 );

function remove_wp_nodes() 
{
    global $wp_admin_bar;   
    $wp_admin_bar->remove_node( 'new-post' );
    $wp_admin_bar->remove_node( 'new-link' );
    $wp_admin_bar->remove_node( 'new-media' );
}
*/
/**
 * Metabox callback
 */
 

/*
add_action( 'init', 'create_theme_taxonomy', 0 );
 
function create_theme_taxonomy() {
    if (!taxonomy_exists('theme1')) {
        register_taxonomy( 'theme1', 'product', array( 'hierarchical' => false, 'label' => __('theme1'), 'query_var' => 'theme1', 'rewrite' => array( 'slug' => 'theme1' ) ) );

 
        wp_insert_term('Beauty', 'theme1');
        wp_insert_term('Dragons', 'theme1');
        wp_insert_term('Halloween', 'theme1');
        register_taxonomy_for_object_type( 'theme1', 'product' );
    }
}
function add_theme_box() {
    add_meta_box('theme_box_ID', __('theme1'), 'your_styling_function', 'product', 'side', 'core');
    remove_meta_box('tagsdiv-theme1','product','core');
}   
 

 
    add_action('admin_menu', 'add_theme_box');

 


function your_styling_function($post) {
 
    echo '<input type="hidden" name="taxonomy_noncename" id="taxonomy_noncename" value="' . 
            wp_create_nonce( 'taxonomy_theme' ) . '" />';
 
     
    // Get all theme1 taxonomy terms
    $themes = get_terms('theme1', 'hide_empty=0'); 
 
?>
<select name='post_theme' id='post_theme'>
    <!-- Display themes as options -->
    <?php 
        $names = wp_get_object_terms($post->ID, 'theme1'); 
        ?>
        <option class='theme1-option' value=''
        <?php if (!count($names)) echo "selected";?>>None</option>
        <?php
    foreach ($themes as $theme1) {
        if (!is_wp_error($names) && !empty($names) && !strcmp($theme1->slug, $names[0]->slug)) 
            echo "<option class='theme1-option' value='" . $theme1->slug . "' selected>" . $theme1->name . "</option>\n"; 
        else
            echo "<option class='theme1-option' value='" . $theme1->slug . "'>" . $theme1->name . "</option>\n"; 
    }
   ?>
</select>    
<?php
}
*/
// add_filter( 'woocommerce_register_post_type_product', 'so_cp_post_type' );

// function so_cp_post_type( $args ){
// 	$cp_args = array(
//   'name' => 'libros'
//);

//$cp_output = 'objects'; // names or objects

//$cp_post_types = get_post_types( $cp_args , $cp_output);
    //return $cp_post_types['libros'];
//}


/*
add_action( 'woocommerce_product_options_general_product_data', 'clmc_add_custom_settings' );
function clmc_add_custom_settings() {
    global $woocommerce, $post;
    echo '<div class="options_group">';

    // Create a number field, for example for UPC
    woocommerce_wp_text_input(
      array(
       'id'                => 'wdm_upc_field',
       'label'             => __( 'UPC', 'woocommerce' ),
       'placeholder'       => '',
       'desc_tip'    => 'true',
       'description'       => __( 'Enter Unique Product Code.', 'woocommerce' ),
       'type'              => 'number'
       ));

    // Create a checkbox for product purchase status
      woocommerce_wp_checkbox(
       array(
       'id'            => 'wdm_is_purchasable',
       'label'         => __('Is Purchasable', 'woocommerce' )
       ));

    echo '</div>';
}
add_action( 'woocommerce_process_product_meta', 'wdm_save_custom_settings' );

function wdm_save_custom_settings( $post_id ){
// save UPC field
$wdm_product_upc = $_POST['wdm_upc_field'];
if( !empty( $wdm_product_upc ) )
update_post_meta( $post_id, 'wdm_upc_field', esc_attr( $wdm_product_upc) );

// save purchasable option
$wdm_purchasable = isset( $_POST['wdm_is_purchasable'] ) ? 'yes' : 'no';
update_post_meta( $post_id, 'wdm_is_purchasable', $wdm_purchasable );
}

*/




/** Step 2 (from text above). */
// add_action( 'admin_menu', 'menu_catalogo' );

/** Step 1. */
/*
function menu_catalogo() {
	add_menu_page( 'Opzioni Catalogo', 'Catalogo', 'gestisci_negozio', 'menu_cat', 'menu_catalogo_options' );
	add_submenu_page( 'menu_cat', 'Aggiungi Libro', 'Aggiungi Libro', 'gestisci_negozio', 'aggiungi libro', $function );
}

/** Step 3. */
/* function menu_catalogo_options() {
	if ( !current_user_can( 'gestisci_negozio' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<p>Here is where the form would go if I actually had options.</p>';
	$cp_args = array(
   'name' => 'libros'
);

$cp_output = 'objects'; // names or objects

$cp_post_types = get_post_types( $cp_args , $cp_output);

	
		var_dump($cp_post_types);
	
	echo '</div>';
}
*/

function remove_clmc_meta_boxes()
{   
    remove_meta_box( 'postexcerpt',  'product', 'normal');
    // remove_meta_box( 'woocommerce-product-data',  'product', 'normal');
    remove_meta_box( 'woocommerce-product-images',  'product', 'side');
    remove_meta_box( 'tagsdiv-product_tag',  'product', 'side');
}
function vp_dep_boolean_ebay($value)
{
    $args   = func_get_args();
    
    $result = false;
    foreach ($args as $val)
    {
      
        if($val == 'ebay' ) $result =true;
    }
    
    return $result;
}
VP_Security::instance()->whitelist_function('vp_dep_boolean_ebay');
function soff_func($text){
  $tt=func_get_args();
  
  global $post;
  $post_id=$post->ID;
  $product = wc_get_product( $post_id );
$special=array('_regular_price','post_title');
if(in_array($text,$special)){
  switch ($text) {
    case '_regular_price':
      $soff=get_post_meta( $post_id, '_regular_price', true ); ;
      break;
    
    case 'post_title':
      $soff=$post->post_title;
      break;
  }
}else{
  $soff=$product->get_attribute($text);
}


return $soff;
    // Built path to metabox template array file

//$text=$product->get_attribute('pa_soffietto');
//return $text;
}
VP_Security::instance()->whitelist_function('soff_func');
function vp_bind_selectize($title)
{
	$terms=get_terms('pa_'.$title,'orderby=name&hide_empty=0');

    $result = array();
    foreach ($terms as $term )
    {
    	$data=$term->name;
        $result[] = array('value' => $data, 'label' => $data);
    }
    return $result;
}
VP_Security::instance()->whitelist_function('vp_bind_selectize');

function create_textbox($name,$title='',$change=false){
	$title = $title!='' ? $title: $name;
  
  $comp_name=$change?$name:'pa_'.$name;
	return   array(
        'type' => 'textbox',
        'name' => $comp_name,        
        'label' => __(ucfirst($title), 'vp_textdomain'),
         'binding'=>array(

                'function'=> 'soff_func',
                'field' =>  $comp_name,
          ),

    );
}
function create_ebay_dep($name,$title='',$type,$change=false){
  $title = $title!='' ? $title: $name;
  $comp_name=$change?$name:'pa_'.$name;
  return   array(
        'type' => $type,
        'name' => $comp_name,        
        'label' => __(ucfirst($title), 'vp_textdomain'),
        'dependency'  => array(
        'field'    => 'libro_type',
        'function' => 'vp_dep_boolean_ebay',
    ),
         'binding'=>array(

                'function'=> 'soff_func',
                'field' =>  $comp_name,
          ),

    );
}
function create_texarea($name,$title=''){
  $title = $title!='' ? $title: $name;
  return   array(
        'type' => 'textarea',
        'name' => 'pa_'.$name,        
        'label' => __(ucfirst($title), 'vp_textdomain'),
          'binding'=>array(

                'function'=> 'soff_func',
                'field' =>  'pa_'.$name,
          ),
    );
}
function create_select($name,$items,$default=null,$title=''){
  $title = $title!='' ? $title: $name;
  return   array(
        'type' => 'select',
        'name' => 'pa_'.$name,        
        'label' => __(ucfirst($title), 'vp_textdomain'),
        'items'=>$items,
        'default' =>$default,
            'binding'=>array(

                'function'=> 'soff_func',
                'field' =>  'pa_'.$name,
          ),

    );
}
function create_combobox($name,$title=''){
		$title = $title!='' ? $title: $name;
	return   array(
    'type'      => 'group',
    'repeating' => false,
    'name'      => 'fixed_'.$name,
    'title'		=> '',
    'fields'    => array(
        array(
            'type'        => 'textcbox',
            'name'        => 'pa_'.$name,
             'binding'=>array(

                'function'=> 'soff_func',
                'field' =>  'pa_'.$name,
          ),

        ),
        /* more controls fields or even nested group ... */
                         array(
        'type' => 'selectize',
        'name' => 'ss_'.$name,
        'label' => __(ucfirst($title), 'vp_textdomain'),
       'items' => array(
        'data' => array(
            array(
                'source' => 'function',
                'value'  => 'vp_bind_selectize',
                'params' => $name,
            ),
        ),
    ),
       
   
    )   
    ),
);

}

function clmc_init_metaboxes()
{

   $mb1 = new VP_Metabox(
array(
    'id'          => 'clmc_book_meta',
    'types'       => array('product'),
    'title'       => __('Libro', 'vp_textdomain'),
    'context'	  => 'normal',
    'priority'    => 'high',
    'template'    => array(
        array(
            'type'      => 'group',            
            'name'      => 'group1',
            'title'     => __('Informazioni generali', 'vp_textdomain'),
            'fields'    => array(
			        create_textbox('post_title','Titolo',true),
							create_textbox('sottotitolo'),
               array(
        'type' => 'select',
        'name' => 'libro_type',        
        'label' => __(ucfirst('Tipo Catalogazione'), 'vp_textdomain'),
        'items'=>array(
                            array('value' => 'normale','label' => __('Normale', 'vp_textdomain'),),
                            array('value' => 'ebay','label' => __('Ebay', 'vp_textdomain'),),
                            array('value' => 'dd','label' => __('Dante & Descartes', 'vp_textdomain'),),
                            array('value' => 'catalogo','label' => __('Catalogo', 'vp_textdomain'),),
                        ),
        'default' =>array('normale',),
            'binding'=>array(

                'function'=> 'soff_func',
                'field' =>  'pa_libro_type',
          ),

    ),
                             
			                array(
			                    'type'      => 'group',
			                    'repeating' => true,
			                    'name'      => 'autore',
			                    'title'     => __('Autore', 'vp_textdomain'),
			                    'fields'    => array(
                        			create_combobox('nome'),create_combobox('cognome'),
                           			array(
								        'type' => 'select',
								        'name' => 'ss_autore',
								        'label' => __('Ruolo', 'vp_textdomain'),
								        'items' => array(
								            array(
								                'value' => 'pa_autore',
								                'label' => __('Autore', 'vp_textdomain'),
								            ),
								            array(
								                'value' => 'pa_traduttore',
								                'label' => __('Traduttore', 'vp_textdomain'),
								            ),
								            array(
								                'value' => 'pa_curatore',
								                'label' => __('Curatore', 'vp_textdomain'),
								            ),
								        ),
								        'default' => array(
								            'pa_autore',
								        ),
								    ),
								),
							),
create_textbox('_regular_price','Prezzo',true),
    create_combobox('editore'),
     create_combobox('a_publ','anno di pubblicazione'),
       create_combobox('isbn'),
      create_textbox('n_pag','Numero pagine'),
      create_combobox('lingua'),
       create_combobox('collana'),
        array(
                        'type' => 'select',
                        'name' => 'pa_condizione',
                        'label' => __('Condizione', 'vp_textdomain'),
                        'binding'=>array(

                'function'=> 'soff_func',
                'field' =>  'pa_condizione',
          ),
                        'items' => array(
                            array(
                                'value' => 'nuovo',
                                'label' => __('Nuovo', 'vp_textdomain'),
                            ),
                            array(
                                'value' => 'c_nuovo',
                                'label' => __('Come Nuovo', 'vp_textdomain'),
                            ),
                            array(
                                'value' => 'ottime',
                                'label' => __('Ottime Condizioni', 'vp_textdomain'),
                            ),
                            array(
                                'value' => 'buone',
                                'label' => __('Buone', 'vp_textdomain'),
                            ),
                            array(
                                'value' => 'accettabile',
                                'label' => __('Accettabile', 'vp_textdomain'),
                            ),
                        ),
                        'default' => array(
                            'buone',
                        ),
                    ),

         create_textbox('c_cond','Condizione Info'),
        
       create_combobox('formato'),
        array(
        'type' => 'checkbox',
        'name' => 'pa_particolari',
        'label' => __('Caratteristiche particolari', 'vp_textdomain'),
        
             'binding'=>array(

                'function'=> 'soff_func',
                'field' =>  'pa_particolari',
          ),
        'items' => array(
            array(
                'value' => 'prima',
                'label' => __('Prima Ed.', 'vp_textdomain'),
            ),
            array(
                'value' => 'autogr',
                'label' => __('Copia autograf.', 'vp_textdomain'),
            ),
            array(
                'value' => 'sovracoperta',
                'label' => __('Sovracoperta', 'vp_textdomain'),
            ),
        ),
        
    ),
  create_texarea('quarta'),
    create_texarea('sinossi'),
    create_texarea('recenzione'),
    create_texarea('soffietto'),
    create_ebay_dep('autore_catalogo','Autore da Catalogo','textbox',true),
     create_ebay_dep('descrizione_catalogo','Descrizione Catalogo','textarea',true),
     create_ebay_dep('numero_argomento'   ,'Numero Argomento','textbox',true),
      array(
        'type' => 'toggle',
        'name' => 'pa_deposito',
        'label' => __('Posseduto', 'vp_textdomain'),
     'binding'=>array(

                'function'=> 'soff_func',
                'field' =>  'pa_deposito',
          ),
        'default' => '1',
    ),
                    ),
                ),

                ),
            )
        );
}


function clmc_default_title_filter() {
  
    return 'My Filtered Title';
  
}


/*
function filter_handler( $data , $postarr ) {
  // do something with the post data
   _log($postarr);
  $data['post_title']=$postarr['clmc_book_meta']['group1']['0']['post_title'];
  _log(vp_metabox('clmc_book_meta.group1.0.post_title'));
  _log($data);
  return $data;
}
*/
// add_filter( 'wp_insert_post_data', 'filter_handler', '99', 2 );
function clmc_update_custom_attribute($post_id) {
   if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

      // Verify this came from the our screen and with proper authorization because save_post can be triggered at other times
      

  $clmc_meta=array();
  $clmc_meta['tax']=array();
  // $clmc_meta['tax']['pa_cognome']='clmc_book_meta.group1.0.fixed_cognome.0.pa_cognome';
  $clmc_meta['tax']['pa_libro_type']='clmc_book_meta.group1.0.libro_type';  
    $clmc_meta['tax']['pa_isbn']='clmc_book_meta.group1.0.fixed_isbn.0.pa_isbn';  
  $clmc_meta['tax']['pa_editore']='clmc_book_meta.group1.0.fixed_editore.0.pa_editore';
  $clmc_meta['tax']['pa_a_publ']='clmc_book_meta.group1.0.fixed_a_publ.0.pa_a_publ';
  $clmc_meta['tax']['pa_lingua']='clmc_book_meta.group1.0.fixed_lingua.0.pa_lingua';
  $clmc_meta['tax']['pa_condizione']='clmc_book_meta.group1.0.pa_condizione';
  $clmc_meta['tax']['pa_formato']='clmc_book_meta.group1.0.fixed_formato.0.pa_formato';
  $clmc_meta['tax']['pa_collana']='clmc_book_meta.group1.0.fixed_collana.0.pa_collana';
  $clmc_meta['tax']['pa_autore_catalogo']='clmc_book_meta.group1.0.autore_catalogo';
  $clmc_meta['ntax']=array();
  
  $clmc_meta['ntax']['pa_sottotitolo']='clmc_book_meta.group1.0.pa_sottotitolo';
  $clmc_meta['ntax']['pa_n_pag']='clmc_book_meta.group1.0.pa_n_pag';
  $clmc_meta['ntax']['pa_c_cond']='clmc_book_meta.group1.0.pa_c_cond';
  $clmc_meta['ntax']['pa_quarta']='clmc_book_meta.group1.0.pa_quarta';
  $clmc_meta['ntax']['pa_sinossi']='clmc_book_meta.group1.0.pa_sinossi';
  $clmc_meta['ntax']['pa_recenzione']='clmc_book_meta.group1.0.pa_recenzione';
  $clmc_meta['ntax']['pa_soffietto']='clmc_book_meta.group1.0.pa_soffietto';
  $clmc_meta['ntax']['pa_descrizione_catalogo']='clmc_book_meta.group1.0.descrizione_catalogo';
  $clmc_meta['ntax']['pa_numero_argomento']='clmc_book_meta.group1.0.numero_argomento';
  $clmc_meta['ntax']['pa_soffietto']='clmc_book_meta.group1.0.pa_soffietto';
  $clmc_meta['ntax']['pa_deposito']='clmc_book_meta.group1.0.pa_deposito';

 $product = wc_get_product( $post_id );
 $product_ID = $product->id;

// update_post_meta( $product_ID, 'post_title', vp_metabox('clmc_book_meta.group1.0.post_title1') );
if(null !== vp_metabox('clmc_book_meta.group1.0.post_title') && ''!=vp_metabox('clmc_book_meta.group1.0.post_title')){
  global $wpdb;
 $wpdb->update( $wpdb->posts, array( 'post_title' => vp_metabox('clmc_book_meta.group1.0.post_title')), array( 'ID' => $post_id ) );
}
/*
  $input2=vp_metabox('clmc_book_meta.group1.0.fixed_lingua.0.pa_lingua');
  
  wp_set_object_terms( $product_ID,$input2 , 'pa_lingua',false); 
  $attributes[ 'pa_lingua'] = array(
              'name'      =>  'pa_lingua',
              'value'     => $input2,
              
              'is_visible'  => true,
              'is_variation'  => false,
              'is_taxonomy'   => true
            );
*/
$particolari=vp_metabox('clmc_book_meta.group1.0.pa_particolari');
$particolari1=array();
if(is_array($particolari)){
  foreach ($particolari as $index => $array) {

$particolari1[]=$array;
  wp_set_object_terms( $product_ID,$array, 'pa_particolari',true); 
  
  }
}
$attributes[ 'pa_particolari'] = array(
              'name'      =>  'pa_particolari',
              'value'     => $particolari1,
              'position'    => '1',
              'is_visible'  => true,
              'is_variation'  => false,
              'is_taxonomy'   => true
            );

$author=vp_metabox('clmc_book_meta.group1.0.autore');
$autore=array('name'=>'pa_autore');
$curatore=array('name'=>'pa_curatore');
$traduttore=array('name'=>'pa_traduttore');
$nome=array('name'=>'pa_nome');
$cognome=array('name'=>'pa_cognome');

  foreach ($author as $index => $single) {
    $role=vp_metabox('clmc_book_meta.group1.0.autore.'.$index.'.ss_autore');
    // trim
    $name=vp_metabox('clmc_book_meta.group1.0.autore.'.$index.'.fixed_nome.0.pa_nome');
    $surname=vp_metabox('clmc_book_meta.group1.0.autore.'.$index.'.fixed_cognome.0.pa_cognome');
    $name_complete=$name.' '.$surname;
    $nome[$index]=$name;
    $cognome[$index]=$surname;
    switch ($role) {
    case "pa_autore":
        $autore[]=$name_complete;
        break;
    case "pa_curatore":
        $curatore[]=$name_complete;
        break;
    case "pa_traduttore":
        $traduttore[]=$name_complete;
        break;
  }
  }
$autore_parent=array($autore,$curatore,$traduttore,$nome,$cognome);
foreach ($autore_parent as $index => $array) {
  $tax=$array['name'];
  unset ($array['name']);
  wp_set_object_terms( $product_ID,$array, $tax,false); 
  $attributes[ $tax] = array(
              'name'      =>  $tax,
              'value'     => $array,
              'position'    => '1',
              'is_visible'  => true,
              'is_variation'  => false,
              'is_taxonomy'   => true
            );
}


  foreach ($clmc_meta['tax'] as $tax => $input) {

  $input1=vp_metabox($input);
  
  
  wp_set_object_terms( $product_ID,$input1 , $tax,false); 
  $attributes[ $tax] = array(
              'name'      =>  $tax,
              'value'     => $input1,
              'position'    => '1',
              'is_visible'  => true,
              'is_variation'  => false,
              'is_taxonomy'   => true
            );

 }
 foreach ($clmc_meta['ntax'] as $ntax => $input) {

  $input1=vp_metabox($input);
 

 
  $attributes[ $ntax] = array(
              'name'      =>  $ntax,
              'value'     => $input1,
              'position'    => '1',
              'is_visible'  => true,
              'is_variation'  => false,
              'is_taxonomy'   => false
            );

 }

update_post_meta( $product_ID, '_regular_price', vp_metabox('clmc_book_meta.group1.0._regular_price') );
update_post_meta( $product_ID, '_price', vp_metabox('clmc_book_meta.group1.0._regular_price') );

update_post_meta( $product_ID, '_product_attributes', $attributes );

}




function clmc_update_custom_meta($meta_id, $post_id, $meta_key, $_meta_value) {
  clmc_update_custom_attribute($post_id);
}

function clmc_save_custom_meta( $post_id ){
clmc_update_custom_attribute($post_id);
}





function birgire_meta_box_callback( $post )
{
		$product = wc_get_product( $post->ID );
	printf("<br><div>%s</div>\n<br>",  'Test');
printf("<div>%s</div>\n", 'Metabox value');
	var_dump( count(vp_metabox('clmc_book_meta.group1.0.autore')));
printf("<br><div>%s</div>\n<br>",  'Product attribute');
	printf( '<pre>%s</pre>', 
		var_dump($product->get_attribute('pa_lingua'))
	);
	printf("<br><div>%s</div>\n<br>",  'Product terms');
	printf( '<pre> %s</pre>', 
		var_dump(wp_get_object_terms( $product->id,'pa_lingua'))
	);
	printf("<br><div>%s</div>\n<br>",  'Post terms');
	printf( '<pre>%s</pre>', 
		var_dump(wp_get_object_terms( $post->ID,'pa_lingua'))
	);




	printf("<br><div>%s</div>\n<br>",  'prdouct attrs');
	printf( '<pre>%s</pre>', 
		print_r($product->get_attributes())
	);


	printf("<br><div>%s</div>\n<br>", 'Post_meta');
	
	printf( '<pre> %s</pre>', 
		print_r(get_post_meta( $post->ID ))
	);
	
	printf("<br><div>%s</div>\n<br>", 'Post Taxonomy');
	
	printf( '<pre>%s</pre>', 
		print_r(get_object_taxonomies( $post))
	);	

	printf("<br><div>%s</div>\n<br>", 'Vafpress');
$vp=get_object_vars(vp_metabox('clmc_book_meta'));
	var_dump($vp['template'][0]['fields']);
printf("<div>%s</div>\n", 'post');
	printf( '<pre>%s</pre>', 
    print_r($post)
  );
  
  printf( '<pre>%s</pre>', 
    print_r($product)
  );
	
	

	/*
	printf("<div>%s</div>\n", 'Post_meta');

	printf( '<pre> %s</pre>', 
		print_r(get_post_meta( $post->ID ))
	);

	printf("<div>%s</div>\n", 'Post_taxo');
	printf( '<pre>%s</pre>', 
		print_r(get_object_taxonomies( $post))
	);
	printf("<div>%s</div>\n", 'metabox');
	
	
	printf( '<pre>%s</pre>', 
		print_r(vp_metabox('clmc_general_meta'))
	
	);
		printf("<div>%s</div>\n", 'prdouct attrs');
	printf( '<pre>%s</pre>', 
		print_r($product->get_attributes())
	);
		printf("<div>%s</div>\n", 'prdouct ');
	printf( '<pre>%s</pre>', 
		print_r($product)
	);
	printf("<div>%s</div>\n", 'post');
	printf( '<pre>%s</pre>', 
		print_r($post)
	);
printf("<div>%s</div>\n", 'ALtro');
		printf( '<pre> %s</pre>', 
		var_dump(wp_get_object_terms( $product->id,'pa_cognome'))
	);
	printf( '<pre>%s</pre>', 
		var_dump(wp_get_object_terms( $post->ID,'pa_cognome'))
	);

		printf( '<pre>%s</pre>', 
		var_dump($product->get_attribute('pa_isbn'))
	);
		$terms=get_terms('pa_cognome','orderby=name&hide_empty=0');

    $result = array();
    foreach ($terms as $term )
    {
    	$data=$term->name;
        $result[] = array('value' => $data, 'label' => $data);
    }
    
		printf( '<pre>%s</pre>', 
		var_dump($result)
	);
*/
}
