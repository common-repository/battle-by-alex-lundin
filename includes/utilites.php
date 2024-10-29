<?php

const battle_table_name         = 'battle_items_table';
const battle_comment_table_name = 'battle_comments_table';

if ( ! function_exists( 'battles_admin_role' ) ) {
	function battles_admin_role() {
		if ( current_user_can( 'administrator' ) ) {
			return 'administrator';
		}
		$roles = apply_filters( 'battles_admin_role', array( 'administrator' ) );
		if ( is_string( $roles ) ) {
			$roles = array( $roles );
		}
		foreach ( $roles as $role ) {
			if ( current_user_can( $role ) ) {
				return $role;
			}
		}

		return false;
	}
}

function get_battle( $id ) {
	global $wpdb;
	$dbBattle   = $wpdb->prefix . battle_table_name;
	$dbComments = $wpdb->prefix . battle_comment_table_name;
	$head       = $wpdb->get_results( "SELECT post_title, post_content, post_modified FROM $wpdb->posts WHERE ID = $id" );
	$arguments  = $wpdb->get_results( "SELECT * FROM $dbBattle WHERE `id_item` = $id" );
	$comments = $wpdb->get_results("SELECT * FROM $dbComments WHERE `comment_battle_id` = $id");

	if ( ! empty( $head ) ) {
		$response = [
			'id'                   => $id,
			'title'                => $head[0]->post_title,
			'content'              => $head[0]->post_content,
			'date'                 => $head[0]->post_modified,
			'first_argument_head'  => get_post_meta( $id, 'first_argument', true ),
			'second_argument_head' => get_post_meta( $id, 'second_argument', true ),
			'rating'               => get_post_meta( $id, 'rating', true ),
			'count_views'          => get_post_meta( $id, 'count_view', true ),
			'username'             => get_post_meta( $id, 'username', true ),
			'arguments'            => [],
			'comments'             => []
		];

		foreach ( $arguments as $argument ) {
			$response['arguments'][] = [
				'id'         => $argument->id,
				'id_item'    => $argument->id_item,
				'argument'   => $argument->argument,
				'rating'     => $argument->rating,
				'title'      => $argument->title,
				'text'       => $argument->text,
				'moderate'   => $argument->moderate,
				'username'   => $argument->username,
				'email'      => $argument->email,
				'created_at' => $argument->created_at,
			];
		}

		foreach ($comments as $comment) {
			$response['comments'][] = [
				'id'                  => $comment->id,
				'comment_battle_id'   => $comment->comment_battle_id,
				'comment_argument_id' => $comment->comment_argument_id,
				'comment_author'      => $comment->comment_author,
				'comment_date'        => $comment->comment_date,
				'comment_text'        => $comment->comment_text,
				'comment_moderate'    => $comment->comment_moderate,
				'comment_rating'      => $comment->comment_rating,
				'comment_parent'      => $comment->comment_parent
			];
		}
	} else {
		$response = [];
	}

	return $response;
}

function get_argument( $id ) {
	global $wpdb;
	$dbBattle = $wpdb->prefix . battle_table_name;
	$argument = $wpdb->get_results( "SELECT * FROM $dbBattle WHERE `id` = $id" );

	if ( ! empty( $argument ) ) {
		$response = [
			'id'         => $argument[0]->id,
			'id_item'    => $argument[0]->id_item,
			'argument'   => $argument[0]->argument,
			'title'      => $argument[0]->title,
			'text'       => $argument[0]->text,
			'moderate'   => (int) $argument[0]->moderate,
			'rating'     => (int) $argument[0]->rating,
			'username'   => $argument[0]->username,
			'email'      => $argument[0]->email,
			'remember'   => (int) $argument[0]->remember,
			'created_at' => $argument[0]->created_at
		];
	} else {
		$response = [];
	}

	return $response;
}

function get_battle_comment( $id ) {
	global $wpdb;
	$dbComments = $wpdb->prefix . battle_comment_table_name;

	$comment = $wpdb->get_results( "SELECT * FROM $dbComments WHERE `id` = $id" );

	if ( ! empty( $comment ) ) {
		$response = [
			'id'                  => $comment[0]->id,
			'comment_battle_id'   => $comment[0]->comment_battle_id,
			'comment_argument_id' => $comment[0]->comment_argument_id,
			'comment_author'      => $comment[0]->comment_author,
			'comment_date'        => $comment[0]->comment_date,
			'comment_text'        => $comment[0]->comment_text,
			'comment_moderate'    => $comment[0]->comment_moderate,
			'comment_rating'      => $comment[0]->comment_rating,
			'comment_parent'      => $comment[0]->comment_parent
		];
	} else {
		$response = [];
	}

	return $response;
}
