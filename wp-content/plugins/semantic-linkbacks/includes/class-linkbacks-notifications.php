<?php

/**
 * Semantic linkbacks class
 *
 * @author Matthias Pfefferle
 */
class Linkbacks_Notifications {
	/**
	 * Initialize the plugin, registering WordPress hooks.
	 */
	public static function init() {
		// Comment Notification Filters
		add_filter( 'comment_moderation_text', array( 'Linkbacks_Notifications', 'comment_moderation_text' ), 10, 2 );
		add_filter( 'comment_moderation_subject', array( 'Linkbacks_Notifications', 'comment_moderation_subject' ), 10, 2 );
		add_filter( 'comment_notification_text', array( 'Linkbacks_Notifications', 'comment_notification_text' ), 10, 2 );
		add_filter( 'comment_notification_subject', array( 'Linkbacks_Notifications', 'comment_notification_subject' ), 10, 2 );
		add_filter( 'ckpn_newcomment_subject', array( 'Linkbacks_Notifications', 'comment_notification_subject' ), 10, 2 );
		add_filter( 'ckpn_newcomment_message', array( 'LinkbackS_Notifications', 'basic_notification_text' ), 10, 2 );
		add_filter( 'fnpn_newcomment_subject', array( 'Linkbacks_Notifications', 'comment_notification_subject' ), 10, 2 );
		add_filter( 'fnpn_newcomment_message', array( 'LinkbackS_Notifications', 'basic_notification_text' ), 10, 2 );

		if ( WP_DEBUG ) {
			// For testing outgoing comment email
			add_filter( 'bulk_actions-edit-comments', array( 'Linkbacks_Notifications', 'register_bulk_send' ) );
			add_filter( 'handle_bulk_actions-edit-comments', array( 'Linkbacks_Notifications', 'bulk_send_notifications' ), 10, 3 );
		}
	}

	/**
	 * Register Bulk send outgoing email
	 */
	public static function register_bulk_send( $bulk_actions ) {
		$bulk_actions['resend_notification_email'] = __( 'Resend Comment Email', 'semantic-linkbacks' );
		$bulk_actions['resend_moderation_email']   = __( 'Resend Moderation Email', 'semantic-linkbacks' );
		if ( function_exists( 'ckpn_new_comment' ) ) {
			$bulk_actions['send_pushover_text'] = __( 'Send Pushover Note', 'semantic-linkbacks' );
		}
		if ( function_exists( 'fnpn_new_comment' ) ) {
			$bulk_actions['send_pushbullet_text'] = __( 'Send Pushbullet Note', 'semantic-linkbacks' );
		}
		return $bulk_actions;
	}

	/**
	 * Handle Bulk Send Outgoing EMail
	 */
	public static function bulk_send_notifications( $redirect_to, $doaction, $comment_ids ) {
		if ( ! in_array( $doaction, array( 'resend_notification_email', 'resend_moderation_email', 'send_pushover_text' ), true ) ) {
			return $redirect_to;
		}
		foreach ( $comment_ids as $comment_id ) {
			switch ( $doaction ) {
				case 'resend_notification_email':
					wp_notify_postauthor( $comment_id );
					break;
				case 'resend_moderation_email':
					wp_notify_moderator( $comment_id );
					break;
				case 'send_pushover_text':
					ckpn_new_comment( $comment_id );
					break;
				case 'send_pushbullet_text':
					fnpn_new_comment( $comment_id );
					break;
			}
		}
		return $redirect_to;
	}

	/**
	 * Filter the comment notification text
	 *
	 * @param string $notify_message
	 * @param int $comment_id comment
	 * @return string $notify_message
	 */
	public static function comment_notification_text( $notify_message, $comment_id ) {
		$comment = get_comment( $comment_id );
		$post    = get_post( $comment->comment_post_ID );
		if ( ! Linkbacks_Handler::get_type( $comment ) ) {
			return $notify_message;
		}
		$notify_message  = self::notification( $comment );
		$notify_message .= self::notification_body( $comment );
		if ( user_can( $post->post_author, 'edit_comment', $comment->comment_ID ) ) {
			$notify_message .= self::moderate_text( $comment ) . "\r\n";
		}
		return $notify_message;

	}

	/**
	 * Generate the moderation text
	 *
	 * @param int|WP_Comment $comment comment
	 * @return string $message Appropriate text.
	 */
	public static function moderate_text( $comment ) {
		$comment = get_comment( $comment );
		/* translators: Comment moderation. 1: Comment action URL */
		$message = sprintf( __( 'Approve it: %s', 'semantic-linkbacks' ), admin_url( "comment.php?action=approve&c={$comment_id}#wpbody-content" ) ) . "\r\n";
		if ( EMPTY_TRASH_DAYS ) {
			/* translators: Trash it URL */
			$message .= sprintf( __( 'Trash it: %s', 'semantic-linkbacks' ), admin_url( "comment.php?action=trash&c={$comment->comment_ID}#wpbody-content" ) ) . "\r\n";
		} else {
			/* translators: Delete it URL */
			$message .= sprintf( __( 'Delete it: %s', 'semantic-linkbacks' ), admin_url( "comment.php?action=delete&c={$comment->comment_ID}#wpbody-content" ) ) . "\r\n";
		}
		/* translators: Spam it URL */
		$message .= sprintf( __( 'Spam it: %s', 'semantic-linkbacks' ), admin_url( "comment.php?action=spam&c={$comment->comment_ID}#wpbody-content" ) ) . "\r\n";
		return $message;
	}

