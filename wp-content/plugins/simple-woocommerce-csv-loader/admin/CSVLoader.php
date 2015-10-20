<?php

/* Modified for Woocommerce */
if(!function_exists('_log')){
  function _log( $message ) {
    if( WP_DEBUG === true ){
      if( is_array( $message ) || is_object( $message ) ){
        error_log( print_r( $message, true ) );
      } else {
        error_log( $message );
      }
    }
  }
}
function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}
function wp_exist_post_by_title($title_str) {
global $wpdb;
return $wpdb->get_row(
   $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_title = %s", $title_str), 'ARRAY_A'
    );
}
function wc_exist_product_by_sku ( $sku ) {

  global $wpdb;



  $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );

  if ( $product_id ) return get_post( $product_id, 'ARRAY_A');



}

if ( ! session_id() ) session_start();
$post_type    = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : 'product';
$separator    = isset( $_REQUEST['separator'] ) ? $_REQUEST['separator'] : ';';
$titeled    = isset( $_REQUEST['titeled'] );
$taxonomy    = isset( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : 'product_cat';

$hierarchical_multicat = isset( $_REQUEST['hierarchical_multicat'] );

$cat        = isset( $_REQUEST['wc_cat'] ) ? $_REQUEST['wc_cat'] : '';
$wc_status    = isset( $_REQUEST['wc_status'] ) ? $_REQUEST['wc_status'] : '';
$book_type    = isset( $_REQUEST['book_type'] ) ? $_REQUEST['book_type'] : 'normale';

$data    = array();
$titles    = array();
if ( isset( $_REQUEST['wc_load_csv'] ) && isset( $_FILES['upload_file'] ) ) {
    if ( ( $handle = fopen( $_FILES['upload_file']['tmp_name'], 'r' ) ) !== FALSE ) {
        $n=0;
        $x=0;
        while ( ( $line = fgetcsv($handle, 1024, $separator ) ) !== FALSE ){
            
            
            $data[$n][] = $line;
            $x++;
            if($x===100){$x=0;$n++;}
            

        }
            
        fclose( $handle );
        if ( $titeled ) {
            $titles = $data[0][0];
            unset( $data[0][0] );
        } else { 
            for( $i = 0; $i < count( $data[0] ); $i++ )
                $titles[] = 'col_' . $i;
        }
    }
    $_SESSION['wc_csv_titles'] = $titles;
    $_SESSION['wc_csv_data'] = $data;
} elseif ( isset( $_REQUEST['wc_load_products_from_csv'] ) && isset( $_SESSION['wc_csv_titles'] ) ) {
    $titles = $_SESSION['wc_csv_titles'];
    $data = $_SESSION['wc_csv_data'];
    unset( $_SESSION['wc_csv_titles'] );
    unset( $_SESSION['wc_csv_data'] );
    if ( is_array( $data ) ) {
        $taxonomies = get_object_taxonomies( $post_type );
        $count = 0;
        $i = 0;
    foreach( $data as $batch ) {
        foreach( $batch as $cols ) {
            $i++;
            $name = '';
            $content = '';
            $excerpt = '';
            $price = 0;
            $order = '';
            $weight = 0;
            $sku = '';
            $stock = '';
            $tax = 0;
            $attachments = array();
            $thumbnail = '';
            $custom_values = array();
            $taxo_values = array();
            //
            $multi_cat_value = array();
            $taxo_attribs = array();
            //
            
            foreach( $cols as $i => $col ) {
                $col_name = isset( $_REQUEST['col_' . $i] ) ? $_REQUEST['col_' . $i] : '';
                
                if ( $col_name == 'wc_name' ) {
                    $name = $col;
                } elseif ( $col_name == 'wc_content' ) {
                    $content = $col;
                } elseif ( $col_name == 'wc_excerpt' ) {
                    $excerpt = $col;
                } elseif ( $col_name == 'wc_price' ) {
                    $price = $col;
                } elseif ( $col_name == 'wc_order' ) {
                    $order = $col;
                } elseif ( $col_name == 'wc_weight' ) {
                    $weight = (float)$col;
                } elseif ( $col_name == 'wc_sku' ) {
                    $sku = $col;
                } elseif ( $col_name == 'wc_stock' ) {
                    $stock = (int)$col;
                } elseif ( $col_name == 'wc_tax' ) {
                    $tax = (int)$col;
                    //
                    } elseif ( $col_name == 'multi_cat' ) {
                    $multi_cat = $col;
                    //
                } elseif ( $col_name == 'wc_attachment' ) {
                    $attachments[] = $col;
                } elseif ( $col_name == 'wc_thumbnail' ) {
                    $thumbnail = $col;
                    //
                    } elseif ( $col_name == 'attribs' ) {
                    $taxo_attribs = $col;
                    //

                }elseif(startsWith($col_name, 'wc_tax_')){
                    
                        foreach( $taxonomies as $taxmy ) {
                            if ( $col_name == 'wc_tax_' . $taxmy ) {
                                $taxo_values[$taxmy] = $col;
                                $break = true;
                                break;
                            }
                        }
                } else {
                    $break = false;
                    if ( is_array( $custom_field_defs ) && count( $custom_field_defs ) > 0 ) {
                        foreach( $custom_field_defs as $custom_field_def ) {
                            if ( $col_name == $custom_field_def['id'] ) {
                                $custom_values[$col_name] = $col;
                                $break = true;
                                break;
                            }
                        }
                    }
                    if ( ! $break && is_array( $taxonomies ) && count( $taxonomies ) > 0 ) {
                        
                        foreach( $taxonomies as $taxmy ) {
                            if ( $col_name == 'wc_tax_' . $taxmy ) {
                                $taxo_values[$taxmy] = $col;
                                $break = true;
                                break;
                            }
                        }
                    }
                }
            }
            
            
            $post = array(
                'comment_status'=> 'open',
                'post_content'    => $content,
                'post_excerpt'    => $excerpt,
                'post_status'    => $wc_status,
                'post_title'    => $name,
                'post_type'        => $post_type,
            );
            if ( wc_exist_product_by_sku($sku) ) {
                //ID for post we want update
                _log($name.' already exist');
                
                $idpost = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );
                $post['ID'] = $idpost; 
                $post_id = wp_update_post( $post );
                $count--; //For update count
            } else {
                $post_id = wp_insert_post( $post );
            }
            $product = wc_get_product( $post_id );
            $product_ID = $product->id;
            if ( $cat > 0 ) {
                wp_set_object_terms( $post_id, (int)$cat, $taxonomy, false );
            }

            update_post_meta( $post_id, '_visibility', 'visible' );
            update_post_meta( $post_id, '_sku', $sku );
            update_post_meta( $post_id, '_price', $price );
            update_post_meta( $post_id, '_weight', $weight );
            update_post_meta( $post_id, '_stock', $stock );
            update_post_meta( $post_id, '_featured', '' );
            update_post_meta( $post_id, '_regular_price', $price );
            update_post_meta( $post_id, '_sale_price', '' );
            update_post_meta( $post_id, '_sale_price_dates_from', '' );
            update_post_meta( $post_id, '_sale_price_dates_to', '' );

            //****** need to make a product_data and variation post insert ******//
            //update_post_meta( $post_id, 'wc_tax_id', $tax );
            //update_post_meta( $post_id, 'wc_is_downloadable', false );
            //update_post_meta( $post_id, 'wc_max_downloads', 0 );
            //update_post_meta( $post_id, 'wc_days_to_expire', 0 );
            //update_post_meta( $post_id, 'wc_type', 'SIMPLE' );
            //update_post_meta( $post_id, 'wc_weight', $weight );
            //update_post_meta( $post_id, 'wc_order', $order );

            ////

            $pdata=array();
$pdata['sku'] = $sku;
$pdata['regular_price'] = $price;
$pdata['sale_price'] = '';
$pdata['featured'] = '';
$pdata['weight'] = $weight;
$pdata['tax_status'] = $taxable;
$pdata['tax_class'] = '';
$pdata['stock_status'] = 'instock';
$pdata['manage_stock'] = 'no';
$pdata['backorders'] = 'no';
update_post_meta( $post_id, 'product_data', $pdata );




            //
$field= $taxo_attribs;
if((!empty($field)) && (explode(',',$field) !=FALSE))
{
$datas=explode(',',$field);
$attrib=array();
for($i=0;$i<count($datas);++$i)
{
$value=explode(':',$datas[$i]);
if (!empty($value[0]))
{
                if ( taxonomy_exists('pa_'.sanitize_title($value[0])) )
                {
                } else
                {
register_taxonomy( 'pa_'.sanitize_title($value[0]), 'post', array( 'hierarchical' => false, 'label' => 'pa_'.sanitize_title($value[0]) ) );

 $wpdb->insert( $wpdb->prefix . "woocommerce_attribute_taxonomies", array( 'attribute_name' => $value[0], 'attribute_type' => 'text' ), array( '%s', '%s' ) );
}



if(term_exists($value[1], 'pa_'.sanitize_title($value[0])))
{ }
else {
wp_insert_term(    $value[1], 'pa_'.sanitize_title($value[0]), array( 'slug' => $value[1] ) );
}
                wp_set_object_terms( $product_ID, $value[1], 'pa_'.sanitize_title($value[0]), true );

/*
$term = $value[1];
$tax = 'pa_'.sanitize_title($value[0]);
$new_term = term_exists($term, $tax);
                if ( ! is_array( $new_term ) )
                    $new_term = wp_insert_term(    $term , 'pa_'.sanitize_title($value[0]), array( 'slug' => $term ) );
                wp_set_object_terms( $post_id, $term, $tax, true );
*/


$value_sanitized = sanitize_title($value[0]);
$attrib[$value_sanitized]=
array('name' =>  htmlspecialchars(stripslashes($value[0])),
'value' => $value[1],
'position' => '0',
'visible' => 'yes',
'variation' => 'no',
'is_taxonomy' => 'yes'
);
}
update_post_meta($product_ID, '_product_attributes', $attrib);
}


            foreach( $custom_values as $id => $custom_value ) {
                update_post_meta( $post_id, $id, $custom_value );
            }
            }
/////// explode field multicat ',' separator

$prod_cats = explode(',',$multi_cat);
for($i=0;$i<count($prod_cats);++$i)
{
                $new_cat = term_exists( $prod_cats[$i], 'product_cat' );
                if ( ! is_array( $new_cat ) ) {
                    wp_insert_term(    $prod_cats[$i], 'product_cat', array( 'slug' => $prod_cats[$i], 'parent'=> $parent) );
                    $new_cat = term_exists( $prod_cats[$i], 'product_cat' );
                    }
                    
                if($hierarchical_multicat) {
                    $parent = $new_cat['term_id'];
                }
                
                wp_set_object_terms( $post_id, (int)$new_cat['term_id'], 'product_cat', true );
                
                delete_option("product_cat_children"); 
            }
            unset($parent);

    $attrib=array();
      $clmc_meta=array();
  $clmc_meta['tax']=array();  

  $clmc_meta['tax']['pa_isbn']='clmc_book_meta.group1.0.fixed_isbn.0.pa_isbn';  
  $clmc_meta['tax']['pa_editore']='clmc_book_meta.group1.0.fixed_editore.0.pa_editore';
  $clmc_meta['tax']['pa_a_publ']='clmc_book_meta.group1.0.fixed_a_publ.0.pa_a_publ';
  $clmc_meta['tax']['pa_lingua']='clmc_book_meta.group1.0.fixed_lingua.0.pa_lingua';
  $clmc_meta['tax']['pa_condizione']='clmc_book_meta.group1.0.pa_condizione';
  $clmc_meta['tax']['pa_formato']='clmc_book_meta.group1.0.fixed_formato.0.pa_formato';
  $clmc_meta['tax']['pa_collana']='clmc_book_meta.group1.0.fixed_collana.0.pa_collana';
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
  $clmc_meta['ntax']['pa_deposito']='clmc_book_meta.group1.0.pa_deposito';

    foreach( $taxo_values as $tax => $term ) {



                //$new_term = term_exists( $term, $tax );
                //if ( ! is_array( $new_term ) )
                //$new_term = wp_insert_term(    $term, $tax, array( 'slug' => $term ) );
        if(array_key_exists($tax,$clmc_meta['tax'])){
        wp_set_object_terms( $product_ID, $term, $tax, true );
            $value_sanitized = sanitize_title($tax);
        $attrib[$value_sanitized]=
            array(  'name' =>  htmlspecialchars(stripslashes($value_sanitized)),
                    'value' => $term,
                    'position' => '0',
                    'visible' => true,
                    'variation' => false,
                    'is_taxonomy' => true
            );
        }
        elseif($tax=='pa_autore_catalogo'){
            wp_set_object_terms( $product_ID, $term, 'pa_autore_catalogo', true );
            $value_sanitized = sanitize_title('pa_autore_catalogo');
        $attrib[$value_sanitized]=
                array(  'name' =>  htmlspecialchars(stripslashes($value_sanitized)),
                    'value' => $term,
                    'position' => '0',
                    'visible' => true,
                    'variation' => false,
                    'is_taxonomy' => true);

        }elseif($tax=='product_cat'){
               $new_term = term_exists( $term, $tax );
                if ( ! is_array( $new_term ) )
                    $new_term = wp_insert_term(    $term, $tax, array( 'slug' => $term ) );
                wp_set_object_terms( $post_id, (int)$new_term['term_id'], $tax, true );
        }
            else{
            $value_sanitized = sanitize_title($tax);
        $attrib[$value_sanitized]=
                array(  'name' =>  htmlspecialchars(stripslashes($value_sanitized)),
                    'value' => $term,
                    'position' => '0',
                    'visible' => true,
                    'variation' => false,
                    'is_taxonomy' => false);
        }
    }
            
wp_set_object_terms( $product_ID, '1', 'pa_deposito', true );
$value_sanitized = sanitize_title('pa_deposito');
        $attrib[$value_sanitized]=
                array(  'name' =>  htmlspecialchars(stripslashes($value_sanitized)),
                    'value' => '1',
                    'position' => '0',
                    'visible' => true,
                    'variation' => false,
                    'is_taxonomy' => true);
        
        wp_set_object_terms( $product_ID, $book_type, 'pa_libro_type', true );
$value_sanitized = sanitize_title('pa_libro_type');
        $attrib['pa_libro_type']=
                array(  'name' =>  'pa_libro_type',
                    'value' => $book_type,
                    'position' => '0',
                    'visible' => true,
                    'variation' => false,
                    'is_taxonomy' => true);
        
    update_post_meta($product_ID, '_product_attributes', $attrib);
        

 






            foreach( $attachments as $url ) {
                //$url = urldecode( $url );
                $base = basename( $url );
                $path = wp_upload_dir();
                $path = $path['path'];
                $dest = $path . '/' . $base;
                copy( $url, $dest );
                $wp_filetype = wp_check_filetype( basename( $dest ), null );
                $attachment = array(
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename( $dest ) ),
                    'post_content' => '',
                    'post_status' => 'inherit',
                );
                $attach_id = wp_insert_attachment( $attachment, $dest, $post_id );
                // you must first include the image.php file for the function wp_generate_attachment_metadata() to work
                require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                $attach_data = wp_generate_attachment_metadata( $attach_id, $dest );
                wp_update_attachment_metadata( $attach_id,  $attach_data );
            }

            if ( strlen( $thumbnail ) > 0 ) {
                $base = basename( $thumbnail );
                $path = wp_upload_dir();
                $path = $path['path'];
                $dest = $path . '/' . $base;
                copy( $thumbnail, $dest );
                $wp_filetype = wp_check_filetype( basename( $dest ), null );
                $attachment = array(
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename( $dest ) ),
                    'post_content' => '',
                    'post_status' => 'inherit',
                );
                $attach_id = wp_insert_attachment( $attachment, $dest, $post_id );
                // you must first include the image.php file for the function wp_generate_attachment_metadata() to work
                require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                $attach_data = wp_generate_attachment_metadata( $attach_id, $dest );
                wp_update_attachment_metadata( $attach_id,  $attach_data );
                update_post_meta( $post_id, '_thumbnail_id', $attach_id );
            }
            $count++;
            $uploaded++;
        }
	}
}
        ?>
        <div id="message" class="updated"><p>
            <?php printf( __( '%s of %s products have been uploaded', 'wc_csvl' ), $count, $uploaded );?>            
        </p></div><?php
    } else { ?>
        <div id="message" class="error"><p>
            <?php _e( 'No product has been uploaded', 'wc_csvl' );?>
        </p></div><?php
    }

