<?php
/**
 * Register custom fields for Malt Custom Filters
 * 
 * This file registers all custom meta fields required for the filtering system
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Malt_Register_Fields {
    
    /**
     * Initialize field registration
     */
    public static function init() {
        // Register meta fields for properties
        add_action('init', array(__CLASS__, 'register_meta_fields'));
        
        // Add meta boxes if needed (optional, for admin interface)
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        add_action('save_post', array(__CLASS__, 'save_meta_fields'));
    }
    
    /**
     * Register custom meta fields
     */
    public static function register_meta_fields() {
        $post_type = 'property'; // Houzez property post type
        
        // Type de lieu
        register_post_meta($post_type, 'type_lieu', array(
            'type' => 'string',
            'description' => 'Type de lieu (Habitations, Bureaux, Industrie, etc.)',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
        ));
        
        // Architecture
        register_post_meta($post_type, 'architecture', array(
            'type' => 'string',
            'description' => 'Architecture style (Classique, Moderne, Industrielle, Futuriste)',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
        ));
        
        // Décoration
        register_post_meta($post_type, 'decoration', array(
            'type' => 'string',
            'description' => 'Décoration style (Ethnique, Minimaliste, Design, Contemporain)',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
        ));
        
        // Pièces & éléments
        register_post_meta($post_type, 'pieces', array(
            'type' => 'string',
            'description' => 'Pièces & éléments (Bureau, Open space, etc.)',
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
        ));
    }
    
    /**
     * Add meta boxes for admin
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'malt_custom_fields',
            __('Malt Custom Filters', 'malt-custom-filters'),
            array(__CLASS__, 'render_meta_box'),
            'property',
            'normal',
            'high'
        );
    }
    
    /**
     * Render meta box
     */
    public static function render_meta_box($post) {
        wp_nonce_field('malt_save_meta_fields', 'malt_meta_fields_nonce');
        
        $type_lieu = get_post_meta($post->ID, 'type_lieu', true);
        $architecture = get_post_meta($post->ID, 'architecture', true);
        $decoration = get_post_meta($post->ID, 'decoration', true);
        $pieces = get_post_meta($post->ID, 'pieces', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="type_lieu"><?php _e('Type de lieu', 'malt-custom-filters'); ?></label></th>
                <td>
                    <input type="text" id="type_lieu" name="type_lieu" value="<?php echo esc_attr($type_lieu); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th><label for="architecture"><?php _e('Architecture', 'malt-custom-filters'); ?></label></th>
                <td>
                    <select id="architecture" name="architecture">
                        <option value=""><?php _e('Select...', 'malt-custom-filters'); ?></option>
                        <option value="Classique" <?php selected($architecture, 'Classique'); ?>><?php _e('Classique', 'malt-custom-filters'); ?></option>
                        <option value="Moderne" <?php selected($architecture, 'Moderne'); ?>><?php _e('Moderne', 'malt-custom-filters'); ?></option>
                        <option value="Industrielle" <?php selected($architecture, 'Industrielle'); ?>><?php _e('Industrielle', 'malt-custom-filters'); ?></option>
                        <option value="Futuriste" <?php selected($architecture, 'Futuriste'); ?>><?php _e('Futuriste', 'malt-custom-filters'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="decoration"><?php _e('Décoration', 'malt-custom-filters'); ?></label></th>
                <td>
                    <select id="decoration" name="decoration">
                        <option value=""><?php _e('Select...', 'malt-custom-filters'); ?></option>
                        <option value="Ethnique" <?php selected($decoration, 'Ethnique'); ?>><?php _e('Ethnique', 'malt-custom-filters'); ?></option>
                        <option value="Minimaliste" <?php selected($decoration, 'Minimaliste'); ?>><?php _e('Minimaliste', 'malt-custom-filters'); ?></option>
                        <option value="Design" <?php selected($decoration, 'Design'); ?>><?php _e('Design', 'malt-custom-filters'); ?></option>
                        <option value="Contemporain" <?php selected($decoration, 'Contemporain'); ?>><?php _e('Contemporain', 'malt-custom-filters'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="pieces"><?php _e('Pièces & éléments', 'malt-custom-filters'); ?></label></th>
                <td>
                    <input type="text" id="pieces" name="pieces" value="<?php echo esc_attr($pieces); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save meta fields
     */
    public static function save_meta_fields($post_id) {
        // Check nonce
        if (!isset($_POST['malt_meta_fields_nonce']) || !wp_verify_nonce($_POST['malt_meta_fields_nonce'], 'malt_save_meta_fields')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save fields
        $fields = array('type_lieu', 'architecture', 'decoration', 'pieces');
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
}

