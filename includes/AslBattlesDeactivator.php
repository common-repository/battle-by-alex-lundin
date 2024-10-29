<?php
namespace AslBattles\Classes;
/**
 * Fired during plugin deactivation
 *
 * @link       https://vk.com/aslundin
 * @since      1.0.0
 *
 * @package    Asl_Battle
 * @subpackage Asl_Battle/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Asl_Battle
 * @subpackage Asl_Battle/includes
 * @author     Alex Lundin <aslundin@yandex.ru>
 */
class AslBattlesDeactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		global $wpdb;
		$table_battle_name  = $wpdb->prefix . battle_table_name;
		$table_comment_name = $wpdb->prefix . battle_comment_table_name;

		$sql = "DROP TABLE IF EXISTS $table_battle_name";
		$sql2 = "DROP TABLE IF EXISTS $table_comment_name";
//		TODO Использовать для дебага

//		$wpdb->query($sql);
//		$wpdb->query($sql2);
	}

}
