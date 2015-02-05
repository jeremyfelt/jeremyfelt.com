<?php
/*
Plugin Name: Press This
Plugin URI: https://wordpress.org/plugins/press-this/
Description: Posting images, links, and cat gifs will never be the same.
Version: 0.0.4.3-20150204
Author: Press This Team
Author URI: https://corepressthis.wordpress.com/
Text Domain: press-this
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Class WpPressThis
 */
class WpPressThis {
	/**
	 * WpPressThis::__construct()
	 * Constructor
	 *
	 * @uses remove_action(), add_action()
	 */
	public function __construct() {
		$script_name = self::script_name();

		if ( empty( $script_name ) )
			return;

		if ( is_admin() ) {
			if ( false !== strpos( self::runtime_url(), $script_name ) ) {
				/*
				 * Take over /wp-admin/press-this.php
				 */
				add_action( 'admin_init', array( $this, 'press_this_php_override' ), 0 );
			} else if ( false !== strpos( admin_url( 'tools.php' ),$script_name ) ) {
				/*
				 * Take over Press This bookmarklet code in /wp-admin/tools.php
				 */
				add_filter( 'shortcut_link', array( $this, 'shortcut_link_override' ) );
			} else if ( false !== strpos( admin_url( 'admin-ajax.php' ), $script_name ) ) {
				/*
				 * AJAX emdpoints
				 */
				// Site settings
				add_action( 'wp_ajax_press_this_site_settings',       array( $this, 'ajax_site_settings' ) );
				// Post draft and publish
				add_action( 'wp_ajax_press_this_publish_post',        array( $this, 'ajax_publish_post' ) );
				add_action( 'wp_ajax_press_this_draft_post',          array( $this, 'ajax_draft_post' ) );
				// Chrome extension manifest
				// add_action( 'wp_ajax_press_this_chrome_ext_manifest', array( $this, 'ajax_chrome_ext_manifest' ) );
			}
		}
	}

	/**
	 * WpPressThis::script_name()
	 * Returns the current app's fully qualified script name/url based on system-level tests
	 *
	 * @return mixed|string
	 * @uses $_SERVER['SCRIPT_NAME'], $_SERVER['PHP_SELF'], $_SERVER['REQUEST_URI']
	 */
	public function script_name() {
		$script_name = ( ! empty( $_SERVER['SCRIPT_NAME'] ) )
			? $_SERVER['SCRIPT_NAME']
			: ( ! empty( $_SERVER['PHP_SELF'] ) )
				? $_SERVER['PHP_SELF']
				: ( ! empty( $_SERVER['REQUEST_URI'] ) )
					? preg_replace( '/^([^\?]+)(\?.*)?$/', '\1', $_SERVER['REQUEST_URI'] )
					: '';
		return ( preg_match('/\/wp-admin\/?$/', $script_name) || ! preg_match('/\.php$/', $script_name ) )
			? untrailingslashit( $script_name ) . '/index.php'
			: $script_name;
	}

	/**
	 * WpPressThis::set_url_scheme( $url )
	 * Sets the URL to https or http, depending on availability and related WP config settings/APIs.
	 *
	 * @param $url string
	 *
	 * @return string
	 * @uses WP's force_ssl_admin(), force_ssl_login(), force_ssl_content(), is_ssl(), and set_url_scheme()
	 */
	public function set_url_scheme( $url ) {
		$current_user = get_current_user();
		if ( ( function_exists( 'force_ssl_admin' ) && force_ssl_admin() )
			|| ( function_exists( 'force_ssl_login' ) && force_ssl_login() )
			|| ( function_exists( 'force_ssl_content' ) && force_ssl_content() )
			|| ( function_exists( 'is_ssl' ) && is_ssl() )
			|| ! empty( $current_user->use_ssl ) ) {

			return set_url_scheme(  $url, 'https' );
		}

		return set_url_scheme( $url, 'http' );
	}

	/**
	 * WpPressThis::strip_url_scheme()
	 * Removes http or https from a URL to have it default to the current protocaol on the client-side (EG: //youtube.com/)
	 *
	 * @param $url
	 * @return mixed
	 */
	public function strip_url_scheme( $url ) {
		return preg_replace( '/^https?:(\/\/.+)$/', '\1', $url );
	}

	/**
	 * WpPressThis::plugin_data()
	 * Returns this plugin's meta data, from in-code plugin header comment
	 *
	 * @return array
	 * @uses WP's get_plugin_data()
	 */
	public function plugin_data() {
		return get_plugin_data( __FILE__, false, false );
	}

	/**
	 * WpPressThis::plugin_data()
	 * Returns this plugin's own version string, from in-code plugin header comment
	 *
	 * @return string The current plugin's version
	 * @uses WpPressThis::plugin_data()
	 */
	public function plugin_version() {
		$data = self::plugin_data();
		return ( ! empty( $data ) && ! empty( $data['Version'] ) ) ? (string) $data['Version'] : 0;
	}