	/**
	 * Filter the comment notification subject
	 *
	 * @param string $subject
	 * @param int $comment_id comment
	 * @return string $subject
	 */
	public static function comment_notification_subject( $subject, $comment_id ) {
		$comment = get_comment( $comment_id );
		$type    = Linkbacks_Handler::get_type( $comment );
		if ( ! $type ) {
			return $subject;
		}
		$title    = get_the_title( $comment->comment_post_ID );
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		/* translators: 1: blog name, 2: Semantic Linkbacks type, 3: post title */
		return sprintf( __( '[%1$s] %2$s: %3$s', 'semantic-linkbacks' ), $blogname, $type, $title );
	}

	/**
	 * Filter the comment moderation text
	 *
	 * @param string $notify_message
	 * @param int $comment_id comment
	 * @return string $notify_message
	 */
	public static function comment_moderation_text( $notify_message, $comment_id ) {
		$comment         = get_comment( $comment_id );
		$notify_message  = self::moderation( $comment ) . "\r\n\r\n";
		$notify_message .= self::notification_body( $comment ) . "\r\n\r\n";
		$notify_message .= self::moderate_text( $comment );
		return $notify_message;
	}

	/**
	 * Filter a notification text
	 * @param string $notify_messsage
	 * @param int|WP_Comment $comment
	 * @return string $notify_basic
	 */
	public static function basic_notification_text( $notify_message, $comment_id ) {
		$comment = get_comment( $comment_id );
		$type    = Linkbacks_Handler::get_type( $comment );
		if ( ! $type ) {
			return '';
		}
		$type         = Linkbacks_Handler::get_comment_type_strings( $type );
		$comment_link = get_comment_link( $comment );
		$notify_basic = wp_strip_all_tags( get_comment_text( $comment ) );
		return $notify_basic;
	}


	/**
	 * Filter the comment moderation subject
	 *
	 * @param string $subject
	 * @param int $comment_id comment
	 * @return string $subject
	 */
	public static function comment_moderation_subject( $subject, $comment_id ) {
		$comment = get_comment( $comment_id );
		$type    = Linkbacks_Handler::get_type( $comment );
		if ( ! $type ) {
			return $subject;
		}
		$type     = Linkbacks_Handler::get_comment_type_strings( $type );
		$title    = get_the_title( $comment->comment_post_ID );
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		/* translators: Comment moderation notification email subject. 1: Site name, 2: Semantic Linkbacks type, 3: Post title */
		return sprintf( __( '[%1$s] Please moderate a %2$s: %3$s', 'semantic-linkbacks' ), $blogname, $type, $title );
	}


	/**
	 * Generate the body of the notification
	 *
	 * @param int|WP_Comment $comment comment
	 * @return string $message Appropriate text.
	 */
	public static function notification_body( $comment ) {
		$comment = get_comment( $comment );
		$type    = Linkbacks_Handler::get_type( $comment );
		if ( ! $type ) {
			return '';
		}
		$type = Linkbacks_Handler::get_comment_type_strings( $type );
		/* translators: 1: website name, 2: website URL */
		$notify_text[] = sprintf( __( 'Author: %1$1s(%2$2s) ', 'semantic-linkbacks' ), get_comment_author( $comment ), Linkbacks_Handler::get_author_url( $comment ) );
		/* translators: Semantic Linkback comment type string */
		$notify_text[] = sprintf( __( 'Semantic Type: %s', 'semantic-linkbacks' ), $type );
		/* translators: URL */
		$notify_text[] = sprintf( __( 'URL: %s ', 'semantic-linkbacks' ), Linkbacks_Handler::get_url( $comment ) );
		/* translators: Comment Text */
		$notify_text[] = sprintf( __( 'Text: %s', 'semantic-linkbacks' ), "\r\n" . wp_strip_all_tags( get_comment_text( $comment ) ) );
		/* translators: 1. Post Permalink */
		$notify_text[] = "\r\n" . sprintf( __( 'You can see all responses to this post here: %1s#comments', 'semantic-linkbacks' ), get_permalink( $comment->comment_post_ID ) );
		/* translators: Comment Permalink */
		$notify_text[] = sprintf( __( 'Permalink: %s', 'semantic-linkbacks' ), get_comment_link( $comment ) );
		return implode( "\r\n", $notify_text );
	}

	/**
	 * Generate the notification which can be placed at the top of the email or as a text notification
	 *
	 * @param int|WP_Comment $comment comment
	 * @return string $message Appropriate text.
	 */
	public static function notification( $comment ) {
		$comment = get_comment( $comment );
		$type    = Linkbacks_Handler::get_type( $comment );
		if ( ! $type ) {
			return '';
		}
		$type = Linkbacks_Handler::get_comment_type_strings( $type );
		/* translators: 1. Semantic Linkbacks type, 2. Post Title */
		return sprintf( __( 'New %1$1s on your post "%2$2s"', 'semantic-linkbacks' ), $type, get_the_title( $comment->comment_post_ID ) );
	}

	/**
	 * Generate the moderation notification which can be placed at the top of the email or as a text notification
	 *
	 * @param int|WP_Comment $comment comment
	 * @return string $message Appropriate text.
	 */
	public static function moderation( $comment ) {
		$type = Linkbacks_Handler::get_type( $comment );
		if ( ! $type ) {
			return '';
		}
		$type = Linkbacks_Handler::get_comment_type_strings( $type );
		/* translators: 1. Semantic Linkbacks type, 2. Post Title */
		return sprintf( __( 'A new %1$1s on the post "%2$2s" is waiting for your approval', 'semantic-linkbacks' ), $type, get_the_title( $comment->comment_post_ID ) );
	}
}
