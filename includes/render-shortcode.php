<?php
/**
 * Render shortcode for Malt Custom Filters
 * 
 * This file handles the HTML output for the [malt_filters] shortcode
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Malt_Render_Shortcode {
    
    /**
     * Render the filters shortcode
     */
    public static function render($atts) {
        $atts = shortcode_atts(array(
            'class' => '',
        ), $atts);
        
        ob_start();
        ?>
        <div class="malt-filters-wrapper <?php echo esc_attr($atts['class']); ?>">
            <form id="malt-filters-form" class="malt-filters-form" method="get" action="<?php echo esc_url(self::get_search_url()); ?>">
                
                <!-- Title -->
                <h2 class="malt-filters-title"><?php _e('Quel decor recherchez-vous ?', 'malt-custom-filters'); ?></h2>
                
                <!-- First Row: Search Fields -->
                <div class="malt-filters-row malt-filters-row-top">
                    
                    <!-- Type de projet -->
                    <div class="malt-filter-item malt-filter-select">
                        <label for="malt-type-projet" class="malt-filter-label"><?php _e('Type de projet', 'malt-custom-filters'); ?></label>
                        <select name="type_projet" id="malt-type-projet" class="malt-select-field">
                            <option value=""><?php _e('Sélectionner...', 'malt-custom-filters'); ?></option>
                            <option value="residential"><?php _e('Résidentiel', 'malt-custom-filters'); ?></option>
                            <option value="commercial"><?php _e('Commercial', 'malt-custom-filters'); ?></option>
                            <option value="industrial"><?php _e('Industriel', 'malt-custom-filters'); ?></option>
                            <option value="hospitality"><?php _e('Hôtellerie', 'malt-custom-filters'); ?></option>
                            <option value="other"><?php _e('Autre', 'malt-custom-filters'); ?></option>
                        </select>
                    </div>
                    
                    <!-- Recherche libre ou par reference -->
                    <div class="malt-filter-item malt-filter-text">
                        <label for="malt-reference" class="malt-filter-label"><?php _e('Recherche libre ou par référence', 'malt-custom-filters'); ?> <span class="malt-filter-example">(ex : 0001)</span></label>
                        <input type="text" name="reference" id="malt-reference" class="malt-text-field" placeholder="<?php esc_attr_e('Entrez une référence...', 'malt-custom-filters'); ?>" />
                    </div>
                    
                    <!-- Adresse, ville, departement -->
                    <div class="malt-filter-item malt-filter-location">
                        <label for="malt-location" class="malt-filter-label"><?php _e('Adresse, ville, département, etc.', 'malt-custom-filters'); ?></label>
                        <div class="malt-location-input-wrapper">
                            <span class="malt-pin-icon">📍</span>
                            <input type="text" name="location" id="malt-location" class="malt-text-field malt-location-field" placeholder="<?php esc_attr_e('Adresse, ville, département...', 'malt-custom-filters'); ?>" />
                        </div>
                    </div>
                    
                </div>
                
                <!-- Second Row: Current Filters -->
                <div class="rana-houzez-filter-bar malt-filters-row malt-filters-row-bottom">
                    
                    <!-- Filter 1: Type de lieu -->
                    <div class="malt-filter-item" data-filter="type_lieu">
                        <label class="malt-filter-label"><?php _e('Type de lieu', 'malt-custom-filters'); ?></label>
                        <div class="malt-filter-trigger">
                            <span class="malt-filter-value"></span>
                            <span class="malt-filter-arrow"></span>
                        </div>
                        <input type="hidden" name="type_lieu" id="malt-type-lieu" value="" />
                    </div>
                    
                    <!-- Filter 2: Architecture -->
                    <div class="malt-filter-item" data-filter="architecture">
                        <label class="malt-filter-label"><?php _e('Architecture', 'malt-custom-filters'); ?></label>
                        <div class="malt-filter-trigger">
                            <span class="malt-filter-value"></span>
                            <span class="malt-filter-arrow"></span>
                        </div>
                        <input type="hidden" name="architecture" id="malt-architecture" value="" />
                    </div>
                    
                    <!-- Filter 3: Décoration -->
                    <div class="malt-filter-item" data-filter="decoration">
                        <label class="malt-filter-label"><?php _e('Décoration', 'malt-custom-filters'); ?></label>
                        <div class="malt-filter-trigger">
                            <span class="malt-filter-value"></span>
                            <span class="malt-filter-arrow"></span>
                        </div>
                        <input type="hidden" name="decoration" id="malt-decoration" value="" />
                    </div>
                    
                    <!-- Filter 4: Pièces & éléments -->
                    <div class="malt-filter-item" data-filter="pieces">
                        <label class="malt-filter-label"><?php _e('Pièces & éléments', 'malt-custom-filters'); ?></label>
                        <div class="malt-filter-trigger">
                            <span class="malt-filter-value"></span>
                            <span class="malt-filter-arrow"></span>
                        </div>
                        <input type="hidden" name="pieces" id="malt-pieces" value="" />
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="malt-filter-submit">
                        <button type="submit" class="malt-search-btn">
                            <?php 
                            $settings = get_option('malt_filters_options', array());
                            $button_text = isset($settings['button_text']) && !empty($settings['button_text']) 
                                ? esc_html($settings['button_text']) 
                                : __('Rechercher', 'malt-custom-filters');
                            echo $button_text;
                            ?>
                        </button>
                    </div>
                    
                </div>
                
                <!-- Mega Panels Container -->
                <div class="malt-filters-panels">
                    <!-- Panels will be injected by JavaScript -->
                </div>
                
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get search results page URL
     */
    private static function get_search_url() {
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
        
        // Final fallback - try to find properties archive
        $properties_archive = get_post_type_archive_link('property');
        if ($properties_archive) {
            return $properties_archive;
        }
        
        return home_url('/');
    }
}

