<?php
/**
 * Header sidebar template part.
 *
 * @package yith-proteo
 */
?>
<div class="header-sidebar">
	<div class="header-sidebar-inner-widgets">
<?php //Eklediğim Kod ?>
<a href="https://floribook.free.nf/floribook-ai/" id="chat-btn">
  <img src="<?php  echo get_stylesheet_directory_uri(); ?>/template-parts/header/ai-img.png" alt="AI Asistan">
</a>

<style>
#chat-btn {
  position: fixed;
  right: 20px;
  bottom: 20px;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #1E3952;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  text-decoration: none;
  font-size: 24px;
}
</style>

<?php
// YITH WooCommerce Wishlist ikonu
if ( class_exists( 'YITH_WCWL' ) ) {

    $wishlist_url   = YITH_WCWL()->get_wishlist_url();
    $wishlist_count = YITH_WCWL()->count_products();
    ?>
    <section class="widget widget-wishlist-icon">
        <a href="<?php echo esc_url( $wishlist_url ); ?>" class="wishlist-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.8rem" height="1.8rem" viewBox="0 0 24 24" fill="none" stroke="#404040" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
            <?php if ( $wishlist_count > 0 ) : ?>
                <span class="wishlist-count"><?php echo esc_html( $wishlist_count ); ?></span>
            <?php endif; ?>
        </a>
    </section>
    <?php 
}
?>

	<?php
	//Sistemden hazır kod
	if ( get_theme_mod( 'yith_proteo_header_search_widget', 'no' ) === 'yes' ) {
		the_widget(
			'WP_Widget_Search',
			array(),
			array(
				'before_widget' => '<section class="widget %1$s ' . ( get_theme_mod( 'yith_proteo_mobile_search_widget', 'yes' ) === 'yes' ? 'hidden-xs' : '' ) . '">',
				'after_widget'  => '</section>',
			)
		);
	}
	?>
	
   	<?php
	if ( get_theme_mod( 'yith_proteo_header_account_widget', 'no' ) === 'yes' ) {
		the_widget(
			'YITH_Proteo_Account_Widget',
			array(
				'custom-icon'   => get_template_directory_uri() . '/img/user.svg',
				'login-url'     => wp_login_url(),
				'myaccount-url' => get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ? get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) : get_home_url( null, '/my-account/' ),
			),
			array(
				'before_widget' => '<section class="widget %1$s">',
				'after_widget'  => '</section>',
			)
		);
	}
	?>

<?php if ( 'yes' === get_theme_mod( 'yith_proteo_show_header_sidebar', 'yes' ) ) { ?>
	<?php dynamic_sidebar( 'header-sidebar' ); ?>
<?php } ?>


</div>
</div>
