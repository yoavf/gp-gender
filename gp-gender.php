<?php
/*
Plugin Name: GP Gender
Plugin URI: http://translate.wordpress.org
Description: Add support for WordPress Gender functions to GlotPress
Version: 1.0
Author: Yoav Farhi
Author URI: https://yoav.blog
Tags: glotpress, glotpress plugin 
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

class GP_Gender {

	public function __construct() {
		add_filter( 'gp_for_translation_rows', array( $this, 'for_translation') );

		// TODO: this only work on translations_get, set it up for CLI too.
		add_filter( 'gp_export_translations_entries', array( $this, 'for_export') );

		add_filter( 'gp_number_of_translations', array( $this, 'number_of_translations'), 10, 2 );
		add_filter( 'gp_translation_row_override_textareas', array( $this, 'override_textareas'), 10, 2 );

		add_action( 'gp_translation_row_textareas', array( $this, 'translation_row_textareas'), 10, 5 );
	}

	public function number_of_translations( $number, $row ) {
		if ( isset( $row->is_gender ) ) {
			return '3';
		}

		return $number;
	}

	public function for_translation( $rows ) {
		//TODO: use translation set info to skip if language has no gender grammar
		foreach ( $rows as $row ) {
			if ( gp_startswith( $row->context, '_wpg' ) ) {
				// Don't display our gender context prefix
				$row->context = str_replace( '_wpg', '', $row->context );
				$row->is_gender = true;
			}
		}
		return $rows;
	}

	public function for_export( $entries ) {
		//TODO: use translation set info to skip if language has no gender grammar
		$g_entries = array();
		foreach ( $entries as $key => $entry ) {
			if ( isset( $entry->is_gender ) ) {
				foreach ( array( 'netural', 'female', 'male' ) as $i => $gender ) {
					$g_entry = clone $entry;
					$g_entry->context =  '_wpg_' . $gender . '_' . $g_entry->context;
					$g_entry->translations = array();
					$g_entry->translations[] = $entry->translations[ $i ];
					$g_entries[] = $g_entry ;
				}
				unset( $entries[ $key ] );
			}
		}
		return $entries + $g_entries;
	}

	public function override_textareas( $override, $translation ) {
		return isset( $translation->is_gender );
	}

	public function translation_row_textareas( $t, $singular, $plural, $can_edit, $can_approve_translation ) {
		printf( '<p>' . __( 'Neutral: %s', 'glotpress' ), '<span class="original">'. $singular .'</span></p>');
		textareas( $t, array( $can_edit, $can_approve_translation ), 0 );

		printf( '<p class="clear">' . __( 'Female: %s', 'glotpress' ), '<span class="original">'. $singular .'</span></p>');
		textareas( $t, array( $can_edit, $can_approve_translation ), 1 );
		printf( '<p class="clear">' . __( 'Male: %s', 'glotpress' ), '<span class="original">'. $singular .'</span></p>');
		textareas( $t, array( $can_edit, $can_approve_translation ), 2 );
	}
}


function gp_gender_init() {
	global $gp_gender;
	if ( ! isset( $gp_gender ) ) {
		$gp_gender = new Gp_Gender();
	}

	return $gp_gender;
}
add_action( 'gp_init', 'gp_gender_init' );
