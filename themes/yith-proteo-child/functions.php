<?php
function yithproteo_child_enqueue_styles() {
    wp_enqueue_style( 'yith-proteo-style', get_template_directory_uri() . '/style.css', array('select2'), YITH_PROTEO_VERSION );
    wp_enqueue_style( 'yith-proteo-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'yith-proteo-style' ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'yithproteo_child_enqueue_styles' );

// Eklenen Kodlar

function change_wishlist_texts( $translated_text, $text, $domain ) {
    if ( $text === 'Product name' ) {
        return 'Ürün';
    }
    if ( $text === 'Unit price' ) {
        return 'Fiyat';
    }
    if ( $text === 'Stock status' ) {
        return 'Stok';
    }
    return $translated_text;
}
add_filter( 'gettext', 'change_wishlist_texts', 20, 3 );

add_filter( 'woocommerce_get_image_size_thumbnail', function( $size ) {
    return array(
        'width'  => 362,
        'height' => 503,
        'crop'   => 0,
    );
} );