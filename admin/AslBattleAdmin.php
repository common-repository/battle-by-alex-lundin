<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://vk.com/aslundin
 * @since      1.0.0
 *
 * @package    Asl_Battle
 * @subpackage Asl_Battle/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Asl_Battle
 * @subpackage Asl_Battle/admin
 * @author     Alex Lundin <aslundin@yandex.ru>
 */
class AslBattleAdmin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	private $cpt_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->cpt_name    = 'asl-battle';

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Asl_Battle_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Asl_Battle_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

//		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/asl-battle-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'frontend/build/index.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name . '-app', plugin_dir_url( __FILE__ ) . 'frontend/build/index.js', array(), $this->version, true );
		wp_set_script_translations( $this->plugin_name . '-app', 'asl-polling', plugin_dir_path( __FILE__ ) . '/languages' );

		if ( current_user_can( 'manage_options' ) ) {
			$isAdmin = 'yes';
		} else {
			$isAdmin = 'no';
		}

		wp_localize_script( $this->plugin_name . '-app', 'asl_battles_admin', array(
			'asl_battles_rest_uri' => get_rest_url(),
			'nonce'                => wp_create_nonce( 'wp_rest' )
		) );


	}

	public function register_battle_type() {
		register_post_type( $this->cpt_name, [
			'labels'              => [
				'name'               => __( 'Battles', 'asl-battle' ),
				'singular_name'      => __( 'Battle', 'asl-battle' ),
				'add_new'            => __( 'Add New Battle', 'asl-battle' ),
				'add_new_item'       => __( 'Add Battle', 'asl-battle' ),
				'edit_item'          => __( 'Edit Battle', 'asl-battle' ),
				'new_item'           => __( 'New Battle', 'asl-battle' ),
				'view_item'          => __( 'View Battle', 'asl-battle' ),
				'not_found'          => __( 'Not Found', 'asl-battle' ),
				'not_found_in_trash' => __( 'Not Found in Trash', 'asl-battle' ),
				'menu_name'          => __( 'Battle', 'asl-battle' ),
			],
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => true,
			'show_in_rest'        => false,
			'menu_position'       => 24,
			'menu_icon'           => null,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => [ 'title' ],
			'has_archive'         => false,
		] );

		register_post_meta( $this->cpt_name, 'rating', [
			'type' => 'string',

		] );

		register_post_meta( $this->cpt_name, 'count_view', [
			'type' => 'string'
		] );

		register_post_meta( $this->cpt_name, 'first_argument', [
			'type' => 'string'
		] );

		register_post_meta( $this->cpt_name, 'second_argument', [
			'type' => 'string'
		] );

		register_post_meta( $this->cpt_name, 'username', [
			'type' => 'string'
		] );
	}

	public function add_menu() {
		global $submenu;
		global $wpdb;
		$capability = battles_admin_role();
		$dbBattle   = $wpdb->prefix . battle_table_name;
		$dbComments = $wpdb->prefix . battle_comment_table_name;

		$resArg = $wpdb->get_results( "SELECT `moderate` FROM $dbBattle" );
		$resCom = $wpdb->get_results( "SELECT `comment_moderate` FROM $dbComments" );

		$countArg = 0;
		$countCom = 0;

		foreach ( $resCom as $key => $item ) {
			if ( $resCom[ $key ]->comment_moderate === "0" || $resCom[ $key ]->comment_moderate === "" ) {
				$countCom ++;
			}
		}

		foreach ( $resArg as $key => $item ) {
			if ( $resArg[ $key ]->moderate === "0" || $resArg[ $key ]->moderate === "" ) {
				$countArg ++;
			}
		}
		if ( $countArg != 0 ) {
			$itemArg = '<span class="update-plugins"><span class="plugin-count">' . $countArg . '</span></span>';
		} else {
			$itemArg = '';
		}

		if ( $countCom != 0 ) {
			$itemCom = '<span class="update-plugins"><span class="plugin-count">' . $countCom . '</span></span>';
		} else {
			$itemCom = '';
		}

		if ( ! $capability ) {
			return;
		}

		$menuName = __( 'Battles' . $itemArg . $itemCom, ' asl-battle' );

		add_menu_page(
			$menuName,
			$menuName,
			$capability,
			'asl_battles',
			array( $this, 'main_page' ),
			'dashicons-format-chat',
			26
		);

		$submenu['asl_battles']['all_battles'] = [
			__( 'Battles' . $itemArg, 'asl-battle' ),
			$capability,
			'admin.php?page=asl_battles#/',
		];

		$submenu['asl_battles']['comments'] = [
			__( 'Comments' . $itemCom, 'asl-battle' ),
			$capability,
			'admin.php?page=asl_battles#/comments',
		];

	}

	public function main_page() {
		include( plugin_dir_path( __FILE__ ) . 'partials/asl-battle-admin-display.php' );
	}

	public function register_battle_routes() {
		register_rest_route( 'asl-battle/v1', '/battles', [
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'rest_get_battles' ],
				'permission_callback' => '__return_true'
			],
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'rest_create_battle' ],
				'permission_callback' => '__return_true'
			]
		] );
		register_rest_route( 'asl-battle/v1', '/battles/(?P<id>\d+)', [
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'rest_get_battle' ],
				'permission_callback' => '__return_true'
			],
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'rest_delete_battle' ],
				'permission_callback' => '__return_true'
			],
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'rest_edit_battle' ],
				'permission_callback' => '__return_true'
			]
		] );

		register_rest_route( 'asl-battle/v1', '/battles/(?P<id>\d+)/arguments/', [
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'rest_battle_get_arguments' ],
				'permission_callback' => '__return_true'
			],
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'rest_battle_add_argument' ],
				'permission_callback' => '__return_true'
			],
		] );
		register_rest_route( 'asl-battle/v1', '/battles/(?P<post_id>\d+)/arguments/(?P<id>\d+)', [
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'rest_battle_get_argument' ],
				'permission_callback' => '__return_true'
			],
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'rest_battle_update_argument' ],
				'permission_callback' => '__return_true'
			],
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'rest_battle_delete_argument' ],
				'permission_callback' => '__return_true'
			],
		] );

		register_rest_route( 'asl-battle/v1', '/battles/(?P<id>\d+)/comments', [
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'rest_battle_get_comments' ],
				'permission_callback' => '__return_true'
			],
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'rest_battle_add_comment' ],
				'permission_callback' => '__return_true'
			],
		] );
		register_rest_route( 'asl-battle/v1', '/battles/(?P<post_id>\d+)/comments/(?P<id>\d+)', [
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'rest_battle_get_comment' ],
				'permission_callback' => '__return_true'
			],
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'rest_battle_update_comment' ],
				'permission_callback' => '__return_true'
			],
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'rest_battle_delete_comment' ],
				'permission_callback' => '__return_true'
			],
		] );
	}

	public function rest_get_battles(): void {
		global $wpdb;
		$dbBattle   = $wpdb->prefix . battle_table_name;
		$dbComments = $wpdb->prefix . battle_comment_table_name;
		$head       = $wpdb->get_results( "SELECT ID, post_title, post_content, post_modified FROM $wpdb->posts WHERE `post_type` = '$this->cpt_name'" );

		if ( ! empty( $head ) ) {
			foreach ( $head as $key => $item ) {
				$response[] = [
					'id'                   => $item->ID,
					'title'                => $item->post_title,
					'content'              => $item->post_content,
					'first_argument_head'  => get_post_meta( $item->ID, 'first_argument', true ),
					'second_argument_head' => get_post_meta( $item->ID, 'second_argument', true ),
					'rating'               => get_post_meta( $item->ID, 'rating', true ),
					'count_views'          => get_post_meta( $item->ID, 'count_view', true ),
					'username'             => get_post_meta( $item->ID, 'username', true ),
					'arguments'            => [],
					'comments'             => []
				];
				$arguments  = $wpdb->get_results( "SELECT * FROM $dbBattle WHERE `id_item` = $item->ID" );
				foreach ( $arguments as $argument ) {
					$response[ $key ]['arguments'][] = [
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
				$comments = $wpdb->get_results( "SELECT * FROM $dbComments WHERE `comment_battle_id` = $item->ID" );
				foreach ( $comments as $comment ) {
					$response[ $key ]['comments'][] = [
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
			}
			wp_send_json( $response, 200 );
		}
	}

	public function rest_create_battle( WP_REST_Request $request ) {
		global $wpdb;
		$data      = $request->get_params();
		$post_data = [
			'post_title'   => sanitize_text_field( $data['title'] ),
			'post_content' => sanitize_text_field( $data['content'] ),
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_type'    => $this->cpt_name
		];

		$post_id = wp_insert_post( $post_data );
		add_post_meta( $post_id, 'first_argument', sanitize_text_field( $data['first_argument_head'] ) );
		add_post_meta( $post_id, 'second_argument', sanitize_text_field( $data['second_argument_head'] ) );
		add_post_meta( $post_id, 'username', sanitize_text_field( $data['username'] ) );
		add_post_meta( $post_id, 'rating', sanitize_text_field( $data['rating'] ) );
		add_post_meta( $post_id, 'count_view', sanitize_text_field( $data['count_views'] ) );

		$head_battle = $wpdb->get_results( "SELECT post_title, post_content FROM $wpdb->posts WHERE ID = $post_id" );
		$res         = [
			'id'                   => $post_id,
			'title'                => $head_battle[0]->post_title,
			'content'              => $head_battle[0]->post_content,
			'first_argument_head'  => get_post_meta( $post_id, 'first_argument', 'true' ),
			'second_argument_head' => get_post_meta( $post_id, 'second_argument', 'true' ),
			'rating'               => get_post_meta( $post_id, 'rating', 'true' ),
			'count_views'          => get_post_meta( $post_id, 'count_view', 'true' ),
			'username'             => get_post_meta( $post_id, 'username', 'true' ),
		];
		wp_send_json( $res, 200 );
	}

	public function rest_get_battle( WP_REST_Request $request ): void {
		$id = $request->get_param( 'id' );
		wp_send_json( get_battle( $id ), 200 );
	}

	public function rest_delete_battle( WP_REST_Request $request ) {
		global $wpdb;
		$dbBattle   = $wpdb->prefix . battle_table_name;
		$dbComments = $wpdb->prefix . battle_comment_table_name;
		$id         = $request->get_param( 'id' );

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $dbBattle WHERE id_item = '%d'",
				(int) $id
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $dbComments WHERE comment_battle_id = '%d'",
				(int) $id
			)
		);

		$del = wp_delete_post( $id );
		$wpdb->delete( $wpdb->postmeta, [ 'post_id' => $id ] );
		clean_post_cache( $id );
		wp_send_json( $del, 200 );
	}

	public function rest_edit_battle( WP_REST_Request $request ) {
		global $wpdb;
		$id  = $request->get_param( 'id' );
		$old = get_battle( $id );
		$new = $request->get_params();

		$result = array_diff_assoc( $new, $old );
		foreach ( $result as $key => $val ) {
			switch ( $key ) {
				case 'title':
					$wpdb->update( $wpdb->posts, [ 'post_title' => $val ], [ 'ID' => $id ] );
					break;
				case 'content':
					$wpdb->update( $wpdb->posts, [ 'post_content' => $val ], [ 'ID' => $id ] );
					break;
				case 'first_argument_head':
					update_post_meta( $id, 'first_argument', $val );
					break;
				case 'second_argument_head':
					update_post_meta( $id, 'second_argument', $val );
					break;
				case 'rating':
					update_post_meta( $id, 'rating', $val );
					break;
				case 'username':
					update_post_meta( $id, 'username', $val );
					break;
				case 'count_views':
					update_post_meta( $id, 'count_view', $val );
					break;
			}
		}

		wp_send_json( get_battle( $id ), 200 );
	}

	public function rest_battle_get_arguments( WP_REST_Request $request ) {
		global $wpdb;
		$dbBattle = $wpdb->prefix . battle_table_name;

		$id  = $request->get_param( 'id' );
		$res = $wpdb->get_results( "SELECT * FROM $dbBattle WHERE `id_item` = $id" );

		wp_send_json( $res );
	}

	public function rest_battle_add_argument( WP_REST_Request $request ) {
		global $wpdb;
		$dbBattle = $wpdb->prefix . battle_table_name;
		$date     = new DateTime();
		$data     = $request->get_params();
		$res      = $wpdb->prepare( $wpdb->insert( $dbBattle, [
			'id_item'    => (int) $data['id_item'],
			'argument'   => $data['argument'],
			'rating'     => (int) $data['rating'],
			'title'      => $data['title'],
			'text'       => $data['text'],
			'moderate'   => (int) $data['moderate'],
			'username'   => $data['username'],
			'email'      => $data['email'],
			'created_at' => $date->format( 'Y-m-d H:i:s' ),
		] ) );
		wp_send_json( (bool) $res, 200 );
	}

	public function rest_battle_get_argument( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		wp_send_json( get_argument( $id ) );
	}

	public function rest_battle_update_argument( WP_REST_Request $request ) {
		global $wpdb;
		$dbBattle = $wpdb->prefix . battle_table_name;
		$id       = $request->get_param( 'id' );
		$old      = get_argument( $id );
		$new      = $request->get_params();
		$result   = array_diff_assoc( $new, $old );
		foreach ( $result as $key => $val ) {
			switch ( $key ) {
				case 'email':
					$wpdb->update( $dbBattle, [ 'email' => $val ], [ 'id' => $id ] );
					break;
				case 'moderate':
					$wpdb->update( $dbBattle, [ 'moderate' => $val ], [ 'id' => $id ] );
					break;
				case 'argument':
					$wpdb->update( $dbBattle, [ 'argument' => $val ], [ 'id' => $id ] );
					break;
				case 'rating':
					$wpdb->update( $dbBattle, [ 'rating' => $val ], [ 'id' => $id ] );
					break;
				case 'text':
					$wpdb->update( $dbBattle, [ 'text' => $val ], [ 'id' => $id ] );
					break;
				case 'title':
					$wpdb->update( $dbBattle, [ 'title' => $val ], [ 'id' => $id ] );
					break;
				case 'username':
					$wpdb->update( $dbBattle, [ 'username' => $val ], [ 'id' => $id ] );
					break;
			}
		}
		wp_send_json( get_battle( $id ), 200 );
	}

	public function rest_battle_delete_argument( WP_REST_Request $request ) {
		global $wpdb;
		$dbBattle = $wpdb->prefix . battle_table_name;
		$dbComments = $wpdb->prefix . battle_comment_table_name;
		$id       = $request->get_param( 'id' );

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $dbBattle WHERE id = '%d'",
				(int) $id
			)
		);

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $dbComments WHERE comment_argument_id = '%d'",
				(int) $id
			)
		);
	}

	public function rest_battle_get_comments( WP_REST_Request $request ) {
		global $wpdb;
		$dbComments = $wpdb->prefix . battle_comment_table_name;

		$id  = $request->get_param( 'id' );
		$res = $wpdb->get_results( "SELECT * FROM $dbComments WHERE `comment_battle_id` = $id" );

		wp_send_json( $res );
	}

	public function rest_battle_add_comment( WP_REST_Request $request ) {
		global $wpdb;
		$dbComments = $wpdb->prefix . battle_comment_table_name;

		$date = new DateTime();
		$data = $request->get_params();
		$res  = $wpdb->prepare( $wpdb->insert( $dbComments, [
			'comment_battle_id'   => (int) $data['comment_battle_id'],
			'comment_argument_id' => $data['comment_argument_id'],
			'comment_author'      => $data['comment_author'],
			'comment_date'        => $date->format( 'Y-m-d H:i:s' ),
			'comment_text'        => $data['comment_text'],
			'comment_moderate'    => (int) $data['comment_moderate'],
			'comment_rating'      => $data['comment_rating'],
			'comment_parent'      => (int) $data['comment_parent'],
		] ) );
		wp_send_json( (bool) $res, 200 );
	}

	public function rest_battle_get_comment( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );
		wp_send_json( get_battle_comment( $id ) );
	}

	public function rest_battle_update_comment( WP_REST_Request $request ) {
		global $wpdb;
		$dbComments = $wpdb->prefix . battle_comment_table_name;
		$id         = $request->get_param( 'id' );
		$old        = get_battle_comment( $id );
		$new        = $request->get_params();
		$result     = array_diff_assoc( $new, $old );
		foreach ( $result as $key => $val ) {
			switch ( $key ) {
				case 'comment_battle_id':
					$wpdb->update( $dbComments, [ 'comment_battle_id' => $val ], [ 'id' => $id ] );
					break;
				case 'comment_argument_id':
					$wpdb->update( $dbComments, [ 'comment_argument_id' => $val ], [ 'id' => $id ] );
					break;
				case 'comment_author':
					$wpdb->update( $dbComments, [ 'comment_author' => $val ], [ 'id' => $id ] );
					break;
				case 'comment_date':
					$wpdb->update( $dbComments, [ 'comment_date' => $val ], [ 'id' => $id ] );
					break;
				case 'comment_text':
					$wpdb->update( $dbComments, [ 'comment_text' => $val ], [ 'id' => $id ] );
					break;
				case 'comment_moderate':
					$wpdb->update( $dbComments, [ 'comment_moderate' => $val ], [ 'id' => $id ] );
					break;
				case 'comment_rating':
					$wpdb->update( $dbComments, [ 'comment_rating' => $val ], [ 'id' => $id ] );
					break;
				case 'comment_parent':
					$wpdb->update( $dbComments, [ 'comment_parent' => $val ], [ 'id' => $id ] );
					break;
			}
		}
		wp_send_json( get_battle( $id ), 200 );
	}

	public function rest_battle_delete_comment( WP_REST_Request $request ) {
		global $wpdb;
		$dbComments = $wpdb->prefix . battle_comment_table_name;
		$id         = $request->get_param( 'id' );

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $dbComments WHERE id = '%d'",
				(int) $id
			)
		);
	}

	public function conditional_plugin_admin_notice() {
		if ( isset( $_GET['page'] ) && sanitize_text_field( $_GET['page'] ) === 'asl_battles' ) {
			remove_action( 'admin_notices', 'update_nag', 3 );
			remove_action( 'network_admin_notices', 'update_nag', 3 );
			echo '<style>.update-nag, .updated, .notice, .error, .is-dismissible { display: none; }</style>';
		}
	}
}
