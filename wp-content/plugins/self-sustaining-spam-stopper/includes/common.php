<?php

namespace SSSS\Common;

function get_spam_word_list() {
	return array(
		'viagra',
		'cialis',
		'albendazole',
		' mg',
		'mining crypto',
	);
}

function contains_mg( $text ) {
	if ( 1 === preg_match( '/(\d+\s?mg)/', $text ) ) {
		return true;
	}

	return false;
}

function get_combo_word_list() {
	return array(
		'girl',
		'woman',
		'women',
		'online',
		'local',
		'city',
	);
}

function contains_sex_combo( $text ) {
	if ( false === strpos( $text, 'sex' ) ) {
		return false;
	}

	foreach ( get_combo_word_list() as $word ) {
		if ( false !== strpos( $text, $word ) ) {
			return true;
		}
	}

	return false;
}