	/**
	 * WpPressThis::runtime_url()
	 * Returns this plugin's own runtime URL, which happens to masquerade/override as /wp-admin/press-this.php
	 *
	 * @return string Full URL to /admin/press-this.php in current install
	 * @uses admin_url()
	 */
	public function runtime_url() {
		return self::set_url_scheme( admin_url( 'press-this.php' ) );
	}

	/**
	 * WpPressThis::plugin_dir_path()
	 *
	 * @return string Full system path to /wp-content/plugins/press-this in current install
	 * @uses __FILE__, plugin_dir_path()
	 */
	public function plugin_dir_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * WpPressThis::plugin_dir_url()
	 *
	 * @return string Full URL path to /wp-content/plugins/press-this in current install
	 * @uses __FILE__, plugin_dir_url()
	 */
	public function plugin_dir_url() {
		return untrailingslashit( self::strip_url_scheme( plugin_dir_url( __FILE__ ) ) );
	}

	/**
	 * WpPressThis::i18n()
	 * Centralized/keyed app caption store, used on both server and client sides.
	 *
	 * @return array
	 */
	public function i18n() {
		return array(
			'press-this'                 => __( 'Press This!', 'press-this' ),
			'welcome'                    => __( 'Welcome to Press This!', 'press-this' ),
			'source'                     => __( 'Source:', 'press-this' ),
			'settings'                   => __( 'Settings', 'press-this' ),
			'close'                      => __( 'Close', 'press-this' ),
			'no-media'                   => __( 'Clear selected media', 'press-this' ),
			'show-all-media'             => __( 'Display all media', 'press-this' ),
			'show-selected-media'        => __( 'Display selected media', 'press-this' ),
			'publish'                    => __( 'Publish', 'press-this' ),
			'save-draft'                 => __( 'Save Draft', 'press-this' ),
			'new-post'                   => __( 'New Post', 'press-this' ),
			'start-typing-here'          => __( 'Start typing here.', 'press-this' ),
			'enter-url-to-scan'          => __( 'Enter a URL to scan', 'press-this' ),
			'scan'                       => __( 'Scan', 'press-this' ),
			'enter-wp-url'               => __( 'Enter a WordPress URL', 'press-this' ),
			'add'                        => __( 'Add', 'press-this' ),
			'upload-photo'               => __( 'Upload Photo', 'press-this' ),
			'upload-failed'              => __( 'Sorry, but your upload failed.', 'press-this' ),
			'unexpected-error'           => __( 'Sorry, but an unexpected error occurred.', 'press-this' ),
			'should-upgrade-bookmarklet' => __( 'You should upgrade <a href="%s" target="_blank">your bookmarklet</a> to the latest version!', 'press-this' ),
			'limit-uploads-to-photos'    => __( 'Please limit your uploads to photos. The file is still in the media library, and can be used in a new post, or <a href="%s" target="_blank">downloaded here</a>.', 'press-this' ),
		);
	}

	/** TODO: this will only be needed for the browser addon?
	 * WpPressThis::press_this_ajax_site_settings()
	 * App and site settings data, including i18n strings for the client-side
	 *
	 * @uses admin_url(), wp_create_nonce()
	 */
	public function site_settings() {
		$current_user = wp_get_current_user();
		$site_name    = get_bloginfo( 'name', 'display' );
		$site_url     = self::strip_url_scheme( home_url( '/' ) );
		$users_sites  = array();

		/* TODO: move to the browser addon
		foreach ( get_blogs_of_user( $current_user->ID ) as $site_id => $site_info ) {
			// Do want to include self in the menu, with proper scheme. But just once.
			if ( empty( $site_info->siteurl ) || isset( $users_sites[ $site_info->siteurl ] ) )
				continue;
			$users_sites[ rtrim( self::set_url_scheme( $site_info->siteurl ), '/' ) ] = $site_info->blogname;
		}
		*/

		return array(
			'version'        => self::plugin_version(),
			'user_id'        => (int) $current_user->ID,
			'blog_id'        => (int) get_current_blog_id(),
			'blog_name'      => $site_name,
			'blog_url'       => rtrim( $site_url, '/' ),
			'runtime_url'    => self::strip_url_scheme( self::runtime_url() ),
			'plugin_dir_url' => self::plugin_dir_url(),
			'ajax_url'       => self::strip_url_scheme( admin_url( 'admin-ajax.php' ) ),
			'instance_sites' => $users_sites,
			'i18n'           => self::i18n(),
		);
	}

