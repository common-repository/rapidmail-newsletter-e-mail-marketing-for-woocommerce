<?php

namespace Rapidmail\Connector\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rapidmail_Connector
 * @subpackage Rapidmail_Connector/admin
 * @author     rapidmail GmbH <pwuest@rapidmail.de>
 */
class NewsletterSettingsPage
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    public static function get_default_label() {
        return __('Subscribe to our newsletter', 'rapidmail-connector');
    }

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function register_settings()
    {
        register_setting(
            'rapidmail_connector_settings_newsletter',
            'rapidmail_connector_newsletter',
            [$this, 'settings_sanitize']
        );
    }

    public function render()
    {
        require_once 'partials/' . $this->plugin_name . '-admin-newsletter-settings-display.php';
    }

    public function settings_sanitize($input)
    {
        if ( isset($input['location']) ) {
            $location = [];
            foreach ( $input['location'] as $key => $value) {
                $location[] = $key;
            }
            $input['location'] = $location;
        }

        return $input;
    }
}