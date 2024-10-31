<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.rapidmail.de/
 * @since      1.0.0
 *
 * @package    Rapidmail_Connector
 * @subpackage Rapidmail_Connector/includes
 */

namespace Rapidmail\Connector;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Rapidmail_Connector
 * @subpackage Rapidmail_Connector/includes
 * @author     rapidmail GmbH <ebess@rapidmail.de>
 */
class Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        global $wpdb;

        $options = get_option('rapidmail_connector');
        $newsletterOptions = get_option('rapidmail_connector_newsletter');

        if (!is_array($options)) {
            update_option(
                'rapidmail_connector',
                [
                    'connect_url' => 'https://my.rapidmail.de/datalink/store/credentials',
                    'overview_url' => 'https://my.rapidmail.de/datalink/wizard.html#/datalink/list',
                ]
            );
        }

        if (!is_array($newsletterOptions)) {
            update_option(
                'rapidmail_connector_newsletter',
                [
                    'enabled' => true,
                    'location' => ['register', 'submit'],
                    'default' => 0,
                    'label' => __('Subscribe to our newsletter', 'rapidmail-connector'),
                ]
            );
        }

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rapidmail_changes (
     id bigint unsigned auto_increment primary key,
     model VARCHAR(32) NOT NULL,
     entity_id bigint unsigned NOT NULL,
     type VARCHAR(32) NOT NULL,
     created_at DATETIME(3) NOT NULL
) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

}