	/**
	 * WpPressThis::shortcut_link_override() (plugin only)
	 * Returns the bookmarklet's static code from /js/bookmarklet.js, with a local JS variable set to the current install's path to PT
	 *
	 * @return string Press This bookmarklet JS trigger found in /wp-admin/tools.php
	 */
	public function shortcut_link_override() {
		$url  = esc_js( self::runtime_url() . '?v=' . self::plugin_version() );
		$link = "javascript:";
		$link .= file_get_contents( self::plugin_dir_path() . '/js/bookmarklet.js' );
		$link .= "WpPressThis_Bookmarklet('{$url}')";
		return str_replace( array( "\r", "\n", "\t" ), '', $link );
	}

	/**
	 * WpPressThis::press_this_php_override() (plugin only)
	 * Takes over /wp-admin/press-this.php for backward compatibility and while in feature-as-plugin mode
	 *
	 * @uses $_POST
	 */
	public function press_this_php_override() {
		// Simply drop the following test once/if this becomes the standard Press This code in core
		if ( false === strpos( self::runtime_url(), self::script_name() ) )
			return;

		if ( ! empty( $_FILES['wppt_file'] ) ) {
			/* Not needed. Handled by the media modal.
			if ( current_user_can('upload_files') )
				self::process_file_upload( $_FILES['wppt_file'], $_POST['wppt_nonce'] );
			else
				self::refuse_file_upload( 'current_user_can' );
			*/
		} else {
			if ( ! current_user_can( 'edit_posts' ) || ! current_user_can( get_post_type_object( 'post' )->cap->create_posts ) )
				wp_die( __( 'Cheatin&#8217; uh?' ) );
			self::serve_app_html();
		}
	}

	/** TODO: not needed. Handled by the media modal.
	 * WpPressThis::process_file_upload( $file, $nonce )
	 * Uploads file to an iframe to not have to refresh the page, and feel AJAXy, then calls parent.wp_pressthis_app.file_upload_success()
	 *
	 * @param $file Single $_FILE key
	 * @param $nonce Valid WP nonce
	 *
	 * @uses WpPressThis::refuse_file_upload(), wp_verify_nonce(), wp_handle_upload(), esc_url(), esc_js()
	 */
	public function process_file_upload( $file, $nonce ) {
		if ( ! wp_verify_nonce( $nonce, 'press_this' ) ) {
			self::refuse_file_upload( 'wp_verify_nonce' );
			return;
		}

		if ( ! function_exists( 'wp_handle_upload' ) )
			require_once( ABSPATH . 'wp-admin/includes/file.php' );

		$file_data = wp_handle_upload( $file, array( 'test_form' => false ) );

		if ( empty( $file_data ) || empty( $file_data['url'] ) ) {
			self::refuse_file_upload( 'file_data' );
			return;
		}
		?>
		<script language="javascript" type="text/javascript">
			parent.wp_pressthis_app.file_upload_success( '<?php echo esc_url( $file_data['url'] ); ?>', '<?php echo esc_js( strtolower( $file_data['type'] ) ); ?>' );
		</script>
		<?php
		die();
	}

	/** TODO: not needed, see above.
	 * WpPressThis::refuse_file_upload( $context )
	 * Calls parent.wp_pressthis_app.render_error(), notifying the user her/his upload failed
	 *
	 * @param $context A key-type string to give developers a hint as to where it failed, when reported
	 *
	 * @uses esc_js(), WpPressThis::i18n()
	 */
	public function refuse_file_upload( $context ) {
		$i18n = self::i18n();
		?>
		<script language="javascript" type="text/javascript">
			parent.wp_pressthis_app.render_error( '<?php echo esc_js( $i18n['upload-failed'] ); ?> [<?php echo $context; ?>]' );
		</script>
		<?php
		die();
	}

	/**
	 * WpPressThis::format_post_data_for_save( $status = 'draft' )
	 *
	 * @return array('post_title' => $title, 'post_content' => $content, 'post_status' => $post_status)
	 * @uses $_POST
	 */
	public function format_post_data_for_save( $status = 'draft' ) {
		$i18n = self::i18n();

		// For image-only posts
		if ( empty( $_POST ) ) {
			return array(
				'post_title'   => $i18n['new-post'],
				'post_content' => '',
			);
		}

		$post    = array();
		$content = '';

		if ( ! empty( $_POST['wppt_title'] ) ) {
			$post['post_title'] = sanitize_text_field( trim( $_POST['wppt_title'] ) );
		}

		if ( ! empty( $_POST['pressthis'] ) ) {
			$content = trim( $_POST['pressthis'] ); // The editor textarea, we have to allow this one and let wp_insert_post() filter the content below
		}

		if ( ! empty( $_POST['wppt_selected_img'] ) ) {
			if ( empty( $_POST['wppt_source_url'] ) || false !== strpos( $_POST['wppt_selected_img'], preg_replace( '/^(http:.+)\/wp-admin\/.+/', '\1/wp-content/', self::script_name() ) ) ) {
				$img_link = $_POST['wppt_selected_img'];
			} else {
				$img_link = $_POST['wppt_source_url'];
			}

			// TODO: needs better filtering?
			$content = '<a href="' . esc_url( $img_link ) . '"><img src="' . esc_url( $_POST['wppt_selected_img'] ) . '" /></a>' . $content;
		}

		$post['post_content'] = $content;

		if ( 'publish' === $status ) {
			if ( current_user_can( 'publish_posts' ) ) {
				$post['post_status'] = 'publish';
			} else {
				$post['post_status'] = 'pending';
			}
		} else {
			$post['post_status'] = 'draft';
		}

		return $post;
	}

