<?php

/*
Plugin Name: Focus
Plugin URI: https://github.com/avryl/focus
Description: Focus.
Author: Janneke Van Dorpe
Author URI: http://profiles.wordpress.org/avryl/
Version: 0.2.7
Text Domain: focus
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( is_admin() && ! wp_is_mobile() && ! class_exists( 'Focus' ) ) {
	class Focus {
		const VERSION = '0.2.7';

		function __construct() {
			add_action( 'load-post.php', array( $this, 'load' ) );
			add_action( 'load-post-new.php', array( $this, 'load' ) );
		}

		function load() {
			add_filter( 'mce_css', array( $this, 'css' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'mce_external_plugins', array( $this, 'external_plugins' ) );
			add_action( 'tiny_mce_plugins', array( $this, 'plugins' ) );
		}

		function css( $css ) {
			$css = explode( ',', $css );
			array_push( $css, plugins_url( 'tinymce.focus.css?v=' . ( defined( 'WP_DEBUG' ) && WP_DEBUG ? urlencode( time() ) : self::VERSION ), __FILE__ ) );

			return implode( ',', $css );
		}

		function enqueue_scripts() {
			wp_deregister_script( 'wp-fullscreen' );
			wp_deregister_script( 'editor-expand' );
			wp_enqueue_script( 'editor-expand', plugins_url( 'editor-expand.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
			wp_enqueue_script( 'focus', plugins_url( 'focus.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );

			wp_enqueue_style( 'focus', plugins_url( 'focus.css', __FILE__ ), array(), self::VERSION );
		}

		function external_plugins( $plugins ) {
			$plugins['focus'] = plugins_url( 'tinymce.focus.js?v=' . self::VERSION, __FILE__ );
			$plugins['wpautoresize'] = plugins_url( 'tinymce.autoresize.js?v=' . self::VERSION, __FILE__ );

			return $plugins;
		}

		function plugins( $plugins ) {
			return array_diff( $plugins, array( 'wpautoresize' ) );
		}
	}

	new Focus;
}
