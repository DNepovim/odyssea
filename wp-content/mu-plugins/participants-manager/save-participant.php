<?php

include '../../../wp-load.php';

if ( isset( $_POST['submitted'] )
     && isset( $_POST['post_nonce_field'] )
     && wp_verify_nonce( $_POST['post_nonce_field'], 'post_nonce' )
) {

	$fields = ptcm_add_default_questions();

	foreach ( $fields as $partition ) {
		foreach ( $partition['fields'] as $field ) {
			$value = trim( $_POST[ $field['id'] ] );
			if ( ! isset( $value ) ) {
				$err[ $field['id'] ] = true;
				$hasError            = true;
			}
		}
	}

	if ( $hasError ) {
		$query = '?';
		foreach ( $err as $key => $value ) {
			$query .= $key . '=' . $value . '&';
		}
		wp_redirect( home_url( $query ) );
		exit;
	}

	$post_information = array(
		'post_title'  => $_POST['ptcm_firstname'] . ' ' . $_POST['ptcm_surname'],
		'post_type'   => 'ptcm_' . $_POST['year'],
		'post_status' => 'private'
	);

	$post_id = wp_insert_post( $post_information );

	foreach ( $fields as $partition ) {
		foreach ( $partition['fields'] as $field ) {
			$value = trim( $_POST[ $field['id'] ] );
			add_post_meta( $post_id, $field['id'], $value );
		}
	}

	if ( $post_id ) {
		if ( wp_get_referer() ) {
			wp_safe_redirect( wp_get_referer() );
		} else {
			wp_safe_redirect( get_home_url() );
		}
	}
}