?>
<div class="wrap">
    <div style="width: 200px; right: 0; float: right; position: fixed; margin: 0 15px 20px 0; background: #fff; border: 1px solid #e9e9e9; padding: 5px 5px 5px 5px; color: #008000; font-size: 11px;">
<h3 style="margin: 0 0 10px 0; border-bottom: 1px dashed #008000;">More Free Plugins:</h3>
<ul>
    <li><a href="http://freebloggingtricks.com/wp-slick-tab/" target="_blank">WP Slick Tab</a></li>
    <li><a href="http://freebloggingtricks.com/really-simple-guest-post-plugin/" target="_blank">Really Simple Guest Post Plugin</a></li>
    <li><a href="http://freebloggingtricks.com/facebooktwittergoogle-plus-linkedin-buffer-share-buttons-wordpress/" target="_blank">WordPress Social Share Plugin</a></li>
    <li><a href="http://freebloggingtricks.com/import-csv-products-jigoshop/" target="_blank">Jigoshop CSV Importer</a></li>
    <li><a href="http://freebloggingtricks.com/hide-comment-author-link/">Hide Comment Author Link</a></li>
</ul>
<h3 style="margin: 0 0 10px 0; border-bottom: 1px dashed #008000;">Check Our Main Site:</h3>
Check <a href="http://freebloggingtricks.com/">FreeBloggingTricks</a> for WordPress tutorials. Don't forget to follow us on <a href="http://www.twitter.com/freebtricks">Twitter</a>, <a href="http://facebook.com/freebloggingtricks">Facebook</a> and <a href="https://plus.google.com/109526129815752833990/">Google+</a>.

