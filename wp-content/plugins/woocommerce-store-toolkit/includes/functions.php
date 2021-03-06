<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	include_once( WOO_ST_PATH . 'includes/admin.php' );

	// WordPress Administration menu
	function woo_st_admin_menu() {

		add_submenu_page( 'woocommerce', __( 'Store Toolkit', 'woo_st' ), __( 'Store Toolkit', 'woo_st' ), 'manage_woocommerce', 'woo_st', 'woo_st_html_page' );

	}
	add_action( 'admin_menu', 'woo_st_admin_menu', 11 );

	function woo_st_template_header( $title = '', $icon = 'woocommerce' ) {

		if( $title )
			$output = $title;
		else
			$output = __( 'Store Toolkit', 'woo_st' ); ?>
<div class="wrap">
	<div id="icon-<?php echo $icon; ?>" class="icon32 icon32-woocommerce-settings"><br /></div>
	<h2><?php echo $output; ?></h2>
<?php
	}

	function woo_st_template_footer() { ?>
</div>
<?php
	}

	function woo_st_support_donate() {

		$output = '';
		$show = true;
		if( function_exists( 'woo_vl_we_love_your_plugins' ) ) {
			if( in_array( WOO_ST_DIRNAME, woo_vl_we_love_your_plugins() ) )
				$show = false;
		}
		if( $show ) {
			$donate_url = 'http://www.visser.com.au/donate/';
			$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . WOO_ST_DIRNAME;
			$output = '
	<div id="support-donate_rate" class="support-donate_rate">
		<p>' . sprintf( __( '<strong>Like this Plugin?</strong> %s and %s.', 'woo_st' ), '<a href="' . $donate_url . '" target="_blank">' . __( 'Donate to support this Plugin', 'woo_st' ) . '</a>', '<a href="' . add_query_arg( array( 'rate' => '5' ), $rate_url ) . '#postform" target="_blank">rate / review us on WordPress.org</a>' ) . '</p>
	</div>
';
		}
		echo $output;

	}

	// Returns number of an Export type prior to nuke, used on Store Toolkit screen
	function woo_st_return_count( $export_type = '' ) {

		global $wpdb;

		$count_sql = null;
		switch( $export_type ) {

			// WooCommerce

			case 'product':
				$post_type = array( 'product', 'product_variation' );
				$args = array(
					'post_type' => $post_type,
					'posts_per_page' => 1
				);
				$query = new WP_Query( $args );
				$count = $query->found_posts;
				break;

			case 'product_image':
				$count_sql = "SELECT COUNT(`post_id`) FROM `" . $wpdb->postmeta . "` WHERE `meta_key` = '_woocommerce_exclude_image'";
				break;

			case 'product_category':
				$term_taxonomy = 'product_cat';
				if( taxonomy_exists( $term_taxonomy ) )
					$count = wp_count_terms( $term_taxonomy );
				break;

			case 'product_tag':
				$term_taxonomy = 'product_tag';
				if( taxonomy_exists( $term_taxonomy ) )
					$count = wp_count_terms( $term_taxonomy );
				break;

			case 'product_brand':
				$term_taxonomy = 'product_brand';
				if( taxonomy_exists( $term_taxonomy ) )
					$count = wp_count_terms( $term_taxonomy );
				break;

			case 'product_vendor':
				$term_taxonomy = 'shop_vendor';
				if( taxonomy_exists( $term_taxonomy ) )
					$count = wp_count_terms( $term_taxonomy );
				break;

			case 'order':
				$post_type = 'shop_order';
				if( post_type_exists( $post_type ) )
					$count = wp_count_posts( $post_type );
				break;

			case 'tax_rate':
				$count_sql = "SELECT COUNT(`tax_rate_id`) FROM `" . $wpdb->prefix . "woocommerce_tax_rates`";
				break;

			case 'download_permission':
				$count_sql = "SELECT COUNT(`download_id`) FROM `" . $wpdb->prefix . "woocommerce_downloadable_product_permissions`";
				break;

			case 'coupon':
				$post_type = 'shop_coupon';
				if( post_type_exists( $post_type ) )
					$count = wp_count_posts( $post_type );
				break;

			case 'attribute':
				$count_sql = "SELECT COUNT(`attribute_id`) FROM `" . $wpdb->prefix . "woocommerce_attribute_taxonomies`";
				break;

			// 3rd Party

			case 'credit_card':
				$post_type = 'offline_payment';
				if( post_type_exists( $post_type ) )
					$count = wp_count_posts( $post_type );
				break;

			// WordPress

			case 'post':
				$post_type = 'post';
				if( post_type_exists( $post_type ) )
					$count = wp_count_posts( $post_type );
				break;

			case 'post_category':
				$term_taxonomy = 'category';
				if( taxonomy_exists( $term_taxonomy ) )
					$count = wp_count_terms( $term_taxonomy );
				break;

			case 'post_tag':
				$term_taxonomy = 'post_tag';
				if( taxonomy_exists( $term_taxonomy ) )
					$count = wp_count_terms( $term_taxonomy );
				break;

			case 'link':
				$count_sql = "SELECT COUNT(`link_id`) FROM `" . $wpdb->prefix . "links`";
				break;

			case 'comment':
				$count = wp_count_comments();
				break;

			case 'media_image':
				$count_sql = "SELECT COUNT(`ID`) FROM `" . $wpdb->posts . "` WHERE `post_mime_type` LIKE 'image%'";
				break;

		}
		if( isset( $count ) || $count_sql ) {
			if( isset( $count ) ) {
				if( is_object( $count ) ) {
					$count_object = $count;
					$count = 0;
					foreach( $count_object as $key => $item )
						$count = $item + $count;
				}
				return $count;
			} else if( !empty( $count_sql ) ) {
				$count = $wpdb->get_var( $count_sql );
			} else {
				$count = 0;
			}
			return $count;
		} else {
			return 0;
		}

	}

	function woo_st_clear_dataset( $export_type = '', $data = false ) {

		global $wpdb;

		if( empty( $export_type ) )
			return false;

		// Commence the drop
		woo_st_update_option( 'in_progress', $export_type );
		switch( $export_type ) {

			// WooCommerce

			case 'product':
				$post_type = array( 'product', 'product_variation' );
				$args = array(
					'post_type' => $post_type,
					'fields' => 'ids',
					'post_status' => woo_st_post_statuses(),
					'numberposts' => 100
				);
				// Loop through every 100 records until 0 is returned, might take awhile
				while( woo_st_return_count( 'product' ) ) {
					$products = get_posts( $args );
					if( !empty( $products ) ) {
						foreach( $products as $product ) {
							wp_delete_post( $product, true );
							// Product Category
							if( taxonomy_exists( 'product_cat' ) )
								wp_set_object_terms( $product, null, 'product_cat' );
							// Product Tag
							if( taxonomy_exists( 'product_tag' ) )
								wp_set_object_terms( $product, null, 'product_tag' );
							// Product Brand
							if( taxonomy_exists( 'product_brand' ) )
								wp_set_object_terms( $product, null, 'product_brand' );
							// Product Vendor
							if( taxonomy_exists( 'shop_vendor' ) )
								wp_set_object_terms( $product, null, 'shop_vendor' );
							// Attributes
							$attributes_sql = "SELECT `attribute_id` as ID, `attribute_name` as name, `attribute_label` as label, `attribute_type` as type FROM `" . $wpdb->prefix . "woocommerce_attribute_taxonomies`";
							$attributes = $wpdb->get_results( $attributes_sql );
							if( !empty( $attributes ) ) {
								foreach( $attributes as $attribute ) {
									if( taxonomy_exists( 'pa_' . $attribute->name ) )
										wp_set_object_terms( $product, null, 'pa_' . $attribute->name );
								}
							}
						}
						unset( $products, $product, $attributes, $attribute );
					}
				}
				break;

			case 'product_category':
				$term_taxonomy = 'product_cat';
				if( !empty( $data ) ) {
					foreach( $data as $single_category ) {
						$post_type = 'product';
						$args = array(
							'post_type' => $post_type,
							'fields' => 'ids',
							'tax_query' => array(
								array(
									'taxonomy' => $term_taxonomy,
									'field' => 'id',
									'terms' => $single_category
								)
							),
							'numberposts' => -1
						);
						$products = get_posts( $args );
						if( $products ) {
							foreach( $products as $product )
								wp_delete_post( $product, true );
							unset( $products, $product );
						}
					}
					unset( $data, $single_category );
				} else {
					$args = array(
						'hide_empty' => false,
						'number' => 100
					);
					// Loop through every 100 records until 0 is returned, might take awhile
					while( woo_st_return_count( 'product_category' ) ) {
						$categories = get_terms( $term_taxonomy, $args );
						if( !empty( $categories ) ) {
							foreach( $categories as $category ) {
								wp_delete_term( $category->term_id, $term_taxonomy );
								$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $wpdb->terms . "` WHERE `term_id` = %d", $category->term_id ) );
								$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $wpdb->term_relationships . "` WHERE `term_taxonomy_id` = %d", $category->term_taxonomy_id ) );
								$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $wpdb->prefix . "woocommerce_termmeta` WHERE `woocommerce_term_id` = %d", $category->term_id ) );
								if( function_exists( 'delete_woocommerce_term_meta' ) )
									delete_woocommerce_term_meta( $category->term_id, 'thumbnail_id' );
							}
							unset( $categories, $category );
						}
					}
					$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $wpdb->term_taxonomy . "` WHERE `taxonomy` = '%s'", $term_taxonomy ) );
				}
				break;

			case 'product_tag':
				$term_taxonomy = 'product_tag';
				$args = array(
					'fields' => 'ids',
					'hide_empty' => false,
					'number' => 100
				);
				// Loop through every 100 records until 0 is returned, might take awhile
				while( woo_st_return_count( 'product_tag' ) ) {
					$tags = get_terms( $term_taxonomy, $args );
					if( !empty( $tags ) ) {
						foreach( $tags as $tag ) {
							wp_delete_term( $tag, $term_taxonomy );
							$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $wpdb->terms . "` WHERE `term_id` = %d", $tag ) );
						}
					}
				}
				break;

			case 'product_brand':
				$term_taxonomy = 'product_brand';
				$args = array(
					'fields' => 'ids',
					'hide_empty' => false,
					'number' => 100
				);
				// Loop through every 100 records until 0 is returned, might take awhile
				while( woo_st_return_count( 'product_brand' ) ) {
					$tags = get_terms( $term_taxonomy, $args );
					if( $tags ) {
						foreach( $tags as $tag ) {
							wp_delete_term( $tag, $term_taxonomy );
							$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $wpdb->terms . "` WHERE `term_id` = %d", $tag ) );
						}
					}
				}
				break;

			case 'product_vendor':
				$term_taxonomy = 'shop_vendor';
				$args = array(
					'fields' => 'ids',
					'hide_empty' => false
				);
				// Loop through every 100 records until 0 is returned, might take awhile
				while( woo_st_return_count( 'product_vendor' ) ) {
					$tags = get_terms( $term_taxonomy, $args );
					if( $tags ) {
						foreach( $tags as $tag ) {
							wp_delete_term( $tag, $term_taxonomy );
							$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $wpdb->terms . "` WHERE `term_id` = %d", $tag ) );
						}
					}
				}
				break;

			case 'product_image':
				$post_type = array( 'product', 'product_variation' );
				$args = array(
					'post_type' => $post_type,
					'fields' => 'ids',
					'post_status' => 'any',
					'numberposts' => 100
				);
				// Loop through every 100 records until 0 is returned, might take awhile
				while( woo_st_return_count( 'product_image' ) ) {
					$products = get_posts( $args );
					// Check each Product for images
					if( !empty( $products ) ) {
						$upload_dir = wp_upload_dir();
						foreach( $products as $product ) {
							$args = array(
								'post_type' => 'attachment',
								'post_parent' => $product,
								'post_status' => 'inherit',
								'post_mime_type' => 'image',
								'numberposts' => -1
							);
							$images = get_children( $args );
							if( !empty( $images ) ) {
								foreach( $images as $image ) {
									wp_delete_attachment( $image->ID, true );
								}
								unset( $images, $image );
							}
						}
					} else {
						// Check for WooCommerce-related images
						$images_sql = "SELECT `post_id` AS `ID` FROM `" . $wpdb->postmeta . "` WHERE `meta_key` = '_woocommerce_exclude_image' AND `meta_value` = 0";
						$images = $wpdb->get_col( $images_sql );
						if( !empty( $images ) ) {
							foreach( $images as $image )
								wp_delete_attachment( $image, true );
							unset( $images, $image );
						}
					}
				}
				break;

			case 'order':
				$post_type = 'shop_order';
				$term_taxonomy = 'shop_order_status';
				$woocommerce_version = woo_get_woo_version();
				if( !empty( $data ) ) {
					foreach( $data as $single_order ) {
						$args = array(
							'post_type' => $post_type,
							'fields' => 'ids',
							'numberposts' => -1
						);
						// Check if this is a pre-WooCommerce 2.2 instance
						if( version_compare( $woocommerce_version, '2.2', '<' ) ) {
							$args['tax_query'] = array(
								array(
									'taxonomy' => $term_taxonomy,
									'field' => 'id',
									'terms' => $single_order
								)
							);
						} else {
							$args['status'] = 'any';
						}
						$orders = get_posts( $args );
						if( !empty( $orders ) ) {
							foreach( $orders as $order ) {
								wp_delete_post( $order, true );
								$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $wpdb->term_relationships . "` WHERE `order_id` = %d", $order ) );
							}
							unset( $orders, $order );
						}
					}
					unset( $data, $single_order );
				} else {
					$args = array(
						'post_type' => $post_type,
						'fields' => 'ids',
						'post_status' => 'any',
						'numberposts' => 100
					);
					// Loop through every 100 records until 0 is returned, might take awhile
					while( woo_st_return_count( 'order' ) ) {
						$orders = get_posts( $args );
						if( !empty( $orders ) ) {
							foreach( $orders as $order ) {
								wp_delete_post( $order, true );
								$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $wpdb->term_relationships . "` WHERE `order_id` = %d", $order ) );
							}
							unset( $orders, $order );
						}
					}
					$wpdb->query( "TRUNCATE TABLE `" . $wpdb->prefix . "woocommerce_order_items`" );
					$wpdb->query( "TRUNCATE TABLE `" . $wpdb->prefix . "woocommerce_order_itemmeta`" );
				}
				break;

			case 'tax_rate':
				$wpdb->query( "TRUNCATE TABLE `" . $wpdb->prefix . "woocommerce_tax_rates`" );
				$wpdb->query( "TRUNCATE TABLE `" . $wpdb->prefix . "woocommerce_tax_rate_locations`" );
				break;

			case 'download_permission':
				$wpdb->query( "TRUNCATE TABLE `" . $wpdb->prefix . "woocommerce_downloadable_product_permissions`" );
				break;

			case 'coupon':
				$post_type = 'shop_coupon';
				$args = array(
					'post_type' => $post_type,
					'fields' => 'ids',
					'post_status' => woo_st_post_statuses(),
					'numberposts' => 100
				);
				// Loop through every 100 records until 0 is returned, might take awhile
				while( woo_st_return_count( 'coupon' ) ) {
					$coupons = get_posts( $args );
					if( !empty( $coupons ) ) {
						foreach( $coupons as $coupon )
							wp_delete_post( $coupon, true );
						unset( $coupons, $coupon );
					}
				}
				break;

			case 'attribute':
				if( isset( $_POST['woo_st_attributes'] ) ) {
					$attributes_sql = "SELECT `attribute_id` as ID, `attribute_name` as name, `attribute_label` as label, `attribute_type` as type FROM `" . $wpdb->prefix . "woocommerce_attribute_taxonomies`";
					$attributes = $wpdb->get_results( $attributes_sql );
					if( $attributes ) {
						foreach( $attributes as $attribute ) {
							$terms_sql = $wpdb->prepare( "SELECT `term_id` FROM `" . $wpdb->prefix . "term_taxonomy` WHERE `taxonomy` = %s", 'pa_' . $attribute->name );
							$terms = $wpdb->get_results( $terms_sql );
							if( $terms ) {
								foreach( $terms as $term )
									wp_delete_term( $term->term_id, 'pa_' . $attribute->name );
							}
							$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $wpdb->prefix . "woocommerce_termmeta` WHERE `meta_key` = 'order_pa_%s'", $attribute->name ) );
							$wpdb->query( $wpdb->prepare( "DELETE FROM `" . $wpdb->term_relationships . "` WHERE `term_taxonomy_id` = %d", $attribute->ID ) );
						}
					}
					$wpdb->query( "DELETE FROM `" . $wpdb->prefix . "woocommerce_attribute_taxonomies`" );
				}
				break;

			// 3rd Party

			case 'credit_card':
				$post_type = 'offline_payment';
				$args = array( 
					'post_type' => $post_type,
					'fields' => 'ids',
					'post_status' => woo_st_post_statuses(),
					'numberposts' => 100
				);
				// Loop through every 100 records until 0 is returned, might take awhile
				while( woo_st_return_count( 'credit_card' ) ) {
					$credit_cards = get_posts( $args );
					if( !empty( $credit_cards ) ) {
						foreach( $credit_cards as $credit_card )
							wp_delete_post( $credit_card, true );
						unset( $credit_cards, $credit_card );
					}
				}
				break;

			// WordPress

			case 'post':
				$post_type = 'post';
				$args = array( 
					'post_type' => $post_type,
					'fields' => 'ids',
					'post_status' => woo_st_post_statuses(),
					'numberposts' => 100
				);
				// Loop through every 100 records until 0 is returned, might take awhile
				while( woo_st_return_count( 'post' ) ) {
					$posts = get_posts( $args );
					if( $posts ) {
						foreach( $posts as $post )
							wp_delete_post( $post, true );
						unset( $posts, $post );
					}
				}
				break;

			case 'post_category':
				$term_taxonomy = 'category';
				$args = array(
					'hide_empty' => false,
					'number' => 100
				);
				// Loop through every 100 records until 0 is returned, might take awhile
				while( woo_st_return_count( 'post_category' ) ) {
					$post_categories = get_terms( $term_taxonomy, $args );
					if( $post_categories ) {
						foreach( $post_categories as $post_category ) {
							wp_delete_term( $post_category->term_id, $term_taxonomy );
							$wpdb->query( "DELETE FROM `" . $wpdb->terms . "` WHERE `term_id` = " . $post_category->term_id );
							$wpdb->query( "DELETE FROM `" . $wpdb->term_relationships . "` WHERE `term_taxonomy_id` = " . $post_category->term_taxonomy_id );
						}
					}
				}
				$wpdb->query( "DELETE FROM `" . $wpdb->term_taxonomy . "` WHERE `taxonomy` = '" . $term_taxonomy . "'" );
				break;

			case 'post_tag':
				$term_taxonomy = 'post_tag';
				$args = array(
					'hide_empty' => false,
					'number' => 100
				);
				// Loop through every 100 records until 0 is returned, might take awhile
				while( woo_st_return_count( 'post_tag' ) ) {
					$post_tags = get_terms( $term_taxonomy, $args );
					if( $post_tags ) {
						foreach( $post_tags as $post_tag ) {
							wp_delete_term( $post_tag->term_id, $term_taxonomy );
							$wpdb->query( "DELETE FROM `" . $wpdb->terms . "` WHERE `term_id` = " . $post_tag->term_id );
							$wpdb->query( "DELETE FROM `" . $wpdb->term_relationships . "` WHERE `term_taxonomy_id` = " . $post_tag->term_taxonomy_id );
						}
					}
				}
				$wpdb->query( "DELETE FROM `" . $wpdb->term_taxonomy . "` WHERE `taxonomy` = '" . $term_taxonomy . "'" );
				break;

			case 'link':
				$wpdb->query( "TRUNCATE TABLE `" . $wpdb->prefix . "links`" );
				break;

			case 'comment':
				$args = array(
					'number' => 100
				);
				// Loop through every 100 records until 0 is returned, might take awhile
				while( woo_st_return_count( 'comment' ) ) {
					$comments = get_comments( $args );
					if( !empty( $comments ) ) {
						foreach( $comments as $comment )
							wp_delete_comment( $comment->comment_ID, true );
						unset( $comments, $comment );
					}
				}
				break;

			case 'media_image':
				$post_type = 'attachment';
				$post_mime_types = array( 'image/jpg', 'image/jpeg', 'image/jpe', 'image/gif', 'image/png' );
				$args = array(
					'post_type' => $post_type,
					'fields' => 'ids',
					'post_mime_type' => $post_mime_types,
					'post_status' => woo_st_post_statuses(),
					'numberposts' => 100,
				);
				// Loop through every 100 records until 0 is returned, might take awhile
				while( woo_st_return_count( 'media_image' ) ) {
					$images = get_posts( $args );
					if( !empty( $images ) ) {
						foreach( $images as $image )
							wp_delete_attachment( $image, true );
						unset( $images, $image );
					}
				}
				break;

		}
		// Mission accomplished
		woo_st_update_option( 'in_progress', '' );

	}

	function woo_st_relink_rogue_simple_type() {

		$updated_products = 0;
		$ignored_products = 0;

		// Get the Term ID for the Simple Product Type
		$term_taxonomy = 'product_type';
		$term_id = term_exists( 'simple', $term_taxonomy );

		// Check if the Simple Product Type exists
		if( $term_id == null || is_wp_error( $term_id ) ) {
			$message = __( 'The Term ID for the Simple Product Type could not be found within the WordPress Terms table (wp_terms), please de-activate and re-activate WooCommerce to resolve this.', 'woo_st' );
			woo_st_admin_notice( $message, 'error' );
			return;
		}
		$term_id = $term_id['term_id'];

		// Get a list of all Product Types
		$args = array(
			'fields' => 'ids',
			'hide_empty' => false
		);
		$terms = get_terms( $term_taxonomy, $args );

		// Filter a list of Products that do not have a Product Type
		$post_type = 'product';
		$args = array(
			'post_type' => $post_type,
			'fields' => 'ids',
			'tax_query' => array(
				array(
					'taxonomy' => $term_taxonomy,
					'field' => 'term_id',
					'terms' => $terms,
					'operator' => 'NOT IN'
				)
			),
			'posts_per_page' => -1,
			'post_status' => 'any'
		);
		$product_ids = new WP_Query( $args );
		if( $product_ids->posts ) {
			foreach( $product_ids->posts as $product_id ) {
				// First strip any corrupt Terms assigned to that Product
				wp_set_object_terms( $product_id, null, $term_taxonomy );
				// Assign the new Simple Term to that Product
				$response = wp_set_object_terms( $product_id, (int)$term_id, $term_taxonomy, true );
				if( $response == null || is_wp_error( $response ) ) {
					$ignored_products ++;
					continue;
				}
				$updated_products++;
			}

			if( !empty( $ignored_products ) ) {
				$message = sprintf( __( 'Some Products would not take the Simple Product Type or WordPress returned an error, we managed to update %d of %d', 'woo_ce' ), $ignored_products, $updated_products );
				woo_st_admin_notice( $message, 'error' );
			} else if( !empty( $updated_products ) ) {
				$message = sprintf( __( 'We managed to update %d Products with the Simple Product Type, happy days!', 'woo_ce' ), $updated_products );
				woo_st_admin_notice( $message );
			} else {
				$message = __( 'No existing Products had the Simple Product Type assigned to them', 'woo_ce' );
				woo_st_admin_notice( $message, 'error' );
			}
		} else {
			$message = __( 'No existing Products had the Simple Product Type assigned to them', 'woo_ce' );
			woo_st_admin_notice( $message, 'error' );
		}

	}

	function woo_st_remove_filename_extension( $filename ) {

		$extension = strrchr( $filename, '.' );
		$filename = substr( $filename, 0, -strlen( $extension ) );

		return $filename;

	}

	// Returns a list of allowed Export type statuses
	function woo_st_post_statuses() {

		$output = array(
			'publish',
			'pending',
			'draft',
			'auto-draft',
			'future',
			'private',
			'inherit',
			'trash'
		);
		return $output;

	}

	function woo_st_convert_sale_status( $sale_status = '' ) {

		$output = $sale_status;
		if( $sale_status ) {
			switch( $sale_status ) {

				case 'cancelled':
					$output = __( 'Cancelled', 'woo_st' );
					break;

				case 'completed':
					$output = __( 'Completed', 'woo_st' );
					break;

				case 'on-hold':
					$output = __( 'On-Hold', 'woo_st' );
					break;

				case 'pending':
					$output = __( 'Pending', 'woo_st' );
					break;

				case 'processing':
					$output = __( 'Processing', 'woo_st' );
					break;

				case 'refunded':
					$output = __( 'Refunded', 'woo_st' );
					break;

				case 'failed':
					$output = __( 'Failed', 'woo_st' );
					break;

			}
		}
		return $output;

	}

	/* End of: WordPress Administration */

}

function woo_st_get_option( $option = null, $default = false, $allow_empty = false ) {

	$output = false;
	if( $option !== null ) {
		$separator = '_';
		$output = get_option( WOO_ST_PREFIX . $separator . $option, $default );
		if( $allow_empty == false && $output != 0 && ( $output == false || $output == '' ) )
			$output = $default;
	}
	return $output;

}

function woo_st_update_option( $option = null, $value = null ) {

	$output = false;
	if( $option !== null && $value !== null ) {
		$separator = '_';
		$output = update_option( WOO_ST_PREFIX . $separator . $option, $value );
	}
	return $output;

}
?>