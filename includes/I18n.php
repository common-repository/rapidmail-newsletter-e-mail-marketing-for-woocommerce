<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.rapidmail.de/
 * @since      1.0.0
 *
 * @package    Rapidmail_Connector
 * @subpackage Rapidmail_Connector/includes
 */

namespace Rapidmail\Connector;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Rapidmail_Connector
 * @subpackage Rapidmail_Connector/includes
 * @author     rapidmail GmbH <ebess@rapidmail.de>
 */
class I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'rapidmail-connector',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

}
