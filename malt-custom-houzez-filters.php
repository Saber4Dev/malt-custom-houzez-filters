<?php
/**
 * Plugin Name: Malt Custom Filters
 * Plugin URI: https://malt.com
 * Description: Custom multi-level filtering system for Houzez theme. Replaces default search with Type de lieu, Architecture, Décoration, and Pièces & éléments filters.
 * Version: 1.0.0
 * Author: Rana
 * Author URI: https://ranoyiart.com
 * Text Domain: malt-custom-filters
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MALT_FILTERS_VERSION', '1.0.0');
define('MALT_FILTERS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MALT_FILTERS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MALT_FILTERS_PLUGIN_FILE', __FILE__);

/**
 * Main plugin class
 */
class Malt_Custom_Filters {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Load plugin files
        $this->load_dependencies();
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Register shortcode
        add_shortcode('malt_filters', array($this, 'render_shortcode'));
        add_shortcode('custom_filters', array($this, 'render_shortcode')); // Alias for compatibility
        
        // Initialize settings page (hook early to ensure menu is registered)
        if (is_admin()) {
            add_action('admin_menu', array($this, 'init_settings_page'), 5);
        }
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once MALT_FILTERS_PLUGIN_DIR . 'includes/register-fields.php';
        require_once MALT_FILTERS_PLUGIN_DIR . 'includes/render-shortcode.php';
        require_once MALT_FILTERS_PLUGIN_DIR . 'includes/query-hooks.php';
        require_once MALT_FILTERS_PLUGIN_DIR . 'includes/settings-page.php';
        
        // Initialize classes after they're loaded
        if (class_exists('Malt_Register_Fields')) {
            Malt_Register_Fields::init();
        }
        if (class_exists('Malt_Query_Hooks')) {
            Malt_Query_Hooks::init();
        }
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_assets() {
        // Only load on pages that might use the shortcode
        if (is_page() || is_single() || is_archive() || is_home()) {
            wp_enqueue_style(
                'malt-filters-css',
                MALT_FILTERS_PLUGIN_URL . 'assets/css/custom-filters.css',
                array(),
                MALT_FILTERS_VERSION
            );
            
            wp_enqueue_script(
                'malt-filters-js',
                MALT_FILTERS_PLUGIN_URL . 'assets/js/custom-filters.js',
                array('jquery'),
                MALT_FILTERS_VERSION,
                true
            );
            
            // Get settings
            $settings = get_option('malt_filters_options', array());
            
            // Localize script for AJAX
            wp_localize_script('malt-filters-js', 'maltFilters', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('malt_filters_nonce'),
                'searchPageUrl' => $this->get_search_page_url(),
                'pluginUrl' => MALT_FILTERS_PLUGIN_URL,
                'autoSubmit' => isset($settings['enable_auto_submit']) ? (bool)$settings['enable_auto_submit'] : false,
                'buttonText' => isset($settings['button_text']) ? $settings['button_text'] : __('Rechercher', 'malt-custom-filters'),
                'buttonColor' => isset($settings['button_color']) ? $settings['button_color'] : '#0073aa',
                'buttonHoverColor' => isset($settings['button_hover_color']) ? $settings['button_hover_color'] : '#005a87',
                'buttonTextColor' => isset($settings['button_text_color']) ? $settings['button_text_color'] : '#ffffff',
                'panelMaxHeight' => isset($settings['panel_max_height']) ? absint($settings['panel_max_height']) : 600
            ));
            
            // Add inline CSS for button colors
            $this->add_inline_styles($settings);
        }
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_assets($hook) {
        // Only load on settings page
        if ($hook !== 'settings_page_malt-filters-settings') {
            return;
        }
        
        // Enqueue WordPress color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Add inline script to initialize color pickers
        wp_add_inline_script('wp-color-picker', '
            jQuery(document).ready(function($) {
                $(".malt-color-picker").wpColorPicker();
            });
        ');
    }
    
    /**
     * Add inline styles for button colors
     */
    private function add_inline_styles($settings) {
        $button_color = isset($settings['button_color']) ? $settings['button_color'] : '#0073aa';
        $button_hover_color = isset($settings['button_hover_color']) ? $settings['button_hover_color'] : '#005a87';
        $button_text_color = isset($settings['button_text_color']) ? $settings['button_text_color'] : '#ffffff';
        $panel_max_height = isset($settings['panel_max_height']) ? absint($settings['panel_max_height']) : 600;
        
        $css = "
            .malt-search-btn {
                background-color: {$button_color} !important;
                color: {$button_text_color} !important;
            }
            .malt-search-btn:hover {
                background-color: {$button_hover_color} !important;
            }
            .malt-panel {
                max-height: {$panel_max_height}px !important;
            }
        ";
        
        wp_add_inline_style('malt-filters-css', $css);
    }
    
    /**
     * Get Houzez search results page URL
     */
    private function get_search_page_url() {
        // Check settings first
        $settings = get_option('malt_filters_options', array());
        if (!empty($settings['search_page_url'])) {
            return get_permalink($settings['search_page_url']);
        }
        
        // Try to get Houzez search page
        $search_page = get_option('houzez_search_result_page');
        if ($search_page) {
            return get_permalink($search_page);
        }
        
        // Fallback to properties archive
        $properties_page = get_option('houzez_properties_page');
        if ($properties_page) {
            return get_permalink($properties_page);
        }
        
        // Final fallback
        return home_url('/properties/');
    }
    
    /**
     * Initialize settings page (called on admin_init)
     */
    public function init_settings_page() {
        if (class_exists('Malt_Settings_Page')) {
            Malt_Settings_Page::init();
        }
    }
    
    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        if (class_exists('Malt_Render_Shortcode')) {
            return Malt_Render_Shortcode::render($atts);
        }
        return '';
    }
}

/**
 * Initialize plugin
 */
function malt_custom_filters_init() {
    return Malt_Custom_Filters::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'malt_custom_filters_init');