</div>    
<h1><?php echo __( 'Simple WooCommerce CSV Loader', 'wc_csvl' );?></h1>
</color>
<ul class="subsubsub">
</ul><!-- subsubsub -->

<div class="clear"></div>

<form method="post" enctype="multipart/form-data">
    <table class="form-table">
    <tbody>
    <tr valign="top" hidden>
    <th scope="row">
        <label for="post_type" hidden ><?php _e( 'Post type', 'tcp' )?>:</label>
    </th>
    <td>
        <select name="post_type" id="post_type" hidden>

            <option value="product">Product</option>

        </select >
        <input type="submit" name="wc_load_taxonomies" value="<?php _e( 'Load taxonomies', 'tcp' );?>" class="button-secondary" hidden />
    </td>
    </tr>
    <tr valign="top" hidden>
    <th scope="row">
        <label for="taxonomy"><?php _e( 'Taxonomy by default (product tag is the best)', 'tcp' )?>:</label>
    </th>
    <td>
        <select name="taxonomy" id="taxonomy">
        <?php foreach( get_object_taxonomies( $post_type ) as $taxmy ) : $tax = get_taxonomy( $taxmy );
if (preg_match('/pa_/', $taxmy) == 0)  {?>
        <option value="<?php echo esc_attr( $taxmy );?>"<?php selected( $taxmy, $taxonomy ); ?>><?php echo $tax->labels->name;?></option>
        <?php } ?>
        <?php endforeach;?>
        </select>
    </td>
    </tr>
    <tr valign="top" hidden>
    <th scope="row">
        <label for="separator"><?php _e( 'Separator', 'wc_csvl' );?>:</label>
    </th>
    <td>
        <input type="text" name="separator" id="separator" value="<?php echo $separator;?>" size="2" maxlenght="4" hidden/>
        <label for="titeled" hidden ><?php _e( 'Columns title in first line', 'wc_csvl' );?>:</label>
        <input type="checkbox" name="titeled" id="titeled" <?php checked($titeled);?> size="2" maxlenght="4" checked hidden/>
    </td>
    </tr>

    <tr valign="top" hidden>
    <th scope="row">
        <label for="hierarchical_multicat" hidden><?php _e( 'Hierarchical categories', 'wc_csvl' );?>:</label>
    </th>
    <td>
        <label for="titeled" hidden><?php _e( 'Hierarchical Categories', 'wc_csvl' );?>:</label>
        <input type="checkbox" name="hierarchical_multicat" id="hierarchical_multicat" <?php checked($hierarchical_multicat);?> size="2" maxlenght="4" hidden />
    </td>
    </tr>

    <tr valign="top">
    <th scope="row">
        <label for="upload_file" value="" ><?php _e( 'file', 'wc_csvl' );?>:</label>
    </th>
    <td>
        <input type="file" name="upload_file" id="upload_file" />
    </td>
    </tr>
        </tr>

    <tr valign="top">
    <th scope="row">
        <label for="book_type" value="" >Tipo Catalogazione:</label>
    </th>
    <td>
        <select name="book_type" id="book_type">
        <option value="normale">Normale</option>
        <option value="ebay">Ebay</option>
        <option value="dd">Dante & Descartes</option>
        <option value="catalogo">Catalogo</option>
        </select>
        
    </td>
    </tr>
    </tbody>
    </table>
    <span class="submit"><input type="submit" name="wc_load_csv" id="wc_load_csv" value="<?php _e( 'Load', 'wc_csvl' );?>" style="button-secondary" /></span>
    <span><?php _e( 'This action helps you to test if the file is correct. Only 4 rows will be displayed.', 'wc_csvl' );?></span>
