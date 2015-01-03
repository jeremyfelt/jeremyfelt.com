<?php
/**
 * @package Make
 */

if ( ! class_exists( 'TTFMAKE_Logo' ) ) :
/**
 * class TTFMAKE_Logo
 *
 * A class that adds custom logo functionality.
 *
 * @since 1.0.0.
 */
class TTFMAKE_Logo {

	/**
	 * The one instance of TTFMAKE_Logo
	 *
	 * @since 1.0.0.
	 *
	 * @var   TTFMAKE_Logo    The one instance.
	 */
	private static $instance;

	/**
	 * Stores the logo image, width, and height information.
	 *
	 * This var acts as a "run-time" cache. Since the functions in this class are called in different places throughout
	 * the page load, once the logo information is computed for the first time, it is cached to this variable.
	 * Subsequent requests for the information are pulled from the variable in memory instead of recomputing it.
	 *
	 * @since 1.0.0.
	 *
	 * @var   array    Holds the image, width, and height information for the logos.
	 */
	var $logo_information = array();

	/**
	 * Stores whether or not a specified logo type is available.
	 *
	 * @since 1.0.0.
	 *
	 * @var   array    Holds boolean values to indicate if the logo type is available.
	 */
	var $has_logo_by_type = array();

	/**
	 * Instantiate or return the one TTFMAKE_Logo instance.
	 *
	 * @since  1.0.0.
	 *
	 * @return TTFMAKE_Logo
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initiate the actions.
	 *
	 * @since  1.0.0.
	 *
	 * @return TTFMAKE_Logo
	 */
	public function __construct() {
		add_action( 'make_css', array( $this, 'print_logo_css' ) );
	}

	/**
	 * Get the ID of an attachment from its image URL.
	 *
	 * @author  Taken from reverted change to WordPress core http://core.trac.wordpress.org/ticket/23831
	 * @since   1.0.0.
	 *
	 * @param   string      $url    The path to an image.
	 * @return  int|bool            ID of the attachment or 0 on failure.
	 */
	function get_attachment_id_from_url( $url = '' ) {
		// If there is no url, return.
		if ( '' === $url ) {
			return false;
		}

		global $wpdb;
		$attachment_id = 0;

		// Function introduced in 4.0
		if ( function_exists( 'attachment_url_to_postid' ) ) {
			$attachment_id = absint( attachment_url_to_postid( $url ) );
			if ( 0 !== $attachment_id ) {
				return $attachment_id;
			}
		}

		// First try this
		if ( preg_match( '#\.[a-zA-Z0-9]+$#', $url ) ) {
			$sql = $wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND guid = %s",
				esc_url_raw( $url )
			);
			$attachment_id = absint( $wpdb->get_var( $sql ) );

			if ( 0 !== $attachment_id ) {
				return $attachment_id;
			}
		}

		// Then try this
		$upload_dir_paths = wp_upload_dir();
		if ( false !== strpos( $url, $upload_dir_paths['baseurl'] ) ) {
			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $url );

