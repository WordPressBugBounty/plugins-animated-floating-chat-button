<?php
/**
 * Plugin Name: Animated Floating Chat Button
 * Plugin URI: https://wordpress.org/plugins/animated-floating-chat-button
 * Description: Adds an animated floating chat button to the WordPress site, enhancing user engagement and providing direct communication via a chat platform.
 * Version: 1.0.3
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Author: Freelancer Habib
 * Author URI: https://www.freelancer.com/u/csehabiburr183
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: animated-floating-chat-button
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define Constants
define( 'AFCB_PLUGIN_VERSION', '1.0.3' );
define( 'AFCB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AFCB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

define( 'AFCB_DEFAULT_PHONE', '+880123456789' );
define( 'AFCB_DEFAULT_MESSAGE', 'Hi! Website owner, put your message here' );

define( 'AFCB_OPTION_VISIBILITY', 'afcb_chat_button_visibility' );
define( 'AFCB_OPTION_PHONE', 'afcb_chat_button_phone_number' );
define( 'AFCB_OPTION_MESSAGE', 'afcb_chat_button_message' );
define( 'AFCB_OPTION_HORIZONTAL_POSITION', 'afcb_chat_button_horizontal_position' );
define( 'AFCB_OPTION_VERTICAL_POSITION', 'afcb_chat_button_vertical_position' );
define( 'AFCB_OPTION_HORIZONTAL_OFFSET', 'afcb_chat_button_horizontal_offset' );
define( 'AFCB_OPTION_VERTICAL_OFFSET', 'afcb_chat_button_vertical_offset' );

/**
 * Sanitize the visibility setting.
 *
 * @param string $value Submitted value.
 * @return string
 */
function afcb_sanitize_on_off( $value ) {
    return ( 'on' === $value ) ? 'on' : 'off';
}

/**
 * Sanitize the horizontal position setting.
 *
 * @param string $value Submitted value.
 * @return string
 */
function afcb_sanitize_horizontal_position( $value ) {
    return in_array( $value, array( 'left', 'right' ), true ) ? $value : 'right';
}

/**
 * Sanitize the vertical position setting.
 *
 * @param string $value Submitted value.
 * @return string
 */
function afcb_sanitize_vertical_position( $value ) {
    return in_array( $value, array( 'top', 'bottom' ), true ) ? $value : 'bottom';
}

/**
 * Sanitize a WhatsApp phone number.
 *
 * @param string $value Submitted phone number.
 * @return string
 */
function afcb_sanitize_phone_number( $value ) {
    return preg_replace( '/[^0-9+]/', '', sanitize_text_field( $value ) );
}

/**
 * Enqueue frontend styles when the chat button is enabled.
 *
 * @return void
 */
function afcb_enqueue_scripts() {
    if ( 'on' !== get_option( AFCB_OPTION_VISIBILITY, 'off' ) ) {
        return;
    }

    wp_enqueue_style(
        'afcb-css',
        AFCB_PLUGIN_URL . 'css/afcb.css',
        array(),
        AFCB_PLUGIN_VERSION,
        'all'
    );
}
add_action( 'wp_enqueue_scripts', 'afcb_enqueue_scripts' );

/**
 * Enqueue assets on the plugin settings page.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 * @return void
 */
function afcb_admin_assets( $hook_suffix ) {
    if ( 'toplevel_page_chat-button-settings' !== $hook_suffix ) {
        return;
    }

    wp_enqueue_style(
        'afcb-admin-css',
        AFCB_PLUGIN_URL . 'css/afcb.css',
        array(),
        AFCB_PLUGIN_VERSION,
        'all'
    );

    wp_enqueue_script(
        'afcb-admin-preview-js',
        AFCB_PLUGIN_URL . 'js/afcb-admin-preview.js',
        array(),
        AFCB_PLUGIN_VERSION,
        true
    );

    wp_localize_script(
        'afcb-admin-preview-js',
        'afcbAdminPreview',
        array(
            'visibleText' => esc_html__( 'Button is visible', 'animated-floating-chat-button' ),
            'hiddenText'  => esc_html__( 'Button is hidden', 'animated-floating-chat-button' ),
        )
    );
}
add_action( 'admin_enqueue_scripts', 'afcb_admin_assets' );