</form>
<?php if ( is_array( $data ) && count( $data ) > 0 ) : ?>
<p><?php _e( 'These lines are the four first products loaded from the CSV file. If you think they are correct continue with the process.', 'wc_csvl' );?></p>
<table class="widefat fixed" cellspacing="0">
    <?php if ( is_array( $titles ) && count( $titles ) > 0 ) :?>
        <thead>
        <tr scope="col" class="manage-column"><th>&nbsp;</th>
        <?php foreach( $titles as $col ) : ?>
            <th><?php echo $col;?></th>
        <?php endforeach;?>
        </tr>
        </thead>
        <tfoot>
        <tr scope="col" class="manage-column"><th>&nbsp;</th>
        <?php foreach( $titles as $col ) : ?>
            <th><?php echo $col;?></th>
        <?php endforeach;?>
        </tr>
        </tfoot>
    <?php endif;?>
        <tbody>
        <?php foreach( $data[0] as $i => $cols ) :
            if ( $i > 4 ) :
                break;
            else : ?>
                <tr>
                    <td><?php echo  $i;?></td>
                <?php foreach( $cols as $col ) : ?>
                    <td><?php echo $col;?></td>
                <?php endforeach;?>
                </tr>
            <?php endif;?>
        <?php endforeach;?>
    </tbody>