	/**
	 * WpPressThis::side_load_images( $post_id, $content = '' )
	 * Get the sources images and save them locally, fr posterity, unless we can't.
	 *
	 * @param $post_id int
	 * @param $content string Current expected markup for PT
	 * @return string New markup with old image URLs replaced with the local attachment ones if swapped
	 * @uses current_user_can(), media_sideload_image(), is_wp_error()
	 */
	public function side_load_images( $post_id, $content = '' ) {
		$new_content = $content;

		if ( ! empty( $_POST['wppt_selected_img'] ) && current_user_can( 'upload_files' ) ) {
			foreach( (array) $_POST['wppt_selected_img'] as $key => $image ) {
				//Don't sideload images already hosted on our WP instance
				if ( false !== strpos( $image, preg_replace( '/^(http:.+)\/wp-admin\/.+/', '\1/wp-content/', self::script_name() ) ) )
					continue;
				// Don't try to sideload file without a file extension, leads to WP upload error,
				// then a "PHP Notice:  Undefined offset: 0 in /wp-admin/includes/media.php on line 811"
				// Matching regex to skip from media_sideload_image() in otherwise erroring /wp-admin/includes/media.php
				if ( ! preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $image ) )
				     continue;
				// See if files exist in content - we don't want to upload non-used selected files.
				if ( false !== strpos( $new_content, htmlspecialchars( $image ) ) ) {
					$upload = media_sideload_image( $image, $post_id );
					// Replace the POSTED content <img> with correct uploaded ones. Regex contains fix for Magic Quotes
					if ( ! is_wp_error( $upload ) )
						$new_content = preg_replace( '/<img ([^>]*)src=\\\?(\"|\')'.preg_quote( htmlspecialchars( $image ), '/' ).'\\\?(\2)([^>\/]*)\/*>/is', $upload, $new_content );
				}
			}
		}

		// Error handling for media_sideload, send original content back
		if ( is_wp_error( $new_content ) )
			return $content;

