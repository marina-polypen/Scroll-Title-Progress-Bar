<?php
/*
Plugin Name: Scroll Title Progress Bar
Description: Smooth progress bar with dynamic headings display. Supports custom post types, mobile responsiveness, and extensive customization options.
Version: 7.2
Author: MarinaP
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: scroll-title-progress-bar
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Define plugin constants
define( 'STPB_DIR', plugin_dir_path( __FILE__ ) );
define( 'STPB_URL', plugin_dir_url( __FILE__ ) );
define( 'STPB_VERSION', '7.1' );

// Load required files
require_once STPB_DIR . 'admin/settings.php';

/**
 * Check if the progress bar should be displayed
 *
 * @return boolean
 */
function stpb_should_display() {
    if ( ! is_singular() ) return false;
    
    $options = get_option( 'stpb_options' );
    if ( ! $options ) return false;
    
    // Get "display on" option; default to both posts and pages
    $display_on = isset( $options['display_on'] ) ? $options['display_on'] : 'post,page';
    $post_types = array_map( 'trim', explode( ',', $display_on ) );
    
    // Check if current post type should display
    if ( ! in_array( get_post_type(), $post_types ) ) return false;
    
    // Exclude pages by ID
    if ( is_page() ) {
        $exclude_ids = isset( $options['exclude_ids'] ) ? $options['exclude_ids'] : '';
        if ( ! empty( $exclude_ids ) ) {
            $ids = array_map( 'trim', explode( ',', $exclude_ids ) );
            if ( in_array( get_the_ID(), $ids ) ) return false;
        }
    }
    
    // Check for custom conditions
    if ( isset( $options['custom_conditions'] ) && ! empty( $options['custom_conditions'] ) ) {
        $conditions = $options['custom_conditions'];
        if ( ! eval( 'return ' . $conditions . ';' ) ) return false;
    }
    
    return true;
}

/**
 * Enqueue plugin assets
 */
function stpb_enqueue_assets() {
    if ( ! stpb_should_display() ) return;
    
    // Enqueue styles
    wp_enqueue_style( 
        'stpb-style', 
        STPB_URL . 'assets/style.css', 
        array(), 
        STPB_VERSION 
    );
    
    // Enqueue scripts
    wp_enqueue_script( 
        'stpb-script', 
        STPB_URL . 'assets/script.js', 
        array( 'jquery' ), 
        STPB_VERSION, 
        true 
    );
    
    // Get plugin options
    $options = get_option( 'stpb_options' );
    
    // Prepare settings for JavaScript
    $stpb_settings = array(
        'headings'           => isset( $options['headings'] ) ? $options['headings'] : 'h2,h3',
        'color'              => isset( $options['color'] ) ? $options['color'] : '#ff5722',
        'height'             => isset( $options['height'] ) ? $options['height'] : '50',
        'opacity'            => isset( $options['opacity'] ) ? $options['opacity'] : '0.8',
        'heading_text_size'  => isset( $options['heading_text_size'] ) ? $options['heading_text_size'] : '16',
        'mobile_breakpoint'  => isset( $options['mobile_breakpoint'] ) ? $options['mobile_breakpoint'] : '768',
        'animation_speed'    => isset( $options['animation_speed'] ) ? $options['animation_speed'] : '300',
        'show_on_mobile'     => isset( $options['show_on_mobile'] ) ? $options['show_on_mobile'] : true,
        'position'           => isset( $options['position'] ) ? $options['position'] : 'top',
        'z_index'           => isset( $options['z_index'] ) ? $options['z_index'] : '9999',
        'show_headings_dropdown' => isset( $options['show_headings_dropdown'] ) ? $options['show_headings_dropdown'] : true,
        'exclude_sidebar_footer' => isset( $options['exclude_sidebar_footer'] ) ? $options['exclude_sidebar_footer'] : true
    );
    
    // Localize script
    wp_localize_script( 'stpb-script', 'stpbOptions', $stpb_settings );
}
add_action( 'wp_enqueue_scripts', 'stpb_enqueue_assets' );

/**
 * Output the progress bar HTML
 */
function stpb_output_html() {
    if ( ! stpb_should_display() ) return;
    
    $options = get_option( 'stpb_options' );
    $position = isset( $options['position'] ) ? $options['position'] : 'top';
    $z_index = isset( $options['z_index'] ) ? $options['z_index'] : '9999';
    
    printf(
        '<div class="scroll-progress position-%s" style="z-index: %s;">
            <div class="scroll-title"></div>
            <div class="scroll-progress-bar"></div>
        </div>',
        esc_attr( $position ),
        esc_attr( $z_index )
    );
}
add_action( 'wp_footer', 'stpb_output_html' );

/**
 * Load plugin text domain
 */
function stpb_load_textdomain() {
    load_plugin_textdomain( 
        'scroll-title-progress-bar', 
        false, 
        dirname( plugin_basename( __FILE__ ) ) . '/languages' 
    );
}
add_action( 'plugins_loaded', 'stpb_load_textdomain' );

/**
 * Activation hook
 */
function stpb_activate() {
    // Set default options if they don't exist
    if ( ! get_option( 'stpb_options' ) ) {
        $default_options = array(
            'display_on'         => 'post,page',
            'headings'           => 'h2,h3',
            'color'              => '#ff5722',
            'height'             => '50',
            'opacity'            => '0.8',
            'heading_text_size'  => '16',
            'mobile_breakpoint'  => '768',
            'animation_speed'    => '300',
            'show_on_mobile'     => true,
            'position'           => 'top',
            'z_index'           => '9999',
            'show_headings_dropdown' => true,
            'exclude_sidebar_footer' => true
        );
        update_option( 'stpb_options', $default_options );
    }
}
register_activation_hook( __FILE__, 'stpb_activate' );

/**
 * Deactivation hook
 */
function stpb_deactivate() {
    // Clean up if needed
}
register_deactivation_hook( __FILE__, 'stpb_deactivate' );
