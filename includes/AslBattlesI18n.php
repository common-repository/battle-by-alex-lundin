<?php
namespace AslBattles\Classes;
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://vk.com/aslundin
 * @since      1.0.0
 *
 * @package    Asl_Battle
 * @subpackage Asl_Battle/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Asl_Battle
 * @subpackage Asl_Battle/includes
 * @author     Alex Lundin <aslundin@yandex.ru>
 */
class AslBattlesI18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'asl-battle',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
