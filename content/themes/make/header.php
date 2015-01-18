<?php
/**
 * @package Make
 */
?><!DOCTYPE html>
<!--[if lte IE 9]><html class="no-js IE9 IE" <?php language_attributes(); ?>><![endif]-->
<!--[if gt IE 9]><!--><html class="no-js" <?php language_attributes(); ?>><!--<![endif]-->
	<head>
		<?php wp_head(); ?>
	</head>

	<body <?php body_class(); ?>>
		<div id="site-wrapper" class="site-wrapper">

			<?php ttfmake_maybe_show_site_region( 'header' ); ?>

			<div id="site-content" class="site-content">
				<div class="container">