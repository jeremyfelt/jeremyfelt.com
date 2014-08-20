<?php
/**
 * @package Make
 */

// Header Options
$header_text     = get_theme_mod( 'header-text', false );
$social_links    = ttfmake_get_social_links();
$show_social     = (int) get_theme_mod( 'header-show-social', ttfmake_get_default( 'header-show-social' ) );
$show_search     = (int) get_theme_mod( 'header-show-search', ttfmake_get_default( 'header-show-search' ) );
$subheader_class = ( 1 === $show_social ) ? ' right-content' : '';
$hide_site_title = (int) get_theme_mod( 'hide-site-title', ttfmake_get_default( 'hide-site-title' ) );
$hide_tagline    = (int) get_theme_mod( 'hide-tagline', ttfmake_get_default( 'hide-tagline' ) );
$menu_label      = get_theme_mod( 'navigation-mobile-label', ttfmake_get_default( 'navigation-mobile-label' ) );
$header_bar_menu = wp_nav_menu( array(
	'theme_location' => 'header-bar',
	'fallback_cb'    => false,
	'echo'           => false,
) );
?>

<header id="site-header" class="<?php echo esc_attr( ttfmake_get_site_header_class() ); ?>" role="banner">
	<?php // Only show Sub Header if it has content
	if ( ! empty( $header_text ) || 1 === $show_search || ( ! empty ( $social_links ) && 1 === $show_social ) || ! empty( $header_bar_menu ) ) : ?>
	<div class="header-bar<?php echo esc_attr( $subheader_class ); ?>">
		<div class="container">
			<a class="skip-link screen-reader-text" href="#site-content"><?php _e( 'Skip to content', 'make' ); ?></a>
			<?php // Social links
			ttfmake_maybe_show_social_links( 'header' ); ?>
			<?php // Header text; shown only if there is no header menu
			if ( ( ! empty( $header_text ) || ttfmake_is_preview() ) && empty( $header_bar_menu ) ) : ?>
				<span class="header-text">
				<?php echo ttfmake_sanitize_text( $header_text ); ?>
				</span>
			<?php endif; ?>
			<?php echo $header_bar_menu; ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="site-header-main">
		<div class="container">
			<div class="site-branding">
				<?php if ( ttfmake_get_logo()->has_logo() ) : ?>
				<div class="custom-logo">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"></a>
				</div>
				<?php endif; ?>
				<h1 class="site-title">
					<?php // Site title
					if ( 1 !== $hide_site_title && get_bloginfo( 'name' ) ) : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<?php bloginfo( 'name' ); ?>
					</a>
					<?php endif; ?>
				</h1>
				<?php // Tagline
				if ( 1 !== $hide_tagline && get_bloginfo( 'description' ) ) : ?>
				<span class="site-description">
					<?php bloginfo( 'description' ); ?>
				</span>
				<?php endif; ?>
			</div>

			<?php // Search form
			if ( 1 === $show_search ) :
				get_search_form();
			endif; ?>

			<nav id="site-navigation" class="site-navigation" role="navigation">
				<span class="menu-toggle"><?php echo esc_html( $menu_label ); ?></span>
				<a class="skip-link screen-reader-text" href="#site-content"><?php _e( 'Skip to content', 'make' ); ?></a>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary'
				) );
				?>
			</nav>
		</div>
	</div>
</header>