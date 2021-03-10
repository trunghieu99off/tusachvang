<?php
/*
* Code Bỏ /product/ hoặc /cua-hang/ hoặc /shop/ ... có hỗ trợ dạng %product_cat%
* Thay /cua-hang/ bằng slug hiện tại của bạn
*/
function devvn_remove_slug( $post_link, $post ) {
    if ( !in_array( get_post_type($post), array( 'product' ) ) || 'publish' != $post->post_status ) {
        return $post_link;
    }
    if('product' == $post->post_type){
        $post_link = str_replace( '/san-pham/', '/', $post_link ); //Thay san-pham bằng slug hiện tại của bạn
    }else{
        $post_link = str_replace( '/' . $post->post_type . '/', '/', $post_link );
    }
    return $post_link;
}
add_filter( 'post_type_link', 'devvn_remove_slug', 10, 2 );
/*Sửa lỗi 404 sau khi đã remove slug product hoặc cua-hang*/
function devvn_woo_product_rewrite_rules($flash = false) {
    global $wp_post_types, $wpdb;
    $siteLink = esc_url(home_url('/'));
    foreach ($wp_post_types as $type=>$custom_post) {
        if($type == 'product'){
            if ($custom_post->_builtin == false) {
                $querystr = "SELECT {$wpdb->posts}.post_name, {$wpdb->posts}.ID
                            FROM {$wpdb->posts} 
                            WHERE {$wpdb->posts}.post_status = 'publish' 
                            AND {$wpdb->posts}.post_type = '{$type}'";
                $posts = $wpdb->get_results($querystr, OBJECT);
                foreach ($posts as $post) {
                    $current_slug = get_permalink($post->ID);
                    $base_product = str_replace($siteLink,'',$current_slug);
                    add_rewrite_rule($base_product.'?$', "index.php?{$custom_post->query_var}={$post->post_name}", 'top');
                }
            }
        }
    }
    if ($flash == true)
        flush_rewrite_rules(false);
}
add_action('init', 'devvn_woo_product_rewrite_rules');
/*Fix lỗi khi tạo sản phẩm mới bị 404*/
function devvn_woo_new_product_post_save($post_id){
    global $wp_post_types;
    $post_type = get_post_type($post_id);
    foreach ($wp_post_types as $type=>$custom_post) {
        if ($custom_post->_builtin == false && $type == $post_type) {
            devvn_woo_product_rewrite_rules(true);
        }
    }
}
add_action('wp_insert_post', 'devvn_woo_new_product_post_save');
// Add custom Theme Functions here
/**
 * Remove product tabs
 *
 */
function woo_remove_product_tab($tabs) {
 
        unset( $tabs['reviews'] );                                  // Remove the reviews tab
    unset( $tabs['additional_information'] );   // Remove the additional information tab
 
        return $tabs;
 
}
add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tab', 98);

add_filter( 'woocommerce_product_tabs', 'woo_rename_tabs', 98 );
function woo_rename_tabs( $tabs ) {
$tabs['description']['title'] = __( 'Thông tin sản phẩm' );     // đổi tên description tab
return $tabs;
}
add_filter('add_to_cart_redirect', 'custom_add_to_cart_redirect');
 
function custom_add_to_cart_redirect() {
     /**
      * Replace with the url of your choosing
      * e.g. return 'http://www.yourshop.com/'
      */
     return get_permalink( get_option('woocommerce_checkout_page_id') );
}