/**
 * Render the floating chat button on the frontend.
 *
 * @return void
 */
function afcb_add_html() {
    if ( 'on' !== get_option( AFCB_OPTION_VISIBILITY, 'off' ) ) {
        return;
    }

    $phone_number        = afcb_sanitize_phone_number( get_option( AFCB_OPTION_PHONE, AFCB_DEFAULT_PHONE ) );
    $message             = sanitize_text_field( get_option( AFCB_OPTION_MESSAGE, AFCB_DEFAULT_MESSAGE ) );
    $horizontal_position = afcb_sanitize_horizontal_position( get_option( AFCB_OPTION_HORIZONTAL_POSITION, 'right' ) );
    $vertical_position   = afcb_sanitize_vertical_position( get_option( AFCB_OPTION_VERTICAL_POSITION, 'bottom' ) );
    $horizontal_offset   = absint( get_option( AFCB_OPTION_HORIZONTAL_OFFSET, 30 ) );
    $vertical_offset     = absint( get_option( AFCB_OPTION_VERTICAL_OFFSET, 30 ) );

    $chat_url = add_query_arg(
        array(
            'phone' => $phone_number,
            'text'  => $message,
        ),
        'https://api.whatsapp.com/send'
    );

    $position_style = sprintf(
        '%1$s:%2$dpx;%3$s:%4$dpx;',
        $horizontal_position,
        $horizontal_offset,
        $vertical_position,
        $vertical_offset
    );
    ?>
    <section
        id="pulse-chat-button-wrapper"
        class="afcb-position-wrapper"
        style="<?php echo esc_attr( $position_style ); ?>"
        aria-label="<?php echo esc_attr__( 'Contact us via WhatsApp', 'animated-floating-chat-button' ); ?>"
    >
        <span class="pulse" aria-hidden="true"></span>
        <span class="pulse" aria-hidden="true"></span>
        <span class="pulse" aria-hidden="true"></span>

        <a
            class="chat-button pulse"
            href="<?php echo esc_url( $chat_url ); ?>"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="<?php echo esc_attr__( 'Open WhatsApp chat', 'animated-floating-chat-button' ); ?>"
        >
            <span class="chat-icon" aria-hidden="true">
                <img
                    src="<?php echo esc_url( AFCB_PLUGIN_URL . 'assets/whatsapp.png' ); ?>"
                    alt=""
                >
            </span>
        </a>
    </section>
    <?php
}
add_action( 'wp_footer', 'afcb_add_html' );

/**
 * Register the plugin admin menu.
 *
 * @return void
 */
function afcb_admin_menu() {
    add_menu_page(
        esc_html__( 'Chat Button Settings', 'animated-floating-chat-button' ),
        esc_html__( 'Chat Button', 'animated-floating-chat-button' ),
        'manage_options',
        'chat-button-settings',
        'afcb_settings_page',
        'dashicons-format-chat',
        100
    );
}
add_action( 'admin_menu', 'afcb_admin_menu' );

/**
 * Set default options and schedule the activation redirect.
 *
 * @return void
 */
function afcb_activate_plugin() {
    add_option( 'afcb_do_activation_redirect', true );
    add_option( AFCB_OPTION_VISIBILITY, 'off' );
    add_option( AFCB_OPTION_PHONE, AFCB_DEFAULT_PHONE );
    add_option( AFCB_OPTION_MESSAGE, AFCB_DEFAULT_MESSAGE );
    add_option( AFCB_OPTION_HORIZONTAL_POSITION, 'right' );
    add_option( AFCB_OPTION_VERTICAL_POSITION, 'bottom' );
    add_option( AFCB_OPTION_HORIZONTAL_OFFSET, 30 );
    add_option( AFCB_OPTION_VERTICAL_OFFSET, 30 );
}
register_activation_hook( __FILE__, 'afcb_activate_plugin' );

/**
 * Redirect administrators to the settings page after activation.
 *
 * @return void
 */