</table>
<p><?php _e( 'Assign the columns of the CSV file (left column) to the fields of the products (right column).', 'wc_csvl' );?></p>
<form method="post">
<input type="hidden" name="post_type" value="<?php echo $post_type;?>" />
<input type="hidden" name="book_type" value="<?php echo $book_type;?>" />
<input type="hidden" name="taxonomy" value="<?php echo $taxonomy;?>" />
<input type="hidden" name="separator" value="<?php echo isset( $_REQUEST['separator'] ) ? $_REQUEST['separator'] : '|';?>" />
<?php if ( isset( $_REQUEST['titeled'] ) ) :?>
<input type="hidden" name="titeled" value="y"/>
<?php endif;?>
<?php if ( isset( $_REQUEST['hierarchical_multicat'] ) ) :?>
<input type="hidden" name="hierarchical_multicat" value="y"/>
<?php endif;?>
<table class="widefat fixed" cellspacing="0">
<thead>
    <tr scope="col" class="manage-column">
        <th><?php _e( 'Imported columns', 'wc_csvl' );?></th>
        <th><?php _e( 'Woocommerce columns', 'wc_csvl' );?></th>
    </tr>
</thead>
<tfoot>
    <tr scope="col" class="manage-column">
        <th><?php _e( 'CSV columns', 'wc_csvl' );?></th>
        <th><?php _e( 'Woocommerce columns', 'wc_csvl' );?></th>
    </tr>
