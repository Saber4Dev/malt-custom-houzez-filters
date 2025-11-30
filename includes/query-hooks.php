<?php
/**
 * Query hooks for integrating with Houzez search
 * 
 * This file hooks into Houzez's search functionality to apply custom filters
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Malt_Query_Hooks {
    
    /**
     * Initialize query hooks
     */
    public static function init() {
        // Hook into Houzez search args
        add_filter('houzez_search_args', array(__CLASS__, 'extend_search_args'), 20, 2);
        
        // Also hook into WP_Query for properties archive
        add_action('pre_get_posts', array(__CLASS__, 'modify_property_query'), 20);
    }
    
    /**
     * Extend Houzez search arguments
     * 
     * @param array $args Query arguments
     * @param array $request Request parameters
     * @return array Modified arguments
     */
    public static function extend_search_args($args, $request) {
        $meta_query = isset($args['meta_query']) ? $args['meta_query'] : array();
        
        // Type de projet
        if (!empty($_GET['type_projet'])) {
            $meta_query[] = array(
                'key' => 'type_projet',
                'value' => sanitize_text_field($_GET['type_projet']),
                'compare' => '='
            );
        }
        
        // Reference (search in title or meta)
        if (!empty($_GET['reference'])) {
            $reference = sanitize_text_field($_GET['reference']);
            // Search in post title or meta
            $args['s'] = $reference;
        }
        
        // Location (search in address meta fields)
        if (!empty($_GET['location'])) {
            $location = sanitize_text_field($_GET['location']);
            $meta_query[] = array(
                'key' => 'property_address',
                'value' => $location,
                'compare' => 'LIKE'
            );
        }
        
        // Type de lieu
        if (!empty($_GET['type_lieu'])) {
            $meta_query[] = array(
                'key' => 'type_lieu',
                'value' => sanitize_text_field($_GET['type_lieu']),
                'compare' => '='
            );
        }
        
        // Architecture
        if (!empty($_GET['architecture'])) {
            $meta_query[] = array(
                'key' => 'architecture',
                'value' => sanitize_text_field($_GET['architecture']),
                'compare' => '='
            );
        }
        
        // Décoration
        if (!empty($_GET['decoration'])) {
            $meta_query[] = array(
                'key' => 'decoration',
                'value' => sanitize_text_field($_GET['decoration']),
                'compare' => '='
            );
        }
        
        // Pièces & éléments (multiple values)
        if (!empty($_GET['pieces'])) {
            $pieces = $_GET['pieces'];
            if (is_array($pieces)) {
                // Multiple values - use OR relation
                $pieces_meta_query = array('relation' => 'OR');
                foreach ($pieces as $piece) {
                    $piece = sanitize_text_field($piece);
                    if (!empty($piece)) {
                        $pieces_meta_query[] = array(
                            'key' => 'pieces',
                            'value' => $piece,
                            'compare' => '='
                        );
                    }
                }
                if (count($pieces_meta_query) > 1) {
                    $meta_query[] = $pieces_meta_query;
                }
            } else {
                // Single value (backward compatibility)
                $meta_query[] = array(
                    'key' => 'pieces',
                    'value' => sanitize_text_field($pieces),
                    'compare' => '='
                );
            }
        }
        
        // Add meta_query if we have any conditions
        if (!empty($meta_query)) {
            if (count($meta_query) > 1) {
                $meta_query['relation'] = 'AND';
            }
            $args['meta_query'] = $meta_query;
        }
        
        return $args;
    }
    
    /**
     * Modify property query on archive pages
     * 
     * @param WP_Query $query
     */
    public static function modify_property_query($query) {
        // Only modify main query on frontend
        if (is_admin() || !$query->is_main_query()) {
            return;
        }
        
        // Only for property post type
        if (!is_post_type_archive('property') && !is_page_template('template-search.php')) {
            return;
        }
        
        $meta_query = $query->get('meta_query');
        if (!is_array($meta_query)) {
            $meta_query = array();
        }
        
        // Type de projet
        if (!empty($_GET['type_projet'])) {
            $meta_query[] = array(
                'key' => 'type_projet',
                'value' => sanitize_text_field($_GET['type_projet']),
                'compare' => '='
            );
        }
        
        // Reference (search in title)
        if (!empty($_GET['reference'])) {
            $reference = sanitize_text_field($_GET['reference']);
            $query->set('s', $reference);
        }
        
        // Location (search in address meta fields)
        if (!empty($_GET['location'])) {
            $location = sanitize_text_field($_GET['location']);
            $meta_query[] = array(
                'key' => 'property_address',
                'value' => $location,
                'compare' => 'LIKE'
            );
        }
        
        // Type de lieu
        if (!empty($_GET['type_lieu'])) {
            $meta_query[] = array(
                'key' => 'type_lieu',
                'value' => sanitize_text_field($_GET['type_lieu']),
                'compare' => '='
            );
        }
        
        // Architecture
        if (!empty($_GET['architecture'])) {
            $meta_query[] = array(
                'key' => 'architecture',
                'value' => sanitize_text_field($_GET['architecture']),
                'compare' => '='
            );
        }
        
        // Décoration
        if (!empty($_GET['decoration'])) {
            $meta_query[] = array(
                'key' => 'decoration',
                'value' => sanitize_text_field($_GET['decoration']),
                'compare' => '='
            );
        }
        
        // Pièces & éléments (multiple values)
        if (!empty($_GET['pieces'])) {
            $pieces = $_GET['pieces'];
            if (is_array($pieces)) {
                // Multiple values - use OR relation
                $pieces_meta_query = array('relation' => 'OR');
                foreach ($pieces as $piece) {
                    $piece = sanitize_text_field($piece);
                    if (!empty($piece)) {
                        $pieces_meta_query[] = array(
                            'key' => 'pieces',
                            'value' => $piece,
                            'compare' => '='
                        );
                    }
                }
                if (count($pieces_meta_query) > 1) {
                    $meta_query[] = $pieces_meta_query;
                }
            } else {
                // Single value (backward compatibility)
                $meta_query[] = array(
                    'key' => 'pieces',
                    'value' => sanitize_text_field($pieces),
                    'compare' => '='
                );
            }
        }
        
        // Apply meta_query if we have conditions
        if (!empty($meta_query)) {
            if (count($meta_query) > 1) {
                $meta_query['relation'] = 'AND';
            }
            $query->set('meta_query', $meta_query);
        }
    }
}

