<?php

namespace SSSS\ContactForm7Filters;

add_filter( 'wpcf7_spam', __NAMESPACE__ . '\check_form_submission', 10, 1 );

function check_form_submission( $spam ) {
	if ( $spam ) {
		return $spam;
	}

	if ( ! $params = get_params() ) {
		return false;
	}

	$form_content = strtolower( $params['content'] );

	// There are a few words that can always be considered spam.
	foreach ( \SSSS\Common\get_spam_word_list() as $word ) {

		// Anything containing a blacklisted word is marked as spam.
		if ( false !== strpos( $form_content, $word ) ) {
			return true;
		}
	}

	// A hazard guess that most sites don't deal with doses.
	if ( \SSSS\Common\contains_mg( $form_content ) ) {
		return true;
	}

	if ( \SSSS\Common\contains_sex_combo( $form_content ) ) {
		return true;
	}

	return $spam;
}

function get_params() {
	$params = array(
		'author' => '',
		'author_email' => '',
		'author_url' => '',
		'content' => '',
	);

	foreach ( (array) $_POST as $key => $val ) {
		if ( '_wpcf7' == substr( $key, 0, 6 )
		or '_wpnonce' == $key ) {
			continue;
		}

		if ( is_array( $val ) ) {
			$val = implode( ', ', array_flatten( $val ) );
		}

		$val = trim( $val );

		if ( 0 == strlen( $val ) ) {
			continue;
		}

		$params['content'] .= "\n\n" . $val;
	}

	$params['content'] = trim( $params['content'] );

	return $params;
}

function array_flatten( $input ) {
	if ( ! is_array( $input ) ) {
		return array( $input );
	}

	$output = array();

	foreach ( $input as $value ) {
		$output = array_merge( $output, array_flatten( $value ) );
	}

	return $output;
}