</tfoot>
<tbody>
<?php if ( is_array( $titles ) && count( $titles ) > 0 ) : ?>
    <?php foreach( $titles as $i => $col ) : ?>
        <tr>
            <td><?php echo $col;?></td>
            <td>
            <select name="col_<?php echo $i;?>">
                <option value=""><?php _e( 'None', 'wc_csvl' );?></option>
                <option value="wc_name" <?php selected( strtoupper( $col ), 'NAME');?>>Title (<?php _e( 'Title', 'wc_csvl' );?>)</option>
                <option value="wc_content" <?php selected( strtoupper( $col ), 'CONTENT');?>>Content (<?php _e( 'Content', 'wc_csvl' );?>)</option>
                <option value="wc_excerpt" <?php selected( strtoupper( $col ), 'EXCERPT');?>>Excerpt (<?php _e( 'Excerpt', 'wc_csvl' );?>)</option>
                <option value="wc_price" <?php selected( strtoupper( $col ), 'PRICE');?>>Price (<?php _e( 'Price', 'wc_csvl' );?>)</option>
                <option value="wc_stock" <?php selected( strtoupper( $col ), 'STOCK');?>>Stock (<?php _e( 'Stock', 'wc_csvl' );?>)</option>
                <option value="wc_weight" <?php selected( strtoupper( $col ), 'WEIGHT');?>>Weight (<?php _e( 'Weight', 'wc_csvl' );?>)</option>
                <option value="wc_sku" <?php selected( strtoupper( $col ), 'SKU');?>>SKU (<?php _e( 'SKU', 'wc_csvl' );?>)</option>
                <option value="wc_order" <?php selected( strtoupper( $col ), 'ORDER');?>>Order (<?php _e( 'Order', 'wc_csvl' );?>)</option>
                <option value="wc_tax" <?php selected( strtoupper( $col ), 'TAX');?>>Tax (<?php _e( 'Tax', 'wc_csvl' );?>)</option>
                <option value="wc_attachment" <?php selected( strtoupper( $col ), 'ATTACHMENT');?>>Attachment (<?php _e( 'Attachment', 'wc_csvl' );?>)</option>
                <option value="wc_thumbnail" <?php selected( strtoupper( $col ), 'THUMBNAIL');?>>Thumbnail (<?php _e( 'Thumbnail', 'wc_csvl' );?>)</option>

                <option value="multi_cat" <?php selected( strtoupper( $col ), 'MULTI_CAT');?>>MultiCat (<?php _e( 'multi_cat', 'multi_cat' );?>)</option>

                <option value="attribs" <?php selected( strtoupper( $col ), 'ATTRIBS');?>>Attrib (<?php _e( 'Attribs', 'attribs' );?>)</option>

                <?php foreach( get_object_taxonomies( $post_type ) as $taxmy ) : $tax = get_taxonomy( $taxmy ); ?>
                <option value="wc_tax_<?php echo $taxmy;?>">T <?php echo $tax->labels->name;?></option>
                <?php endforeach;?>


            </select>
            </td>
        </tr>
    <?php endforeach;?>
<?php endif;?>
</tbody>
</table>

<p>
    <label for="wc_status"><?php _e( 'Set products status to', 'wc_csvl' )?>:</label>
    <select id="wc_status" name="wc_status">
        <option value="publish"><?php _e( 'publish', 'wc_cvsl' );?></option>
        <option value="draft"><?php _e( 'draft', 'wc_cvsl' );?></option>
    </select>
</p>
<span class="submit">
    <input type="submit" name="wc_load_products_from_csv" id="wc_load_products_from_csv" value="<?php _e( 'Upload', 'wc_csvl' );?>" class="button-primary" />
    <span><?php _e( 'This action will load the products in the eCommerce. Be patient.', 'wc_csvl' );?></span>
</span>
</form>
<?php endif;?>
</div>