function afcb_redirect() {
    if ( ! get_option( 'afcb_do_activation_redirect', false ) ) {
        return;
    }

    delete_option( 'afcb_do_activation_redirect' );

    if ( isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    wp_safe_redirect( admin_url( 'admin.php?page=chat-button-settings' ) );
    exit;
}
add_action( 'admin_init', 'afcb_redirect' );

/**
 * Render the plugin settings page.
 *
 * @return void
 */
function afcb_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $phone_number        = get_option( AFCB_OPTION_PHONE, AFCB_DEFAULT_PHONE );
    $message             = get_option( AFCB_OPTION_MESSAGE, AFCB_DEFAULT_MESSAGE );
    $visibility          = get_option( AFCB_OPTION_VISIBILITY, 'off' );
    $horizontal_position = afcb_sanitize_horizontal_position( get_option( AFCB_OPTION_HORIZONTAL_POSITION, 'right' ) );
    $vertical_position   = afcb_sanitize_vertical_position( get_option( AFCB_OPTION_VERTICAL_POSITION, 'bottom' ) );
    $horizontal_offset   = absint( get_option( AFCB_OPTION_HORIZONTAL_OFFSET, 30 ) );
    $vertical_offset     = absint( get_option( AFCB_OPTION_VERTICAL_OFFSET, 30 ) );
    ?>
    <div class="wrap afcb-admin-page">
        <div class="afcb-admin-shell">
            <header class="afcb-admin-topbar">
                <div class="afcb-window-dots" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <div class="afcb-brand">
                    <span class="dashicons dashicons-format-chat" aria-hidden="true"></span>
                    <span><?php echo esc_html__( 'Animated Floating Chat Button', 'animated-floating-chat-button' ); ?></span>
                </div>

                <span class="afcb-version">
                    <?php
                    printf(
                        /* translators: %s: Plugin version number. */
                        esc_html__( 'Version %s', 'animated-floating-chat-button' ),
                        esc_html( AFCB_PLUGIN_VERSION )
                    );
                    ?>
                </span>
            </header>

            <div class="afcb-admin-content">
                <section class="afcb-settings-panel">
                    <div class="afcb-section-heading">
                        <h1><?php echo esc_html__( 'Chat Button Settings', 'animated-floating-chat-button' ); ?></h1>
                        <p><?php echo esc_html__( 'Configure your WhatsApp button and preview the changes instantly.', 'animated-floating-chat-button' ); ?></p>
                    </div>

                    <form method="post" action="options.php" class="afcb-modern-form">
                        <?php settings_fields( 'afcb_settings' ); ?>

                        <div class="afcb-field-group">
                            <label for="<?php echo esc_attr( AFCB_OPTION_PHONE ); ?>">
                                <span class="dashicons dashicons-phone" aria-hidden="true"></span>
                                <span>
                                    <strong><?php echo esc_html__( 'WhatsApp Number', 'animated-floating-chat-button' ); ?></strong>
                                    <small><?php echo esc_html__( 'Include the country code, for example +880123456789.', 'animated-floating-chat-button' ); ?></small>
                                </span>
                            </label>

                            <input
                                type="text"
                                id="<?php echo esc_attr( AFCB_OPTION_PHONE ); ?>"
                                name="<?php echo esc_attr( AFCB_OPTION_PHONE ); ?>"
                                value="<?php echo esc_attr( $phone_number ); ?>"
                                placeholder="<?php echo esc_attr( AFCB_DEFAULT_PHONE ); ?>"
                                inputmode="tel"
                                autocomplete="tel"
                            >
                        </div>

                        <div class="afcb-field-group">
                            <label for="<?php echo esc_attr( AFCB_OPTION_MESSAGE ); ?>">
                                <span class="dashicons dashicons-editor-alignleft" aria-hidden="true"></span>
                                <span>
                                    <strong><?php echo esc_html__( 'Default Message', 'animated-floating-chat-button' ); ?></strong>
                                    <small><?php echo esc_html__( 'This message will be pre-filled when a visitor opens WhatsApp.', 'animated-floating-chat-button' ); ?></small>
                                </span>
                            </label>

                            <input
                                type="text"
                                id="<?php echo esc_attr( AFCB_OPTION_MESSAGE ); ?>"
                                name="<?php echo esc_attr( AFCB_OPTION_MESSAGE ); ?>"
                                value="<?php echo esc_attr( $message ); ?>"
                                placeholder="<?php echo esc_attr__( 'Enter a default message', 'animated-floating-chat-button' ); ?>"
                            >
                        </div>

                        <div class="afcb-field-group afcb-choice-field">
                            <div class="afcb-field-label">
                                <span class="dashicons dashicons-leftright" aria-hidden="true"></span>
                                <span>
                                    <strong><?php echo esc_html__( 'Horizontal Position', 'animated-floating-chat-button' ); ?></strong>
                                    <small><?php echo esc_html__( 'Choose which side of the screen displays the button.', 'animated-floating-chat-button' ); ?></small>
                                </span>
                            </div>

                            <div class="afcb-segmented-control">
                                <label>
                                    <input
                                        type="radio"
                                        name="<?php echo esc_attr( AFCB_OPTION_HORIZONTAL_POSITION ); ?>"
                                        value="left"
                                        <?php checked( $horizontal_position, 'left' ); ?>
                                    >
                                    <span>
                                        <span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
                                        <?php echo esc_html__( 'Left', 'animated-floating-chat-button' ); ?>
                                    </span>
                                </label>

                                <label>
                                    <input
                                        type="radio"
                                        name="<?php echo esc_attr( AFCB_OPTION_HORIZONTAL_POSITION ); ?>"
                                        value="right"
                                        <?php checked( $horizontal_position, 'right' ); ?>
                                    >
                                    <span>
                                        <?php echo esc_html__( 'Right', 'animated-floating-chat-button' ); ?>
                                        <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="afcb-field-group afcb-choice-field">
                            <div class="afcb-field-label">
                                <span class="dashicons dashicons-sort" aria-hidden="true"></span>
                                <span>
                                    <strong><?php echo esc_html__( 'Vertical Position', 'animated-floating-chat-button' ); ?></strong>
                                    <small><?php echo esc_html__( 'Choose whether the button appears at the top or bottom.', 'animated-floating-chat-button' ); ?></small>
                                </span>
                            </div>

                            <div class="afcb-segmented-control">
                                <label>
                                    <input
                                        type="radio"
                                        name="<?php echo esc_attr( AFCB_OPTION_VERTICAL_POSITION ); ?>"
                                        value="top"
                                        <?php checked( $vertical_position, 'top' ); ?>
                                    >
                                    <span>
                                        <span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
                                        <?php echo esc_html__( 'Top', 'animated-floating-chat-button' ); ?>
                                    </span>
                                </label>

                                <label>
                                    <input
                                        type="radio"
                                        name="<?php echo esc_attr( AFCB_OPTION_VERTICAL_POSITION ); ?>"
                                        value="bottom"
                                        <?php checked( $vertical_position, 'bottom' ); ?>
                                    >
                                    <span>
                                        <?php echo esc_html__( 'Bottom', 'animated-floating-chat-button' ); ?>
                                        <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="afcb-field-group afcb-range-field">
                            <label for="afcb-horizontal-range">
                                <span class="afcb-axis-icon" aria-hidden="true">↔</span>
                                <span>
                                    <strong><?php echo esc_html__( 'Horizontal Space', 'animated-floating-chat-button' ); ?></strong>
                                    <small><?php echo esc_html__( 'Distance from the selected left or right edge.', 'animated-floating-chat-button' ); ?></small>
                                </span>
                            </label>

                            <div class="afcb-range-control">
                                <input
                                    type="range"
                                    id="afcb-horizontal-range"
                                    min="0"
                                    max="200"
                                    step="1"
                                    value="<?php echo esc_attr( $horizontal_offset ); ?>"
                                >

                                <div class="afcb-number-control">
                                    <input
                                        type="number"
                                        id="<?php echo esc_attr( AFCB_OPTION_HORIZONTAL_OFFSET ); ?>"
                                        name="<?php echo esc_attr( AFCB_OPTION_HORIZONTAL_OFFSET ); ?>"
                                        min="0"
                                        max="200"
                                        step="1"
                                        value="<?php echo esc_attr( $horizontal_offset ); ?>"
                                    >
                                    <span><?php echo esc_html__( 'px', 'animated-floating-chat-button' ); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="afcb-field-group afcb-range-field">
                            <label for="afcb-vertical-range">
                                <span class="afcb-axis-icon" aria-hidden="true">↕</span>
                                <span>
                                    <strong><?php echo esc_html__( 'Vertical Space', 'animated-floating-chat-button' ); ?></strong>
                                    <small><?php echo esc_html__( 'Distance from the selected top or bottom edge.', 'animated-floating-chat-button' ); ?></small>
                                </span>
                            </label>

                            <div class="afcb-range-control">
                                <input
                                    type="range"
                                    id="afcb-vertical-range"
                                    min="0"
                                    max="200"
                                    step="1"
                                    value="<?php echo esc_attr( $vertical_offset ); ?>"
                                >

                                <div class="afcb-number-control">
                                    <input
                                        type="number"
                                        id="<?php echo esc_attr( AFCB_OPTION_VERTICAL_OFFSET ); ?>"
                                        name="<?php echo esc_attr( AFCB_OPTION_VERTICAL_OFFSET ); ?>"
                                        min="0"
                                        max="200"
                                        step="1"
                                        value="<?php echo esc_attr( $vertical_offset ); ?>"
                                    >
                                    <span><?php echo esc_html__( 'px', 'animated-floating-chat-button' ); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="afcb-field-group afcb-toggle-field">
                            <label for="<?php echo esc_attr( AFCB_OPTION_VISIBILITY ); ?>">
                                <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                                <span>
                                    <strong><?php echo esc_html__( 'Visibility on Frontend', 'animated-floating-chat-button' ); ?></strong>
                                    <small><?php echo esc_html__( 'Enable or disable the floating WhatsApp button.', 'animated-floating-chat-button' ); ?></small>
                                </span>
                            </label>

                            <label class="afcb-toggle-switch">
                                <input
                                    type="checkbox"
                                    id="<?php echo esc_attr( AFCB_OPTION_VISIBILITY ); ?>"
                                    name="<?php echo esc_attr( AFCB_OPTION_VISIBILITY ); ?>"
                                    value="on"
                                    <?php checked( $visibility, 'on' ); ?>
                                >
                                <span class="afcb-toggle-slider" aria-hidden="true"></span>
                                <span class="screen-reader-text"><?php echo esc_html__( 'Toggle frontend visibility', 'animated-floating-chat-button' ); ?></span>
                            </label>
                        </div>

                        <div class="afcb-form-actions">
                            <button type="submit" class="button button-primary afcb-save-button">
                                <span class="dashicons dashicons-saved afcb-save-btn" aria-hidden="true"></span>
                                <?php echo esc_html__( 'Save Changes', 'animated-floating-chat-button' ); ?>
                            </button>
                        </div>
                    </form>
                </section>

                <aside class="afcb-sidebar">
                    <section class="afcb-preview-panel">
                        <div class="afcb-preview-heading">
                            <h2><?php echo esc_html__( 'Live Preview', 'animated-floating-chat-button' ); ?></h2>
                        </div>

                        <div class="afcb-browser-preview">
                            <div class="afcb-browser-toolbar" aria-hidden="true">
                                <div class="afcb-browser-controls">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                                <div class="afcb-browser-address"></div>
                            </div>

                            <div class="afcb-browser-content">
                                <div class="afcb-preview-site-header" aria-hidden="true">
                                    <div class="afcb-preview-logo"></div>
                                    <div class="afcb-preview-menu">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </div>

                                <div class="afcb-preview-site-body" aria-hidden="true">
                                    <div class="afcb-preview-title"></div>
                                    <div class="afcb-preview-text"></div>
                                    <div class="afcb-preview-text afcb-preview-text-short"></div>
                                    <div class="afcb-preview-cards">
                                        <span></span>
                                        <span></span>
                                    </div>
                                </div>

                                <div
                                    id="afcb-admin-floating-preview"
                                    class="afcb-position-wrapper"
                                    aria-label="<?php echo esc_attr__( 'Chat button preview', 'animated-floating-chat-button' ); ?>"
                                >
                                    <span class="pulse" aria-hidden="true"></span>
                                    <span class="pulse" aria-hidden="true"></span>
                                    <span class="pulse" aria-hidden="true"></span>

                                    <span class="chat-button pulse">
                                        <span class="chat-icon" aria-hidden="true">
                                            <img
                                                src="<?php echo esc_url( AFCB_PLUGIN_URL . 'assets/whatsapp.png' ); ?>"
                                                alt=""
                                            >
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="afcb-preview-status<?php echo ( 'on' === $visibility ) ? '' : ' is-hidden'; ?>">
                            <span class="afcb-status-dot" aria-hidden="true"></span>
                            <span id="afcb-preview-status-text">
                                <?php
                                echo ( 'on' === $visibility )
                                    ? esc_html__( 'Button is visible', 'animated-floating-chat-button' )
                                    : esc_html__( 'Button is hidden', 'animated-floating-chat-button' );
                                ?>
                            </span>
                        </div>
                    </section>

                    <section class="afcb-developer-card">
                        <img
                            src="<?php echo esc_url( AFCB_PLUGIN_URL . 'assets/developer.png' ); ?>"
                            alt="<?php echo esc_attr__( 'Freelancer Habib', 'animated-floating-chat-button' ); ?>"
                            class="afcb-developer-photo"
                        >

                        <div class="afcb-developer-details">
                            <h2><?php echo esc_html__( 'Adnan Habib', 'animated-floating-chat-button' ); ?></h2>
                            <p><?php echo esc_html__( 'Plugin Developer', 'animated-floating-chat-button' ); ?></p>
                        </div>

                        <div class="afcb-developer-actions">
                            <a
                                class="afcb-developer-button afcb-email-button"
                                href="<?php echo esc_url( 'mailto:hirehabibur@gmail.com' ); ?>"
                            >
                                <span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
                                <?php echo esc_html__( 'Email', 'animated-floating-chat-button' ); ?>
                            </a>

                            <a
                                class="afcb-developer-button afcb-whatsapp-button"
                                href="<?php echo esc_url( 'https://wa.me/8801770268035' ); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <img
                                    src="<?php echo esc_url( AFCB_PLUGIN_URL . 'assets/whatsapp.png' ); ?>"
                                    alt=""
                                    aria-hidden="true"
                                >
                                <?php echo esc_html__( 'WhatsApp Me', 'animated-floating-chat-button' ); ?>
                            </a>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Register plugin settings.
 *
 * @return void
 */
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
}
add_action( 'admin_init', 'afcb_register_settings' );

