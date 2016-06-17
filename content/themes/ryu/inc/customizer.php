<?php
/**
 * ryu Theme Customizer
 *
 * @package Ryu
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function ryu_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
	$wp_customize->add_section( 'ryu_theme_options', array(
		'title'             => __( 'Theme Options', 'ryu' ),
		'priority'          => 35,
	) );

	$wp_customize->add_setting( 'gravatar_email', array(
		'default'           => get_option( 'admin_email' ),
		'sanitize_callback' => 'is_email',
	) );

	$wp_customize->add_control( 'gravatar_email', array(
		'label'             => __( 'Gravatar', 'ryu' ),
		'section'           => 'ryu_theme_options',
		'type'              => 'text',
		'priority'          => 1,
	) );

	$wp_customize->add_setting( 'email_link', array(
		'default'           => '',
		'sanitize_callback' => 'is_email',
	) );

	$wp_customize->add_control( 'email_link', array(
		'label'             => __( 'Email Link', 'ryu' ),
		'section'           => 'ryu_theme_options',
		'type'              => 'text',
		'priority'          => 2,
	) );

	$wp_customize->add_setting( 'twitter_link', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );

	$wp_customize->add_control( 'twitter_link', array(
		'label'             => __( 'Twitter Link', 'ryu' ),
		'section'           => 'ryu_theme_options',
		'type'              => 'text',
		'priority'          => 3,
	) );

	$wp_customize->add_setting( 'facebook_link', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );

	$wp_customize->add_control( 'facebook_link', array(
		'label'             => __( 'Facebook Link', 'ryu' ),
		'section'           => 'ryu_theme_options',
		'type'              => 'text',
		'priority'          => 4,
	) );

	$wp_customize->add_setting( 'pinterest_link', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );

	$wp_customize->add_control( 'pinterest_link', array(
		'label'             => __( 'Pinterest Link', 'ryu' ),
		'section'           => 'ryu_theme_options',
		'type'              => 'text',
		'priority'          => 5,
	) );

	$wp_customize->add_setting( 'google_plus_link', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );

	$wp_customize->add_control( 'google_plus_link', array(
		'label'             => __( 'Google+ Link', 'ryu' ),
		'section'           => 'ryu_theme_options',
		'type'              => 'text',
		'priority'          => 6,
	) );

	$wp_customize->add_setting( 'linkedin_link', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );

	$wp_customize->add_control( 'linkedin_link', array(
		'label'             => __( 'LinkedIn Link', 'ryu' ),
		'section'           => 'ryu_theme_options',
		'type'              => 'text',
		'priority'          => 7,
	) );

	$wp_customize->add_setting( 'flickr_link', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );

	$wp_customize->add_control( 'flickr_link', array(
		'label'             => __( 'Flickr Link', 'ryu' ),
		'section'           => 'ryu_theme_options',
		'type'              => 'text',
		'priority'          => 8,
	) );

	$wp_customize->add_setting( 'github_link', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );

	$wp_customize->add_control( 'github_link', array(
		'label'             => __( 'Github Link', 'ryu' ),
		'section'           => 'ryu_theme_options',
		'type'              => 'text',
		'priority'          => 9,
	) );

	$wp_customize->add_setting( 'dribbble_link', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );

	$wp_customize->add_control( 'dribbble_link', array(
		'label'             => __( 'Dribbble Link', 'ryu' ),
		'section'           => 'ryu_theme_options',
		'type'              => 'text',
		'priority'          => 10,
	) );

	$wp_customize->add_setting( 'vimeo_link', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );

	$wp_customize->add_control( 'vimeo_link', array(
		'label'             => __( 'Vimeo Link', 'ryu' ),
		'section'           => 'ryu_theme_options',
		'type'              => 'text',
		'priority'          => 11,
	) );

	$wp_customize->add_setting( 'youtube_link', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );

	$wp_customize->add_control( 'youtube_link', array(
		'label'             => __( 'YouTube Link', 'ryu' ),
		'section'           => 'ryu_theme_options',
		'type'              => 'text',
		'priority'          => 12,
	) );

	$wp_customize->add_setting( 'tumblr_link', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );

	$wp_customize->add_control( 'tumblr_link', array(
		'label'             => __( 'Tumblr Link', 'ryu' ),
		'section'           => 'ryu_theme_options',
		'type'              => 'text',
		'priority'          => 13,
	) );
}
add_action( 'customize_register', 'ryu_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function ryu_customize_preview_js() {
	wp_enqueue_script( 'ryu-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130410', true );
}
add_action( 'customize_preview_init', 'ryu_customize_preview_js' );