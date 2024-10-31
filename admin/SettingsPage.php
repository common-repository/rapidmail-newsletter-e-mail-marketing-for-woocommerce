<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.rapidmail.de/
 * @since      1.0.0
 *
 * @package    Rapidmail_Connector
 * @subpackage Rapidmail_Connector/admin
 */

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
class SettingsPage
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

    /**
     * @var array
     */
    private $options = [];

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
            'rapidmail_connector_settings',
            'rapidmail_connector',
            [$this, 'settings_sanitize']
        );

        add_settings_section(
            'general',
            __('Settings'),
            function () {
            },
            'rapidmail_connector_settings_general'
        );

        add_settings_field(
            'rapidmail_connector_connect_url',
            __('URL used for connecting to Rapidmail', 'rapidmail-connector'),
            [$this, 'settings_connect_url'],
            'rapidmail_connector_settings_general',
            'general'
        );
        add_settings_field(
            'rapidmail_connector_overview_url',
            __('URL to overview of integrations', 'rapidmail-connector'),
            [$this, 'settings_overview_url'],
            'rapidmail_connector_settings_general',
            'general'
        );
    }

    public function render()
    {
        $this->options = get_option('rapidmail_connector');

        if (empty($this->options['connect_url']) || empty($this->options['overview_url'])) {

            ?>
            <div class="error">
                <p>
                    <?php echo esc_html(__('The settings are not complete.', 'rapidmail-connector')); ?>
                </p>
            </div>
            <?php

        }

        require_once 'partials/' . $this->plugin_name . '-admin-settings-display.php';
    }

    public function settings_connect_url()
    {
        printf(
            '<input class="regular-text" type="text" name="rapidmail_connector[connect_url]" id="rapidmail_connector_connect_url" value="%s">',
            isset($this->options['connect_url']) ? esc_attr($this->options['connect_url']) : ''
        );
    }

    public function settings_overview_url()
    {
        printf(
            '<input class="regular-text" type="text" name="rapidmail_connector[overview_url]" id="rapidmail_connector_overview_url" value="%s">',
            isset($this->options['overview_url']) ? esc_attr($this->options['overview_url']) : ''
        );
    }

    public function settings_sanitize($input)
    {
        return $input;
    }
}