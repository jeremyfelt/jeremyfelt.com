<?php
/**
 * @package Make
 */

if ( ! class_exists( 'TTFMAKE_Prioritizer' ) ) :
/**
 * Class TTFMAKE_Prioritizer
 *
 * Increment upward from a starting number with each call to add().
 *
 * @since 1.0.0.
 */
class TTFMAKE_Prioritizer {
	/**
	 * The starting priority.
	 *
	 * @since 1.0.0.
	 *
	 * @var   int    The priority used to start the incrementor.
	 */
	var $initial_priority = 0;

	/**
	 * The amount to increment for each step.
	 *
	 * @since 1.0.0.
	 *
	 * @var   int    The amount to increment for each step.
	 */
	var $increment = 0;

	/**
	 * Holds the reference to the current priority value.
	 *
	 * @since 1.0.0.
	 *
	 * @var   int    Holds the reference to the current priority value.
	 */
	var $current_priority = 0;

	/**
	 * Set the initial properties on init.
	 *
	 * @param  int                    $initial_priority    Value to being the counter.
	 * @param  int                    $increment           Value to increment the counter by.
	 * @return TTFMAKE_Prioritizer
	 */
	function __construct( $initial_priority = 100, $increment = 100 ) {
		$this->initial_priority = absint( $initial_priority );
		$this->increment        = absint( $increment );
		$this->current_priority = $this->initial_priority;
	}

	/**
	 * Get the current value.
	 *
	 * @since  1.0.0.
	 *
	 * @return int    The current priority value.
	 */
	public function get() {
		return $this->current_priority;
	}

	/**
	 * Increment the priority.
	 *
	 * @since  1.0.0.
	 *
	 * @param  int    $increment    The value to increment by.
	 * @return void
	 */
	public function inc( $increment = 0 ) {
		if ( 0 === $increment ) {
			$increment = $this->increment;
		}
		$this->current_priority += absint( $increment );
	}

	/**
	 * Increment by the $this->increment value.
	 *
	 * @since  1.0.0.
	 *
	 * @return int    The priority value.
	 */
	public function add() {
		$priority = $this->get();
		$this->inc();
		return $priority;
	}

	/**
	 * Reset the counter.
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function reboot() {
		$this->current_priority = $this->initial_priority;
	}
}
endif;

if ( class_exists( 'WP_Customize_Image_Control' ) && ! class_exists( 'TTFMAKE_Customize_Image_Control' ) ) :
/**
 * Class TTFMAKE_Customize_Image_Control
 *
 * Extend WP_Customize_Image_Control allowing access to uploads made within the same context.
 *
 * @since 1.0.0.
 */
class TTFMAKE_Customize_Image_Control extends WP_Customize_Image_Control {
	/**
	 * Override the stock tab_uploaded function.
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function tab_uploaded() {
		$images = get_posts( array(
			'post_type'  => 'attachment',
			'meta_key'   => '_wp_attachment_context',
			'meta_value' => $this->context,
			'orderby'    => 'none',
			'nopaging'   => true,
		) );

		?><div class="uploaded-target"></div><?php

		if ( empty( $images ) ) {
			return;
		}

		foreach ( (array) $images as $image ) {
			$thumbnail_url = wp_get_attachment_image_src( $image->ID, 'medium' );
			$this->print_tab_image( esc_url_raw( $image->guid ), esc_url_raw( $thumbnail_url[0] ) );
		}
	}
}
endif;

if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'TTFMAKE_Customize_Misc_Control' ) ) :
/**
 * Class TTFMAKE_Customize_Misc_Control
 *
 * Control for adding arbitrary HTML to a Customizer section.
 *
 * @since 1.0.0.
 */
class TTFMAKE_Customize_Misc_Control extends WP_Customize_Control {
	/**
	 * The current setting name.
	 *
	 * @since 1.0.0.
	 *
	 * @var   string    The current setting name.
	 */
	public $settings = 'blogname';

	/**
	 * The current setting description.
	 *
	 * @since 1.0.0.
	 *
	 * @var   string    The current setting description.
	 */
	public $description = '';

	/**
	 * The current setting group.
	 *
	 * @since 1.0.0.
	 *
	 * @var   string    The current setting group.
	 */
	public $group = '';

	/**
	 * Render the description and title for the section.
	 *
	 * Prints arbitrary HTML to a customizer section. This provides useful hints for how to properly set some custom
	 * options for optimal performance for the option.
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	public function render_content() {
		switch ( $this->type ) {
			default:
			case 'text' :
				echo '<p class="description">' . $this->description . '</p>';
				break;

			case 'heading':
				echo '<span class="customize-control-title">' . $this->label . '</span>';
				break;

			case 'line' :
				echo '<hr />';
				break;
		}
	}
}
endif;