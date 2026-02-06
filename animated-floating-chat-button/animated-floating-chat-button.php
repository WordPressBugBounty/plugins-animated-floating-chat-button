<?php
/**
 * Plugin Name: Animated Floating Chat Button
 * Plugin URI: https://wordpress.org/plugins/animated-floating-chat-button
 * Description: Adds an animated floating chat button to the WordPress site, enhancing user engagement and providing direct communication via a chat platform.
 * Version: 1.0.1
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Author: Freelancer Habib
 * Author URI: http://freelancer.com/u/csehabiburr183
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: animated-floating-chat-button
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define Constants
define('AFCB_PLUGIN_VERSION', '1.0.1');
define('AFCB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AFCB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AFCB_DEFAULT_PHONE', '+8801770268035');
define('AFCB_DEFAULT_MESSAGE', 'Hi! Adnan Habib');
define('AFCB_OPTION_VISIBILITY', 'afcb_chat_button_visibility');
define('AFCB_OPTION_PHONE', 'afcb_chat_button_phone_number');
define('AFCB_OPTION_MESSAGE', 'afcb_chat_button_message');

// Enqueue Scripts and Styles
function afcb_enqueue_scripts() {
    if (get_option(AFCB_OPTION_VISIBILITY) === 'on') {
        wp_enqueue_style('afcb-css', esc_url(AFCB_PLUGIN_URL . 'css/afcb.css'), array(), AFCB_PLUGIN_VERSION, 'all');
    }
}
add_action('wp_enqueue_scripts', 'afcb_enqueue_scripts');

function afcb_admin_styles($hook) {
    if ('toplevel_page_chat-button-settings' === $hook) {
        wp_enqueue_style('afcb-admin-css', esc_url(AFCB_PLUGIN_URL . 'css/afcb.css'), array(), AFCB_PLUGIN_VERSION, 'all');
    }
}
add_action('admin_enqueue_scripts', 'afcb_admin_styles');

// Render Frontend HTML
function afcb_add_html() {
    $visibility = get_option(AFCB_OPTION_VISIBILITY, 'off');
    if ($visibility !== 'on') return;

    $phone_number = get_option(AFCB_OPTION_PHONE, AFCB_DEFAULT_PHONE);
    $message = urlencode(get_option(AFCB_OPTION_MESSAGE, AFCB_DEFAULT_MESSAGE));
    $chat_url = "https://api.whatsapp.com/send?phone=$phone_number&text=$message";
    ?>
    <section id="pulse-chat-button-wrapper" aria-label="<?php echo esc_attr('Contact us via Chat'); ?>">
        <div class="pulse"></div>
        <div class="pulse"></div>
        <div class="pulse"></div>
        <a style="color: #fff" target="_blank" href="<?php echo esc_url($chat_url); ?>" class="chat-button pulse" aria-label="Open Chat">
            <span class="chat-icon">
                <img src="<?php echo esc_url(AFCB_PLUGIN_URL . 'assets/whatsapp.png'); ?>" alt="<?php echo esc_attr('Chat Icon'); ?>" class="chat-icon">
            </span>
        </a>
    </section>
    <?php
}
add_action('wp_footer', 'afcb_add_html');

// Admin Menu and Settings
function afcb_admin_menu() {
    add_menu_page('Chat Button Settings', 'Chat Button', 'manage_options', 'chat-button-settings', 'afcb_settings_page', 'dashicons-format-chat', 100);
}
add_action('admin_menu', 'afcb_admin_menu');

function afcb_activate_plugin() {
    add_option('afcb_do_activation_redirect', true);
    add_option(AFCB_OPTION_VISIBILITY, 'off'); // Set visibility to off by default
}
register_activation_hook(__FILE__, 'afcb_activate_plugin');

function afcb_redirect() {
    if (get_option('afcb_do_activation_redirect', false)) {
        delete_option('afcb_do_activation_redirect');
        if (!isset($_GET['activate-multi'])) {
            wp_redirect(admin_url('admin.php?page=chat-button-settings'));
            exit;
        }
    }
}
add_action('admin_init', 'afcb_redirect');

// Render Settings Page
function afcb_settings_page() {
    ?>
    <div class="wrap">
        <div class="afcb-container">
            <div class="afcb-settings-form">
                <h1>Animated Floating Chat Button:</h1>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('afcb_settings');
                    do_settings_sections('chat-button-settings');
                    submit_button();
                    ?>
                </form>
            </div>
            <div id="afcb-about-developer">
                <h2>About Developer</h2>
                <div class="developer-info">
                    <img src="<?php echo esc_url(AFCB_PLUGIN_URL . 'assets/developer.jpg'); ?>" alt="<?php echo esc_attr('Developer Photo'); ?>" class="developer-photo">
                    <p><strong>Freelancer Habib</strong></p>
                    <p><strong>Email:</strong> <a href="mailto:hirehabibur@gmail.com">hirehabibur@gmail.com</a></p>
                    <p><a href="http://www.freelancer.com/u/csehabiburr183" target="_blank">
                        <button class="hire-me-btn">Hire Me</button>
                    </a></p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Register Settings
function afcb_register_settings() {
    register_setting(
        'afcb_settings',
        AFCB_OPTION_PHONE,
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    register_setting(
        'afcb_settings',
        AFCB_OPTION_MESSAGE,
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
        )
    );
    register_setting(
        'afcb_settings',
        AFCB_OPTION_VISIBILITY,
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );

    add_settings_section('afcb_settings_section', 'Settings', null, 'chat-button-settings');

    add_settings_field(AFCB_OPTION_PHONE, 'Chat Number', 'afcb_phone_number_callback', 'chat-button-settings', 'afcb_settings_section');
    add_settings_field(AFCB_OPTION_MESSAGE, 'Default Message', 'afcb_message_callback', 'chat-button-settings', 'afcb_settings_section');
    add_settings_field(AFCB_OPTION_VISIBILITY, 'Visibility on Frontend', 'afcb_visibility_callback', 'chat-button-settings', 'afcb_settings_section');
}
add_action('admin_init', 'afcb_register_settings');

// Settings Callbacks
function afcb_phone_number_callback() {
    $phone_number = get_option(AFCB_OPTION_PHONE, AFCB_DEFAULT_PHONE);
    echo "<input type='text' name='" . esc_attr(AFCB_OPTION_PHONE) . "' value='" . esc_attr($phone_number) . "' placeholder='" . esc_attr(AFCB_DEFAULT_PHONE) . "' />";
}

function afcb_message_callback() {
    $message = get_option(AFCB_OPTION_MESSAGE, AFCB_DEFAULT_MESSAGE);
    echo "<input type='text' name='" . esc_attr(AFCB_OPTION_MESSAGE) . "' value='" . esc_attr($message) . "' placeholder='Default message' />";
}

function afcb_visibility_callback() {
    $visibility = get_option(AFCB_OPTION_VISIBILITY, 'off');
    $checked = checked('on', $visibility, false);
    echo "<label class='toggle-switch'><input type='checkbox' name='" . esc_attr(AFCB_OPTION_VISIBILITY) . "' value='on' " . esc_attr($checked) . "><span class='slider'></span></label>";
}

// Save Notification
function afcb_add_settings_message() {
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
        add_settings_error('afcb_settings', 'afcb_message_saved', 'Settings Saved Successfully!', 'updated');
    }
}
add_action('admin_init', 'afcb_add_settings_message');

// Display Admin Notices
function afcb_admin_notices() {
    settings_errors('afcb_settings');
}
add_action('admin_notices', 'afcb_admin_notices');

// Plugin Action Links
function afcb_add_action_links($links) {
    $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=chat-button-settings')) . '">Settings</a>';
    $hire_me_link = '<a href="http://www.freelancer.com/u/csehabiburr183" target="_blank">Hire Me</a>';
    array_unshift($links, $settings_link, $hire_me_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'afcb_add_action_links');

?>