/**
 * Add a success notice after settings are saved.
 *
 * @return void
 */
function afcb_add_settings_message() {
    if (
        ! isset( $_GET['page'], $_GET['settings-updated'] )
        || 'chat-button-settings' !== sanitize_key( wp_unslash( $_GET['page'] ) )
        || 'true' !== sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) )
    ) {
        return;
    }

    add_settings_error(
        'afcb_settings',
        'afcb_message_saved',
        esc_html__( 'Settings saved successfully.', 'animated-floating-chat-button' ),
        'updated'
    );
}
add_action( 'admin_init', 'afcb_add_settings_message' );

/**
 * Display plugin settings notices on the plugin settings page.
 *
 * @return void
 */
function afcb_admin_notices() {
    $screen = get_current_screen();

    if ( ! $screen || 'toplevel_page_chat-button-settings' !== $screen->id ) {
        return;
    }

    settings_errors( 'afcb_settings' );
}
add_action( 'admin_notices', 'afcb_admin_notices' );

/**
 * Add links to the Plugins screen.
 *
 * @param array $links Existing plugin action links.
 * @return array
 */
function afcb_add_action_links( $links ) {
    $settings_link = sprintf(
        '<a href="%1$s">%2$s</a>',
        esc_url( admin_url( 'admin.php?page=chat-button-settings' ) ),
        esc_html__( 'Settings', 'animated-floating-chat-button' )
    );

    $hire_me_link = sprintf(
        '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
        esc_url( 'https://www.freelancer.com/hireme/csehabiburr183' ),
        esc_html__( 'Hire Me', 'animated-floating-chat-button' )
    );

    array_unshift( $links, $settings_link, $hire_me_link );

    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'afcb_add_action_links' );