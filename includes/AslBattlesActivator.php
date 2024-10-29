<?php

namespace AslBattles\Classes;
/**
 * Fired during plugin activation
 *
 * @link       https://vk.com/aslundin
 * @since      1.0.0
 *
 * @package    Asl_Battle
 * @subpackage Asl_Battle/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Asl_Battle
 * @subpackage Asl_Battle/includes
 * @author     Alex Lundin <aslundin@yandex.ru>
 */
class AslBattlesActivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::create_tables_database();
	}

	public static function create_tables_database() {
		global $wpdb;

		$charset_collate    = $wpdb->get_charset_collate();
		$table_battle_name  = $wpdb->prefix . battle_table_name;
		$table_comment_name = $wpdb->prefix . battle_comment_table_name;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_battle_name'" ) !== $table_battle_name ) {
			$sql
				= "CREATE TABLE $table_battle_name (
				id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    			id_item int(11) NOT NULL,
    			argument varchar(255),
    			rating int(11) DEFAULT 0,
    			title varchar(255),
    			text longtext,
    			moderate varchar(20) DEFAULT '0',
    			username varchar(255),
    			email varchar(255),
    			remember varchar(20) DEFAULT '0',
    			created_at 	datetime NULL,
				updated_at 	datetime NULL
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_comment_name'" ) !== $table_comment_name ) {
			$sql
				= "CREATE TABLE $table_comment_name (
				id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				comment_battle_id int(11) NOT NULL,
				comment_argument_id int(11) NOT NULL,
				comment_author tinytext NOT NULL,
				comment_author_email varchar(100),
				comment_author_ip varchar(100),
				comment_date datetime,
				comment_text longtext,
				comment_rating int DEFAULT 0,
				comment_moderate varchar(20) DEFAULT '0',
				comment_parent bigint unsigned DEFAULT 0,
				user_id bigint DEFAULT 0
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}

}