			// Remove the upload path base directory from the attachment URL
			$url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $url );

			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$sql = $wpdb->prepare(
				"SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'",
				esc_url_raw( $url )
			);
			$attachment_id = absint( $wpdb->get_var( $sql ) );
		}

		return $attachment_id;
	}

	/**
	 * Get the dimensions of a logo image from cache or regenerate the values.
	 *
	 * @since  1.0.0.
	 *
	 * @param  string    $url      The URL of the image in question.
	 * @param  bool      $force    Cause a cache refresh.
	 * @return array               The dimensions array on success, and a blank array on failure.
	 */
	function get_logo_dimensions( $url, $force = false ) {
		// Build the cache key
		$key = 'ttfmake-' . md5( 'logo-dimensions-' . $url . TTFMAKE_VERSION );

		// Pull from cache
		$dimensions = get_transient( $key );

		// If the value is not found in cache, regenerate
		if ( false === $dimensions || is_preview() || true === $force ) {
			$dimensions = array();

			// Get the ID of the attachment
			$attachment_id = $this->get_attachment_id_from_url( $url );

			// Get the dimensions
			$info = wp_get_attachment_image_src( $attachment_id, 'full' );

			if ( false !== $info && isset( $info[0] ) && isset( $info[1] ) && isset( $info[2] ) ) {
				// Detect JetPack altered src
				if ( false === $info[1] && false === $info[2] ) {
					// Parse the URL for the dimensions
					$pieces = parse_url( urldecode( $info[0] ) );

					// Pull apart the query string
					if ( isset( $pieces['query'] ) ) {
						parse_str( $pieces['query'], $query_pieces );

						// Get the values from "resize"
						if ( isset( $query_pieces['resize'] ) || isset( $query_pieces['fit'] ) ) {
							if ( isset( $query_pieces['resize'] ) ) {
								$jp_dimensions = explode( ',', $query_pieces['resize'] );
							} elseif ( $query_pieces['fit'] ){
								$jp_dimensions = explode( ',', $query_pieces['fit'] );
							}

							if ( isset( $jp_dimensions[0] ) && isset( $jp_dimensions[1] ) ) {
								// Package the data
								$dimensions = array(
									'width'  => $jp_dimensions[0],
									'height' => $jp_dimensions[1],
								);
							}
						}
					}
				} else {
					// Package the data
					$dimensions = array(
						'width'  => $info[1],
						'height' => $info[2],
					);
				}
			} else {
				// Get the image path from the URL
				$wp_upload_dir = wp_upload_dir();
				$path          = trailingslashit( $wp_upload_dir['basedir'] ) . get_post_meta( $attachment_id, '_wp_attached_file', true );

				// Sometimes, WordPress just doesn't have the metadata available. If not, get the image size
				if ( file_exists( $path ) ) {
					$getimagesize = getimagesize( $path );

					if ( false !== $getimagesize && isset( $getimagesize[0] ) && isset( $getimagesize[1] ) ) {
						$dimensions = array(
							'width'  => $getimagesize[0],
							'height' => $getimagesize[1],
						);
					}
				}
			}

			// Store the transient
			if ( ! is_preview() ) {
				set_transient( $key, $dimensions, 86400 );
			}
		}

		return $dimensions;
	}

	/**
	 * Determine if a custom logo should be displayed.
	 *
	 * @since  1.0.0.
	 *
	 * @return bool    True if a logo should be displayed. False if a logo shouldn't be displayed.
	 */
	public function has_logo() {
		return ( $this->has_logo_by_type( 'logo-regular' ) || $this->has_logo_by_type( 'logo-retina' ) );
	}

	/**
	 * Determine if necessary information is available to show a particular logo.
	 *
	 * @since  1.0.0.
	 *
	 * @param  string    $type    The type of logo to inspect for.
	 * @return bool               True if all information is available. False is something is missing.
	 */
	function has_logo_by_type( $type ) {
		// Clean the type value
		$type = sanitize_key( $type );

		// If the information is already set, return it from the instance cache
		if ( isset( $this->has_logo_by_type[ $type ] ) ) {
			return $this->has_logo_by_type[ $type ];
		}

		// Grab the logo information
		$information = $this->get_logo_information();

		// Set the default return value
		$return = false;

		// Verify that the logo type exists in the array
		if ( isset( $information[ $type ] ) ) {

			// Verify that the image is set and has a value
			if ( isset( $information[ $type ]['image'] ) && ! empty( $information[ $type ]['image'] ) ) {

				// Verify that the width is set and has a value
				if ( isset( $information[ $type ]['width'] ) && ! empty( $information[ $type ]['width'] ) ) {

					// Verify that the height is set and has a value
					if ( isset( $information[ $type ]['height'] ) && ! empty( $information[ $type ]['height'] ) ) {
						$return = true;
					}
				}
			}
		}

		// Cache to the instance var for future use
		$this->has_logo_by_type[ $type ] = $return;
		return $this->has_logo_by_type[ $type ];
	}

	/**
	 * Utility function for getting information about the theme logos.
	 *
	 * @since  1.0.0.
	 *
	 * @param  bool     $force    Update the dimension cache.
	 * @return array              Array containing image file, width, and height for each logo.
	 */
	function get_logo_information( $force = false) {
		// If the logo information is cached to an instance var, pull from there
		if ( ! empty( $this->logo_information ) ) {
			return $this->logo_information;
		}

		// Set the logo slugs
		$logos = array(
			'logo-regular',
			'logo-retina',
		);

		// For each logo slug, get the image, width and height
		foreach ( $logos as $logo ) {
			$this->logo_information[ $logo ]['image'] = get_theme_mod( $logo, ttfmake_get_default( $logo ) );

			// Set the defaults
			$this->logo_information[ $logo ]['width']  = '';
			$this->logo_information[ $logo ]['height'] = '';

			// If there is an image, get the dimensions
			if ( ! empty( $this->logo_information[ $logo ]['image'] ) ) {
				$dimensions = $this->get_logo_dimensions( $this->logo_information[ $logo ]['image'], $force );

				// Set the dimensions to the array if all information is present
				if ( ! empty( $dimensions ) && isset( $dimensions['width'] ) && isset( $dimensions['height'] ) ) {
					$this->logo_information[ $logo ]['width']  = $dimensions['width'];
					$this->logo_information[ $logo ]['height'] = $dimensions['height'];
				}
			}
		}

		/**
		 * Filter the URL and dimensions of the custom logo.
		 *
		 * This filter may be useful if you encounter problems getting your custom
		 * logo to appear. Note, however, that using this filter will hard-code the logo
		 * information and settings in the Logo interface in the Customizer won't be
		 * reflected.
		 *
		 * @since 1.0.0.
		 *
		 * @param array    $logo_information    The array of information.
		 */
		$this->logo_information = apply_filters( 'ttfmake_custom_logo_information', $this->logo_information );

		return $this->logo_information;
	}

	/**
	 * Print CSS in the head for the logo.
	 *
	 * @since  1.0.0.
	 *
	 * @return void
	 */
	function print_logo_css() {
		/**
		 * Filter the maximum allowable width for a custom logo.
		 *
		 * @since 1.0.0.
		 *
		 * @param string|int    $width    The maximum width, in pixels.
		 */
		$size = apply_filters( 'ttfmake_custom_logo_max_width', '960' );

		// Grab the logo information
		$info = $this->get_logo_information();

		// Both logo types are available
		if ( $this->has_logo_by_type( 'logo-regular' ) && $this->has_logo_by_type( 'logo-retina' ) ) {
			$final_dimensions = $this->adjust_dimensions( $info['logo-regular']['width'], $info['logo-regular']['height'], $size, false );

			ttfmake_get_css()->add( array(
				'selectors' => array( '.custom-logo' ),
				'declarations' => array(
					'background-image' => 'url("' . addcslashes( esc_url_raw( $info['logo-regular']['image'] ), '"' ) . '")',
					'width'            => absint( $final_dimensions['width'] ) . 'px'
				)
			) );

			ttfmake_get_css()->add( array(
				'selectors' => array( '.custom-logo a' ),
				'declarations' => array(
					'padding-bottom' => (float) $final_dimensions['ratio'] . '%'
				)
			) );

			ttfmake_get_css()->add( array(
				'selectors' => array( '.custom-logo' ),
				'declarations' => array(
					'background-image' => 'url("' . addcslashes( esc_url_raw( $info['logo-retina']['image'] ), '"' ) . '")'
				),
				'media' => '(-webkit-min-device-pixel-ratio: 1.3),(-o-min-device-pixel-ratio: 2.6/2),(min--moz-device-pixel-ratio: 1.3),(min-device-pixel-ratio: 1.3),(min-resolution: 1.3dppx)'
			) );
		}
		// Regular logo only
		else if ( $this->has_logo_by_type( 'logo-regular' ) ) {
			$final_dimensions = $this->adjust_dimensions( $info['logo-regular']['width'], $info['logo-regular']['height'], $size );

			ttfmake_get_css()->add( array(
				'selectors' => array( '.custom-logo' ),
				'declarations' => array(
					'background-image' => 'url("' . addcslashes( esc_url_raw( $info['logo-regular']['image'] ), '"' ) . '")',
					'width'            => absint( $final_dimensions['width'] ) . 'px'
				)
			) );

			ttfmake_get_css()->add( array(
				'selectors' => array( '.custom-logo a' ),
				'declarations' => array(
					'padding-bottom' => (float) $final_dimensions['ratio'] . '%'
				)
			) );
		}
		// Retina logo only
		else if ( $this->has_logo_by_type( 'logo-retina' ) ) {
			$final_dimensions = $this->adjust_dimensions( $info['logo-retina']['width'], $info['logo-retina']['height'], $size, true );

			ttfmake_get_css()->add( array(
				'selectors' => array( '.custom-logo' ),
				'declarations' => array(
					'background-image' => 'url("' . addcslashes( esc_url_raw( $info['logo-retina']['image'] ), '"' ) . '")',
					'width'            => absint( $final_dimensions['width'] ) . 'px'
				)
			) );

			ttfmake_get_css()->add( array(
				'selectors' => array( '.custom-logo a' ),
				'declarations' => array(
					'padding-bottom' => (float) $final_dimensions['ratio'] . '%'
				)
			) );
		}
	}

	/**
	 * Scale the image to the width boundary.
	 *
	 * @since  1.0.0.
	 *
	 * @param  int      $width             The image's width.
	 * @param  int      $height            The image's height.
	 * @param  int      $width_boundary    The maximum width for the image.
	 * @param  bool     $retina            Whether or not to divide the dimensions by 2.
	 * @return array                       Resulting height/width dimensions.
	 */
	function adjust_dimensions( $width, $height, $width_boundary, $retina = false ) {
		// Divide the dimensions by 2 for retina logos
		$divisor = ( true === $retina ) ? 2 : 1;
		$width   = $width / $divisor;
		$height  = $height / $divisor;

		// If width is wider than the boundary, apply the adjustment
		if ( $width > $width_boundary ) {
			$change_percentage = $width_boundary / $width;
			$width             = $width_boundary;
			$height            = $height * $change_percentage;
		}

		// Height / Width ratio
		$ratio = $height / $width * 100;

		// Arrange the resulting dimensions in an array
		return array(
			'width'  => $width,
			'height' => $height,
			'ratio'  => $ratio
		);
	}
}
endif;

if ( ! function_exists( 'ttfmake_get_logo' ) ) :
/**
 * Return the one TTFMAKE_Logo object.
 *
 * @since  1.0.0.
 *
 * @return TTFMAKE_Logo
 */
function ttfmake_get_logo() {
	return TTFMAKE_Logo::instance();
}
endif;

add_action( 'init', 'ttfmake_get_logo', 11 );

if ( ! function_exists( 'ttfmake_refresh_logo_cache' ) ) :
/**
 * Refresh the logo cache after the customizer is saved.
 *
 * @since  1.0.0.
 *
 * @param  object    $wp_customize    The customizer object.
 * @return void
 */
function ttfmake_refresh_logo_cache( $wp_customize ) {
	ttfmake_get_logo()->get_logo_information( true );
}
endif;

add_action( 'customize_save_after', 'ttfmake_refresh_logo_cache' );