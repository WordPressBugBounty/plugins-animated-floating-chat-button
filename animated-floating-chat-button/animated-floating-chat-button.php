<?php
/**
 * Plugin Name: Animated Floating Chat Button
 * Plugin URI: https://wordpress.org/plugins/animated-floating-chat-button
 * Description: Adds an animated floating chat button to the WordPress site, enhancing user engagement and providing direct communication via a chat platform.
 * Version: 1.0.2
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
define('AFCB_PLUGIN_VERSION', '1.0.2');
define('AFCB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AFCB_PLUGIN_URL', plugin_dir_url(__FILE__));

define('AFCB_DEFAULT_PHONE', '+8801770268035');
define('AFCB_DEFAULT_MESSAGE', 'Hi! Adnan Habib');

define('AFCB_OPTION_VISIBILITY', 'afcb_chat_button_visibility');
define('AFCB_OPTION_PHONE', 'afcb_chat_button_phone_number');
define('AFCB_OPTION_MESSAGE', 'afcb_chat_button_message');
define('AFCB_OPTION_HORIZONTAL_POSITION', 'afcb_chat_button_horizontal_position');
define('AFCB_OPTION_VERTICAL_POSITION', 'afcb_chat_button_vertical_position');
define('AFCB_OPTION_HORIZONTAL_OFFSET', 'afcb_chat_button_horizontal_offset');
define('AFCB_OPTION_VERTICAL_OFFSET', 'afcb_chat_button_vertical_offset');

// Sanitize Settings Values
function afcb_sanitize_on_off($value) {
    return ($value === 'on') ? 'on' : 'off';
}

function afcb_sanitize_horizontal_position($value) {
    return in_array($value, array('left', 'right'), true) ? $value : 'right';
}

function afcb_sanitize_vertical_position($value) {
    return in_array($value, array('top', 'bottom'), true) ? $value : 'bottom';
}

function afcb_sanitize_phone_number($value) {
    return preg_replace('/[^0-9+]/', '', sanitize_text_field($value));
}

// Enqueue Frontend Scripts and Styles
function afcb_enqueue_scripts() {
    if (get_option(AFCB_OPTION_VISIBILITY, 'off') === 'on') {
        wp_enqueue_style('afcb-css', AFCB_PLUGIN_URL . 'css/afcb.css', array(), AFCB_PLUGIN_VERSION, 'all');
    }
}
add_action('wp_enqueue_scripts', 'afcb_enqueue_scripts');

// Enqueue Admin Scripts and Styles
function afcb_admin_assets($hook) {
    if ('toplevel_page_chat-button-settings' !== $hook) {
        return;
    }

    wp_enqueue_style('afcb-admin-css', AFCB_PLUGIN_URL . 'css/afcb.css', array(), AFCB_PLUGIN_VERSION, 'all');

    wp_enqueue_script('afcb-admin-preview-js', AFCB_PLUGIN_URL . 'js/afcb-admin-preview.js', array(), AFCB_PLUGIN_VERSION, true);
}
add_action('admin_enqueue_scripts', 'afcb_admin_assets');

// Render Frontend HTML
function afcb_add_html() {
    $visibility = get_option(AFCB_OPTION_VISIBILITY, 'off');
    if ($visibility !== 'on') {
        return;
    }

    $phone_number = afcb_sanitize_phone_number(get_option(AFCB_OPTION_PHONE, AFCB_DEFAULT_PHONE));
    $message      = get_option(AFCB_OPTION_MESSAGE, AFCB_DEFAULT_MESSAGE);

    $horizontal_position = afcb_sanitize_horizontal_position(get_option(AFCB_OPTION_HORIZONTAL_POSITION, 'right'));
    $vertical_position   = afcb_sanitize_vertical_position(get_option(AFCB_OPTION_VERTICAL_POSITION, 'bottom'));
    $horizontal_offset   = absint(get_option(AFCB_OPTION_HORIZONTAL_OFFSET, 30));
    $vertical_offset     = absint(get_option(AFCB_OPTION_VERTICAL_OFFSET, 30));

    $chat_url = add_query_arg(
        array(
            'phone' => $phone_number,
            'text'  => $message,
        ),
        'https://api.whatsapp.com/send'
    );

    $position_style = sprintf(
        '%s:%dpx;%s:%dpx;',
        $horizontal_position,
        $horizontal_offset,
        $vertical_position,
        $vertical_offset
    );
    ?>
    <section id="pulse-chat-button-wrapper" class="afcb-position-wrapper" style="<?php echo esc_attr($position_style); ?>" aria-label="<?php echo esc_attr__('Contact us via Chat', 'animated-floating-chat-button'); ?>">
        <div class="pulse"></div>
        <div class="pulse"></div>
        <div class="pulse"></div>

        <a style="color:#fff;" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url($chat_url); ?>" class="chat-button pulse" aria-label="<?php echo esc_attr__('Open Chat', 'animated-floating-chat-button'); ?>">
            <span class="chat-icon">
                <img src="<?php echo esc_url(AFCB_PLUGIN_URL . 'assets/whatsapp.png'); ?>" alt="<?php echo esc_attr__('Chat Icon', 'animated-floating-chat-button'); ?>" class="chat-icon">
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

// Plugin Activation
function afcb_activate_plugin() {
    add_option('afcb_do_activation_redirect', true);
    add_option(AFCB_OPTION_VISIBILITY, 'off');
    add_option(AFCB_OPTION_HORIZONTAL_POSITION, 'right');
    add_option(AFCB_OPTION_VERTICAL_POSITION, 'bottom');
    add_option(AFCB_OPTION_HORIZONTAL_OFFSET, 30);
    add_option(AFCB_OPTION_VERTICAL_OFFSET, 30);
}
register_activation_hook(__FILE__, 'afcb_activate_plugin');

// Redirect After Activation
function afcb_redirect() {
    if (get_option('afcb_do_activation_redirect', false)) {
        delete_option('afcb_do_activation_redirect');

        if (!isset($_GET['activate-multi']) && current_user_can('manage_options')) {
            wp_safe_redirect(admin_url('admin.php?page=chat-button-settings'));
            exit;
        }
    }
}
add_action('admin_init', 'afcb_redirect');

// Render Settings Page
function afcb_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
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
                    <img src="<?php echo esc_url(AFCB_PLUGIN_URL . 'assets/developer.png'); ?>" alt="<?php echo esc_attr__('Developer Photo', 'animated-floating-chat-button'); ?>" class="developer-photo">
                    <p><strong>Freelancer Habib</strong></p>
                    <p><strong>Email:</strong> <a href="mailto:hirehabibur@gmail.com">hirehabibur@gmail.com</a></p>
                    <p>
                        <a href="<?php echo esc_url('http://www.freelancer.com/u/csehabiburr183'); ?>" target="_blank" rel="noopener noreferrer">
                            <button type="button" class="hire-me-btn">Hire Me</button>
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <div id="afcb-admin-floating-preview" class="afcb-position-wrapper" aria-label="<?php echo esc_attr__('Chat Button Preview', 'animated-floating-chat-button'); ?>">
            <div class="pulse"></div>
            <div class="pulse"></div>
            <div class="pulse"></div>

            <div class="chat-button pulse">
                <span class="chat-icon">
                    <img src="<?php echo esc_url(AFCB_PLUGIN_URL . 'assets/whatsapp.png'); ?>" alt="<?php echo esc_attr__('WhatsApp Preview', 'animated-floating-chat-button'); ?>" class="chat-icon">
                </span>
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
            'sanitize_callback' => 'afcb_sanitize_phone_number',
            'default'           => AFCB_DEFAULT_PHONE,
        )
    );

    register_setting(
        'afcb_settings',
        AFCB_OPTION_MESSAGE,
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => AFCB_DEFAULT_MESSAGE,
        )
    );

    register_setting(
        'afcb_settings',
        AFCB_OPTION_VISIBILITY,
        array(
            'type'              => 'string',
            'sanitize_callback' => 'afcb_sanitize_on_off',
            'default'           => 'off',
        )
    );

    register_setting(
        'afcb_settings',
        AFCB_OPTION_HORIZONTAL_POSITION,
        array(
            'type'              => 'string',
            'sanitize_callback' => 'afcb_sanitize_horizontal_position',
            'default'           => 'right',
        )
    );

    register_setting(
        'afcb_settings',
        AFCB_OPTION_VERTICAL_POSITION,
        array(
            'type'              => 'string',
            'sanitize_callback' => 'afcb_sanitize_vertical_position',
            'default'           => 'bottom',
        )
    );

    register_setting(
        'afcb_settings',
        AFCB_OPTION_HORIZONTAL_OFFSET,
        array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 30,
        )
    );

    register_setting(
        'afcb_settings',
        AFCB_OPTION_VERTICAL_OFFSET,
        array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 30,
        )
    );

    add_settings_section('afcb_settings_section', 'Settings', null, 'chat-button-settings');

    add_settings_field(AFCB_OPTION_PHONE, 'Chat Number', 'afcb_phone_number_callback', 'chat-button-settings', 'afcb_settings_section');
    add_settings_field(AFCB_OPTION_MESSAGE, 'Default Message', 'afcb_message_callback', 'chat-button-settings', 'afcb_settings_section');
    add_settings_field(AFCB_OPTION_VISIBILITY, 'Visibility on Frontend', 'afcb_visibility_callback', 'chat-button-settings', 'afcb_settings_section');
    add_settings_field(AFCB_OPTION_HORIZONTAL_POSITION, 'Horizontal Position', 'afcb_horizontal_position_callback', 'chat-button-settings', 'afcb_settings_section');
    add_settings_field(AFCB_OPTION_VERTICAL_POSITION, 'Vertical Position', 'afcb_vertical_position_callback', 'chat-button-settings', 'afcb_settings_section');
    add_settings_field(AFCB_OPTION_HORIZONTAL_OFFSET, 'Left / Right Space', 'afcb_horizontal_offset_callback', 'chat-button-settings', 'afcb_settings_section');
    add_settings_field(AFCB_OPTION_VERTICAL_OFFSET, 'Top / Bottom Space', 'afcb_vertical_offset_callback', 'chat-button-settings', 'afcb_settings_section');
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

// Position Settings Callbacks
function afcb_horizontal_position_callback() {
    $value = get_option(AFCB_OPTION_HORIZONTAL_POSITION, 'right');

    echo "<select name='" . esc_attr(AFCB_OPTION_HORIZONTAL_POSITION) . "'>";
    echo "<option value='right' " . selected($value, 'right', false) . ">Right</option>";
    echo "<option value='left' " . selected($value, 'left', false) . ">Left</option>";
    echo "</select>";
}

function afcb_vertical_position_callback() {
    $value = get_option(AFCB_OPTION_VERTICAL_POSITION, 'bottom');

    echo "<select name='" . esc_attr(AFCB_OPTION_VERTICAL_POSITION) . "'>";
    echo "<option value='bottom' " . selected($value, 'bottom', false) . ">Bottom</option>";
    echo "<option value='top' " . selected($value, 'top', false) . ">Top</option>";
    echo "</select>";
}

function afcb_horizontal_offset_callback() {
    $value = absint(get_option(AFCB_OPTION_HORIZONTAL_OFFSET, 30));
    echo "<input type='number' min='0' name='" . esc_attr(AFCB_OPTION_HORIZONTAL_OFFSET) . "' value='" . esc_attr($value) . "' /> px";
}

function afcb_vertical_offset_callback() {
    $value = absint(get_option(AFCB_OPTION_VERTICAL_OFFSET, 30));
    echo "<input type='number' min='0' name='" . esc_attr(AFCB_OPTION_VERTICAL_OFFSET) . "' value='" . esc_attr($value) . "' /> px";
}

// Save Notification
function afcb_add_settings_message() {
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
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
    $hire_me_link = '<a href="' . esc_url('http://www.freelancer.com/u/csehabiburr183') . '" target="_blank" rel="noopener noreferrer">Hire Me</a>';

    array_unshift($links, $settings_link, $hire_me_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'afcb_add_action_links');