		return $new_content;
	}

	/**
	 * WpPressThis::save( $post_status = 'draft' )
	 * Save the post as draft or published
	 *
	 * @param string $post_status
	 * @return bool|int|WP_Error
	 */
	public function save( $post_status = 'draft' ) {
		if ( ! wp_verify_nonce( $_POST['wppt_nonce'], 'press_this' ) ) {
			return false;
		}

		if ( ! $post_id = (int) $_POST['post_ID'] ) {
			return false;
		}

		$data = self::format_post_data_for_save( $post_status );

		$post = array(
			'ID'             => $post_id,
			'post_title'     => $data['post_title'],
			'post_content'   => $data['post_content'],
			'post_status'    => $data['post_status'],
			'post_type'      => 'post',
		);

		$new_content = self::side_load_images( $post_id, $post['post_content'] );

		if ( ! is_wp_error( $new_content ) ) {
			$post['post_content'] = $new_content;
		}

		return wp_update_post( $post, true );
	}

	/**
	 * WpPressThis::fetch_source_html( $url )
	 *
	 * @return string Source's HTML sanitized markup
	 * @uses download_url(), is_wp_error(), wp_kses(), file_get_contents() , and unlink()
	 */
	public function fetch_source_html( $url ) {
		// Download source page to tmp file
		$source_tmp_file = ( ! empty( $url ) ) ? download_url( $url ) : '';
		$source_content  = '';
		if ( ! is_wp_error( $source_tmp_file ) && file_exists( $source_tmp_file ) ) {
			// Get the content of the source page from the tmp file.
			$source_content = wp_kses(
				file_get_contents( $source_tmp_file ),
				array (
					'img' => array(
						'src'      => array(),
					),
					'link' => array(
						'rel'      => array(),
						'itemprop' => array(),
						'href'     => array(),
					),
					'meta' => array(
						'property' => array(),
						'name'     => array(),
						'content'  => array(),
					)
				)
			);
			// All done with backward compatibility
			// Let's do some cleanup, for good measure :)
			unlink( $source_tmp_file );
		} else if ( is_wp_error( $source_tmp_file ) ) {
			$source_content = new WP_Error( 'upload-error',  sprintf( __( 'Error: %s', 'press-this' ), sprintf( __( 'Could not download the source URL (native error: %s).', 'press-this' ), $source_tmp_file->get_error_message() ) ) );
		} else if ( ! file_exists( $source_tmp_file ) ) {
			$source_content = new WP_Error( 'no-local-file',  sprintf( __( 'Error: %s', 'press-this' ), __( 'Could not save or locate the temporary download file for the source URL.', 'press-this' ) ) );
		}

		return $source_content;
	}

	/**
	 * WpPressThis::source_data_fetch_fallback()
	 * Fetch and parse _meta, _img, and _links data from the source
	 *
	 * @param string $url
	 * @param array $data Existing data array if you have one.
	 *
	 * @return array New data array
	 * @uses self::fetch_source_html()
	 */
	public function source_data_fetch_fallback( $url, $data = array() ) {
		if ( empty( $url ) ) {
			return array();
		}

		// Download source page to tmp file
		$source_content = self::fetch_source_html( $url );
		if ( is_wp_error( $source_content ) ) {
			return array( 'errors' => $source_content->get_error_messages() );
		}

		// Fetch and gather <img> data
		if ( empty( $data['_img'] ) ) {
			$data['_img'] = array();
		}

		if ( preg_match_all( '/<img (.+)[\s]?\/>/', $source_content, $matches ) ) {
			if ( !empty( $matches[0] ) ) {
				foreach ( $matches[0] as $value ) {
					if ( preg_match( '/<img[^>]+src="([^"]+)"[^>]+\/>/', $value, $new_matches ) ) {
						if ( ! in_array( $new_matches[1], $data['_img'] ) ) {
							$data['_img'][] = $new_matches[1];
						}
					}
				}
			}
		}

		// Fetch and gather <iframe> data
		if ( empty( $data['_embed'] ) ) {
			$data['_embed'] = array();
		}

		if ( preg_match_all( '/<iframe (.+)[\s]?\/>/', $source_content, $matches ) ) {
			if ( !empty( $matches[0] ) ) {
				foreach ( $matches[0] as $value ) {
					if ( preg_match( '/<iframe[^>]+src="([^"]+)"[^>]+\/>/', $value, $new_matches ) ) {
						if ( ! in_array( $new_matches[1], $data['_embed'] ) ) {
							if ( preg_match( '/\/\/www\.youtube\.com\/embed\/([^\?]+)\?.+$/', $new_matches[1], $src_matches )
								|| preg_match( '/\/\/player\.vimeo\.com\/video\/([\d]+)$/', $new_matches[1], $src_matches ) ) {
								$data['_embed'][] = $src_matches[1];
							}
						}
					}
				}
			}
		}

		// Fetch and gather <meta> data
		if ( empty( $data['_meta'] ) ) {
			$data['_meta'] = array();
		}

		if ( preg_match_all( '/<meta (.+)[\s]?\/?>/  ', $source_content, $matches ) ) {
			if ( !empty( $matches[0] ) ) {
				foreach ( $matches[0] as $key => $value ) {
					if ( preg_match( '/<meta[^>]+(property|name)="(.+)"[^>]+content="(.+)"/', $value, $new_matches ) ) {
						if ( empty( $data['_meta'][ $new_matches[2] ] ) ) {
							if ( preg_match( '/:?(title|description|keywords)$/', $new_matches[2] ) ) {
								$data['_meta'][ $new_matches[2] ] = str_replace( '&#039;', "'", str_replace( '&#034;', '', html_entity_decode( $new_matches[3] ) ) );
							} else {
								$data['_meta'][ $new_matches[2] ] = $new_matches[3];
								if ( 'og:video' == $new_matches[2] || 'og:video:secure_url' == $new_matches[2] ) {
									if ( preg_match( '/\/\/www\.youtube\.com\/v\/([^\?]+)/', $new_matches[3], $src_matches ) ) {
										if ( ! in_array( 'https://www.youtube.com/watch?v=' . $src_matches[1], $data['_embed'] ) ) {
											$data['_embed'][] = 'https://www.youtube.com/watch?v=' . $src_matches[1];
										}
									} else if ( preg_match( '/\/\/vimeo.com\/moogaloop\.swf\?clip_id=([\d]+)$/', $new_matches[3], $src_matches ) ) {
										if ( ! in_array( 'https://vimeo.com/' . $src_matches[1], $data['_embed'] ) ) {
											$data['_embed'][] = 'https://vimeo.com/' . $src_matches[1];
										}
									}
								} else if ( 'og:image' == $new_matches[2] || 'og:image:secure_url' == $new_matches[2] ) {
									if ( ! in_array( $new_matches[3], $data['_img'] ) ) {
										$data['_img'][] = $new_matches[3];
									}
								}
							}
						}
					}
				}
			}
		}

		// Fetch and gather <link> data
		if ( empty( $data['_links'] ) ) {
			$data['_links'] = array();
		}

		if ( preg_match_all( '/<link (.+)[\s]?\/>/', $source_content, $matches ) ) {
			if ( !empty( $matches[0] ) ) {
				foreach ( $matches[0] as $key => $value ) {
					if ( preg_match( '/<link[^>]+(rel|itemprop)="([^"]+)"[^>]+href="([^"]+)"[^>]+\/>/', $value, $new_matches ) ) {
						if ( 'alternate' == $new_matches[2] || 'thumbnailUrl' == $new_matches[2] || 'url' == $new_matches[2] ) {
							if ( empty( $data['_links'][ $new_matches[2] ] ) ) {
								$data['_links'][ $new_matches[2] ] = $new_matches[3];
							}
						}
					}
				}
			}
		}

		return $data;
	}

	/**
	 * WpPressThis::merge_or_fetch_data()
	 * This code handles making our version of Press This backward compatible with the previous/legacy version by supporting its query string params
	 *
	 * @return array
	 */
	public function merge_or_fetch_data() {
		// Merge $_POST and $_GET, as appropriate ($_POST > $_GET), to remain backward compatible
		$data = array_merge_recursive( $_POST, $_GET );

		// Get the legacy QS params, or equiv POST data
		$data['u'] = ( !empty( $data['u'] ) && preg_match( '/^https?:/', $data['u'] ) ) ? $data['u'] : '';
		$data['s'] = ( !empty( $data['s'] ) ) ? $data['s'] : '';
		$data['t'] = ( !empty( $data['t'] ) ) ? $data['t'] : '';

		// If no _meta (a new thing) was passed via $_POST, fetch data from source as fallback, makes PT fully backward compatible
		if ( empty( $data['_meta'] ) && ! empty( $data['u'] ) ) {
			$data = self::source_data_fetch_fallback( $data['u'], $data );
		}

		return $data;
	}

	/**
	 * WpPressThis::serve_app_html()
	 * Serves the app's base HTML, which in turns calls the load.js
	 *
	 * @uses $_POST, WpPressThis::runtime_url(), WpPressThis::plugin_dir_url()
	 */
	public function serve_app_html() {
		global $wp_locale;

		// Get i18n strings
		$i18n                     = self::i18n();

		// Get data, new (POST) and old (GET)
		$data                     = self::merge_or_fetch_data();

		// Get site settings array/data
		$site_settings            = self::site_settings();

		// Get a fresh nonce
		$nonce                    = wp_create_nonce( 'press_this' );

		// Set the passed data
		$data['_version']         = $site_settings['version'];
		$data['_runtime_url']     = $site_settings['runtime_url'];
		$data['_plugin_dir_url']  = $site_settings['plugin_dir_url'];
		$data['_ajax_url']        = $site_settings['ajax_url'];
		$data['_nonce']           = $nonce;

		// Generate some include paths (plugin only)
		$wp_js_inc_dir            = preg_replace( '/^(.+)\/wp-admin\/.+$/', '\1/wp-includes/js', $site_settings['runtime_url'] );
		$json_js_inc              = $wp_js_inc_dir . '/json2.js';
		$jquery_js_inc            = $wp_js_inc_dir . '/jquery/jquery.js';
		$app_css_inc              = $site_settings['plugin_dir_url'] . '/css/press-this.css';
		$load_js_inc              = $site_settings['plugin_dir_url'] . '/js/load.js';
		$form_action              = $site_settings['runtime_url'];
		$upload_action            = preg_replace( '/^(.+)\/press-this\.php$/', '\1/media-upload.php', $site_settings['runtime_url'] ) . '?referer=wptuts-settings&type=image&TB_iframe=true&post_id=0';
		$txt_domain               = 'press-this';

		// Echo HTML
?>
<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width">
	<title><?php echo esc_html( $i18n['press-this'] ) ?></title>
	<link rel='stylesheet' id='all-css' href='<?php echo $app_css_inc ?>' type='text/css' media='all' />
	<script language="JavaScript">
		window.wp_pressthis_data   = <?php echo json_encode( $data ) ?>;
		window.wp_pressthis_config = <?php echo json_encode( $site_settings ) ?>;
	</script>
	<script src="<?php echo esc_url( $json_js_inc ) ?>" language="JavaScript"></script>
	<script src="<?php echo esc_url( $jquery_js_inc ) ?>" language="JavaScript"></script>
	<script src="<?php echo esc_url( $load_js_inc ) ?>" language="JavaScript"></script>

	<script type="text/javascript">
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>',
		pagenow = 'press-this',
		typenow = 'post',
		adminpage = 'press-this-php',
		thousandsSeparator = '<?php echo addslashes( $wp_locale->number_format['thousands_sep'] ); ?>',
		decimalPoint = '<?php echo addslashes( $wp_locale->number_format['decimal_point'] ); ?>',
		isRtl = <?php echo (int) is_rtl(); ?>;
	</script>

	<?php
		// $post->ID is needed for the embed shortcode so we can show oEmbed previews in the editor. Maybe find a way without it.
		$post = get_default_post_to_edit( 'post', true );

		wp_enqueue_media( array( 'post' => $post->ID ) );
		wp_enqueue_script( 'editor' );

		wp_enqueue_script( 'press-this-app', self::strip_url_scheme( plugin_dir_url( __FILE__ ) ) . 'js/app.js', array(), false, true );
	?>
</head>
<body>
	<div id="wppt_adminbar" class="adminbar">
		<h1 id="wppt_current_site" class="current-site">
			<span class="dashicons dashicons-wordpress-alt"></span>
			<a href="#" target="_blank"></a>
		</h1>
		<ul id="wppt_sites" class="site-list">
		<?php
			foreach( (array) $site_settings['instance_sites'] as $instance_url => $instance_name ) {
				$instance_url = untrailingslashit( $instance_url );
				echo '<li '
				               .  ' class="wppt_site_entry ' . ( ( untrailingslashit( $site_settings['blog_url'] ) == self::strip_url_scheme( $instance_url ) ) ? 'entry-selected': '' ) . '" '
				               .  ' data-url="' . esc_url( $instance_url ) . '">'
				               .  '<a class="wppt_site_entry_link" href="' . esc_url( $instance_url . '/wp-admin/press-this.php?v=' . self::plugin_version() . '&buster=' . time() ) . ( ( ! empty( $data['u'] ) ) ? '&u=' . urlencode( $data['u'] ) : '' ) . '">' . esc_html( $instance_name ) . '</a></li>';
			}
		?>
			<li class="add-site">
				<form id="wppt_sites_form" name="wppt_sites_form" action="<?php echo esc_url( $upload_action ) ?>" method="GET">
					<input type="text" name="wppt_new_site" id="wppt_new_site" class="add-site__url" value="" placeholder="<?php echo esc_attr( $i18n['enter-wp-url'] ) ?>" />
					<input type="submit" name="wppt_new_site_submit" id="wppt_new_site_submit" class="add-site__submit" value="<?php echo esc_attr( $i18n['add'] ) ?>" style="display:none"/>
					<a href="" class="add-site__submit">
						<span class="dashicons dashicons-plus"></span>
						Add
					</a>
				</form>
			</li>
		</ul>
	</div>
	<div id="wppt_scanbar" class="scan">
		<form action="<?php echo esc_url( $form_action ) ?>" method="GET">
			<input type="url" name="u" id="wppt_url_scan" class="scan__url" value="" placeholder="<?php echo esc_attr( $i18n['enter-url-to-scan'] ) ?>" />
			<input type="submit" name="wppt_url_scan_submit" id="wppt_url_scan_submit" class="scan__submit" value="<?php echo esc_attr( $i18n['scan'] ) ?>" />
		</form>
	</div>

	<form id="wppt_form" name="wppt_form" method="POST" action="<?php echo esc_url( $form_action ) ?>" target="_self">
		<input type="hidden" name="post_ID" id="post_ID" value="<?php echo esc_attr( $post->ID ); ?>" />
		<input type="hidden" name="wppt_nonce" id="wppt_nonce_field" value="<?php echo esc_attr( $nonce ) ?>"/>
		<input type="hidden" name="wppt_title" id="wppt_title_field" value=""/>
		<input type="hidden" name="wppt_selected_img" id="wppt_selected_img_field" value=""/>
		<input type="hidden" name="wppt_source_url" id="wppt_source_url_field" value=""/>
		<input type="hidden" name="wppt_source_name" id=wppt_source_name_field" value=""/>

	<div id='wppt_app_container' class="editor">
		<h2 id='wppt_title_container' class="post__title" contenteditable="true"></h2>
		<div id='wppt_featured_image_container' class="featured-container">
			<img src="" id="wppt_selected_img" class="featured-image" width="400" height="300" />
			<div role="group">
				<a role="button" href="#" title="<?php echo esc_attr( $i18n['show-all-media'] ) ?>" id="wppt_all_media_switch" class="icon-button--dark dashicons dashicons-images-alt2"><span class="screen-reader-text"><?php _e( 'Switch featured image' ); ?></span></a>
				<a role="button" href="#" title="<?php echo esc_attr( $i18n['no-media'] ) ?>" id="wppt_no_image" class="icon-button--dark dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Remove featured image' ); ?></span></a>
			</div>
			<div id='wppt_all_media_widget' class="all-media">
				<div id='wppt_all_media_container'></div>
			</div>
		</div>

		<?php

		wp_editor( '', 'pressthis', array(
			'drag_drop_upload' => true,
	//		'tabfocus_elements' => 'content-html,save-post',
			`editor_class` => 'press-thiss__editor',
			'editor_height' => 350,
			'teeny' => true,
			'tinymce' => array(
				'resize' => false,
				'wordpress_adv_hidden' => false,
	//			'add_unload_trigger' => false,
	//			'statusbar' => false,
				'plugins' => 'lists,media,paste,tabfocus,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpview',
				'toolbar1' => 'bold,italic,bullist,numlist,blockquote,link,unlink',
				'toolbar2' => 'undo,redo'
			),
			'quicktags' => false,
		) );

		?>
	</div>

	<div class="actions">
		<div class="post-actions">
			<input type="submit" class="button--subtle" name="wppt_draft" id="wppt_draft_field" value="<?php echo esc_attr( $i18n['save-draft'] ) ?>"/>
			<input type="submit" class="button--primary" name="wppt_publish" id="wppt_publish_field" value="<?php echo esc_attr( $i18n['new-post'] ) ?>"/>
		</div>
	</div>
	</form>

	<?php

		// TODO: consider running "special" press-this hooks here?
		// Maybe better so we don't output stuff accidentaly added by plugins. Would probably prevent some errors.
		do_action( 'admin_footer', '' );
		do_action( 'admin_print_footer_scripts' );

	?>
</body>
</html>
<?php
		die();
	}

	/**
	 * WpPressThis::press_this_ajax_site_settings()
	 * Ajax endpoint to serve the results of WpPressThis::press_this_ajax_site_settings()
	 *
	 * @uses admin_url(), wp_create_nonce()
	 */
	public function ajax_site_settings() {
		header( 'content-type: application/json' );
		echo json_encode( self::site_settings() );
		die();
	}

	/**
	 * @param $post_id
	 * @param string $post_status
	 */
	public function post_save_json_response( $post_id, $post_status = 'draft' ) {
		$i18n = self::i18n();
		header( 'content-type: application/json' );
		if ( is_wp_error( $post_id ) || intval( $post_id ) < 1 ) {
			echo json_encode( array( 'error' => $i18n['unexpected-error'] ) );
		} else {
			echo json_encode( array( 'post_id' => $post_id, 'post_permalink' => get_post_permalink( $post_id ), 'post_status' => $post_status ) );
		}
		die();
	}

	/**
	 * Ajax endpoint save a draft post
	 */
	public function ajax_draft_post() {
		self::post_save_json_response( self::save( 'draft' ) );
	}

	/**
	 * Ajax endpoint publish a post
	 */
	public function ajax_publish_post() {
		self::post_save_json_response( self::save( 'publish' ), 'published' );
	}

	/**
	 * Experimental Ajax endpoint to publish a site-specific Chrome extension manifest
	 */
	public function  ajax_chrome_ext_manifest() {
		$i18n        = self::i18n();
		$plugin_data = self::plugin_data();
		$plugin_name = ( ! empty( $plugin_data['Name'] ) ) ? $plugin_data['Name'] : $i18n[ 'press-this' ];
		$plugin_desc = ( ! empty( $plugin_data['Description'] ) ) ? $plugin_data['Description'] : __( 'Posting images, links, and cat gifs will never be the same.', 'press-this' );
		$icon        = './images/wordpress-logo-notext-rgb.png';
		$type        = ( true == false ) ? 'app' : 'extension';
		$manifest = array(
			'manifest_version' => 2,
			'name'             => $plugin_name,
			'description'      => $plugin_desc,
			'version'          => self::plugin_version(),
			'icons'            => array(
				'128' => $icon,
			),
			'web_accessible_resources' => array( $icon ),
		);

		if ( 'app' == $type ) {
			$manifest['permissions'] = array(
				'unlimitedStorage',
				'notifications',
				'geolocation',
				'clipboardRead',
				'clipboardWrite',
				'background',
				// 'activeTab',
				// preg_replace( '/^(.+)\/wp-admin\/.+$/', '\1/', self::runtime_url() ),
			);
			$manifest['app']         = array(
				'launch' => array(
					'web_url' => self::runtime_url(),
				),
			);
		} else {
			$manifest['permissions'] = array(
				'activeTab',
				// preg_replace( '/^(.+)\/wp-admin\/.+$/', '\1/', self::runtime_url() ),
			);
			$manifest['background'] = array(
				'scripts'    => array(
					'./js/ext-chrome-background.js'
					// "vendor/jquery-2.0.3.min.js", "background-lib.js", "background.js"
				),
				'persistent' => false,
			);
			$manifest['browser_action'] = array(
				'default_title' => $plugin_name,
			);
		}
		/*
		$manifest['urls'] = array(
			preg_replace( '/^http(.+)\/wp-admin\/.+$/', '*://\1/', self::runtime_url() )
		);
		$manifest['intents'] = array(
			'http://webintents.org/share' => array(
				'type' => array(
					'image/*',
					'video/*',
					'text/*'
				),
				'href' => self::runtime_url(),
				'title' => 'Share this on your WordPress blog',
				'disposition'=> 'window'
			),
		);
		*/
		header( 'content-type: application/json' );
		echo json_encode( $manifest );
		die();
	}
}

new WpPressThis;
