<?php
/**
 * Settings Page for Malt Custom Filters
 * 
 * Handles admin settings page, options, and Help tab
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Malt_Settings_Page {
    
    /**
     * Option group name
     */
    private $option_group = 'malt_filters_settings';
    
    /**
     * Option name
     */
    private $option_name = 'malt_filters_options';
    
    /**
     * Initialize settings
     */
    public static function init() {
        $instance = new self();
        
        // Register settings on admin_init (when settings API is available)
        add_action('admin_init', array($instance, 'register_settings'));
        
        // Add admin menu directly (we're already in admin_menu hook)
        $instance->add_admin_menu();
        
        // Hook help tab to load action
        add_action('load-settings_page_malt-filters-settings', array($instance, 'add_help_tab'));
        
        return $instance;
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            $this->option_group,
            $this->option_name,
            array($this, 'sanitize_settings')
        );
        
        // General Settings Section
        add_settings_section(
            'malt_filters_general',
            __('General Settings', 'malt-custom-filters'),
            array($this, 'render_general_section'),
            'malt-filters-settings'
        );
        
        // Button Settings Section
        add_settings_section(
            'malt_filters_button',
            __('Button Settings', 'malt-custom-filters'),
            array($this, 'render_button_section'),
            'malt-filters-settings'
        );
        
        // Display Settings Section
        add_settings_section(
            'malt_filters_display',
            __('Display Settings', 'malt-custom-filters'),
            array($this, 'render_display_section'),
            'malt-filters-settings'
        );
        
        // Register fields
        $this->register_fields();
    }
    
    /**
     * Register settings fields
     */
    private function register_fields() {
        // General Settings
        add_settings_field(
            'search_page_url',
            __('Search Results Page', 'malt-custom-filters'),
            array($this, 'render_search_page_field'),
            'malt-filters-settings',
            'malt_filters_general'
        );
        
        add_settings_field(
            'enable_auto_submit',
            __('Auto Submit on Selection', 'malt-custom-filters'),
            array($this, 'render_auto_submit_field'),
            'malt-filters-settings',
            'malt_filters_general'
        );
        
        // Button Settings
        add_settings_field(
            'button_text',
            __('Search Button Text', 'malt-custom-filters'),
            array($this, 'render_button_text_field'),
            'malt-filters-settings',
            'malt_filters_button'
        );
        
        add_settings_field(
            'button_color',
            __('Button Background Color', 'malt-custom-filters'),
            array($this, 'render_button_color_field'),
            'malt-filters-settings',
            'malt_filters_button'
        );
        
        add_settings_field(
            'button_hover_color',
            __('Button Hover Color', 'malt-custom-filters'),
            array($this, 'render_button_hover_color_field'),
            'malt-filters-settings',
            'malt_filters_button'
        );
        
        add_settings_field(
            'button_text_color',
            __('Button Text Color', 'malt-custom-filters'),
            array($this, 'render_button_text_color_field'),
            'malt-filters-settings',
            'malt_filters_button'
        );
        
        // Display Settings
        add_settings_field(
            'filters_per_row',
            __('Filters Per Row (Desktop)', 'malt-custom-filters'),
            array($this, 'render_filters_per_row_field'),
            'malt-filters-settings',
            'malt_filters_display'
        );
        
        add_settings_field(
            'panel_max_height',
            __('Panel Max Height (px)', 'malt-custom-filters'),
            array($this, 'render_panel_max_height_field'),
            'malt-filters-settings',
            'malt_filters_display'
        );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Malt Custom Filters Settings', 'malt-custom-filters'),
            __('Malt Filters', 'malt-custom-filters'),
            'manage_options',
            'malt-filters-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if settings were saved
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'malt_filters_messages',
                'malt_filters_message',
                __('Settings saved successfully!', 'malt-custom-filters'),
                'updated'
            );
        }
        
        // Get current tab
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'settings';
        
        settings_errors('malt_filters_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <nav class="nav-tab-wrapper">
                <a href="?page=malt-filters-settings&tab=settings" class="nav-tab <?php echo $current_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Settings', 'malt-custom-filters'); ?>
                </a>
                <a href="?page=malt-filters-settings&tab=help" class="nav-tab <?php echo $current_tab === 'help' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Help/Documentation', 'malt-custom-filters'); ?>
                </a>
            </nav>
            
            <div class="malt-settings-content" style="margin-top: 20px;">
                <?php if ($current_tab === 'settings') : ?>
                    <form action="options.php" method="post">
                        <?php
                        settings_fields($this->option_group);
                        do_settings_sections('malt-filters-settings');
                        submit_button(__('Save Settings', 'malt-custom-filters'));
                        ?>
                    </form>
                <?php else : ?>
                    <?php $this->render_help_documentation(); ?>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
            .malt-settings-content {
                background: #fff;
                padding: 20px;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .malt-help-section {
                margin-bottom: 30px;
            }
            .malt-help-section h2 {
                border-bottom: 2px solid #0073aa;
                padding-bottom: 10px;
                margin-bottom: 15px;
            }
            .malt-help-section h3 {
                color: #0073aa;
                margin-top: 20px;
                margin-bottom: 10px;
            }
            .malt-help-section code {
                background: #f0f0f1;
                padding: 2px 6px;
                border-radius: 3px;
                font-family: Consolas, Monaco, monospace;
            }
            .malt-help-section pre {
                background: #f0f0f1;
                padding: 15px;
                border-radius: 5px;
                overflow-x: auto;
            }
            .malt-help-section pre code {
                background: none;
                padding: 0;
            }
            .malt-help-section ul, .malt-help-section ol {
                margin-left: 20px;
            }
            .malt-help-section li {
                margin-bottom: 8px;
            }
            .malt-help-box {
                background: #f0f8ff;
                border-left: 4px solid #0073aa;
                padding: 15px;
                margin: 20px 0;
            }
        </style>
        <?php
    }
    
    /**
     * Render help/documentation tab
     */
    private function render_help_documentation() {
        ?>
        <div class="malt-help-documentation">
            
            <div class="malt-help-section">
                <h2><?php _e('Plugin Overview', 'malt-custom-filters'); ?></h2>
                <p><?php _e('Malt Custom Filters is a powerful WordPress plugin designed specifically for the Houzez theme. It replaces the default Houzez search bar with a fully custom, multi-level filtering system.', 'malt-custom-filters'); ?></p>
                
                <div class="malt-help-box">
                    <h3><?php _e('Key Features:', 'malt-custom-filters'); ?></h3>
                    <ul>
                        <li><strong><?php _e('Multi-Level Filtering:', 'malt-custom-filters'); ?></strong> <?php _e('Hierarchical filter system with 2-3 levels of depth for complex property searches.', 'malt-custom-filters'); ?></li>
                        <li><strong><?php _e('Four Filter Types:', 'malt-custom-filters'); ?></strong> <?php _e('Type de lieu, Architecture, Décoration, and Pièces & éléments filters.', 'malt-custom-filters'); ?></li>
                        <li><strong><?php _e('Houzez Integration:', 'malt-custom-filters'); ?></strong> <?php _e('Seamlessly integrates with Houzez search functionality using meta queries.', 'malt-custom-filters'); ?></li>
                        <li><strong><?php _e('Theme-Safe:', 'malt-custom-filters'); ?></strong> <?php _e('Never modifies theme files directly, ensuring compatibility with theme updates.', 'malt-custom-filters'); ?></li>
                        <li><strong><?php _e('Responsive Design:', 'malt-custom-filters'); ?></strong> <?php _e('Fully responsive interface that works on all devices.', 'malt-custom-filters'); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="malt-help-section">
                <h2><?php _e('Installation & Setup', 'malt-custom-filters'); ?></h2>
                <h3><?php _e('Step 1: Activate the Plugin', 'malt-custom-filters'); ?></h3>
                <p><?php _e('Navigate to Plugins → Installed Plugins in your WordPress admin and click "Activate" for "Malt Custom Filters".', 'malt-custom-filters'); ?></p>
                
                <h3><?php _e('Step 2: Configure Settings', 'malt-custom-filters'); ?></h3>
                <p><?php _e('Go to Settings → Malt Filters to configure:', 'malt-custom-filters'); ?></p>
                <ul>
                    <li><?php _e('Search Results Page - Select where search results are displayed', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Button colors and text', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Display options (filters per row, panel height)', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Auto-submit behavior', 'malt-custom-filters'); ?></li>
                </ul>
                
                <h3><?php _e('Step 3: Add the Shortcode', 'malt-custom-filters'); ?></h3>
                <p><?php _e('Add the shortcode to your search page or any page where you want the filters to appear:', 'malt-custom-filters'); ?></p>
                <pre><code>[malt_filters]</code></pre>
                <p><?php _e('Or with a custom CSS class:', 'malt-custom-filters'); ?></p>
                <pre><code>[malt_filters class="my-custom-class"]</code></pre>
                
                <h3><?php _e('Step 4: Set Property Filter Values', 'malt-custom-filters'); ?></h3>
                <p><?php _e('When editing a property in WordPress admin, you will see a "Malt Custom Filters" meta box. Fill in the filter values:', 'malt-custom-filters'); ?></p>
                <ul>
                    <li><strong><?php _e('Type de lieu:', 'malt-custom-filters'); ?></strong> <?php _e('Enter the property type (e.g., "Habitations > Appartement > Appartement moderne")', 'malt-custom-filters'); ?></li>
                    <li><strong><?php _e('Architecture:', 'malt-custom-filters'); ?></strong> <?php _e('Select from: Classique, Moderne, Industrielle, or Futuriste', 'malt-custom-filters'); ?></li>
                    <li><strong><?php _e('Décoration:', 'malt-custom-filters'); ?></strong> <?php _e('Select from: Ethnique, Minimaliste, Design, or Contemporain', 'malt-custom-filters'); ?></li>
                    <li><strong><?php _e('Pièces & éléments:', 'malt-custom-filters'); ?></strong> <?php _e('Enter the room or element (e.g., "Bureau" or "Open space")', 'malt-custom-filters'); ?></li>
                </ul>
            </div>
            
            <div class="malt-help-section">
                <h2><?php _e('Usage Examples', 'malt-custom-filters'); ?></h2>
                
                <h3><?php _e('Example 1: Basic Usage', 'malt-custom-filters'); ?></h3>
                <p><?php _e('Add the shortcode to your property search page:', 'malt-custom-filters'); ?></p>
                <pre><code>[malt_filters]</code></pre>
                
                <h3><?php _e('Example 2: With Custom Styling', 'malt-custom-filters'); ?></h3>
                <p><?php _e('Add a custom CSS class for styling:', 'malt-custom-filters'); ?></p>
                <pre><code>[malt_filters class="custom-filter-style"]</code></pre>
                <p><?php _e('Then add CSS to your theme:', 'malt-custom-filters'); ?></p>
                <pre><code>.custom-filter-style .malt-filters-row {
    background: #f5f5f5;
    padding: 20px;
}</code></pre>
                
                <h3><?php _e('Example 3: In a Page Template', 'malt-custom-filters'); ?></h3>
                <p><?php _e('Use the shortcode in PHP templates:', 'malt-custom-filters'); ?></p>
                <pre><code>&lt;?php echo do_shortcode('[malt_filters]'); ?&gt;</code></pre>
            </div>
            
            <div class="malt-help-section">
                <h2><?php _e('Understanding Filter Types', 'malt-custom-filters'); ?></h2>
                
                <h3><?php _e('1. Type de lieu (Location Type)', 'malt-custom-filters'); ?></h3>
                <p><?php _e('This is a hierarchical mega panel filter with 2-3 levels:', 'malt-custom-filters'); ?></p>
                <ul>
                    <li><strong><?php _e('Level 1:', 'malt-custom-filters'); ?></strong> <?php _e('Root categories (Habitations, Bureaux, Industrie, Commerce, Hôtellerie, Autre)', 'malt-custom-filters'); ?></li>
                    <li><strong><?php _e('Level 2:', 'malt-custom-filters'); ?></strong> <?php _e('Sub-categories (e.g., Habitations → Appartement, Maison, Chalet)', 'malt-custom-filters'); ?></li>
                    <li><strong><?php _e('Level 3:', 'malt-custom-filters'); ?></strong> <?php _e('Specific types (e.g., Appartement → Appartement moderne, Studio, Loft)', 'malt-custom-filters'); ?></li>
                </ul>
                <p><?php _e('Users navigate through levels by clicking items. Only one item can be selected at a time.', 'malt-custom-filters'); ?></p>
                
                <h3><?php _e('2. Architecture', 'malt-custom-filters'); ?></h3>
                <p><?php _e('Simple dropdown filter with four options:', 'malt-custom-filters'); ?></p>
                <ul>
                    <li>Classique</li>
                    <li>Moderne</li>
                    <li>Industrielle</li>
                    <li>Futuriste</li>
                </ul>
                
                <h3><?php _e('3. Décoration', 'malt-custom-filters'); ?></h3>
                <p><?php _e('Simple dropdown filter with four options:', 'malt-custom-filters'); ?></p>
                <ul>
                    <li>Ethnique</li>
                    <li>Minimaliste</li>
                    <li>Design</li>
                    <li>Contemporain</li>
                </ul>
                
                <h3><?php _e('4. Pièces & éléments (Rooms & Elements)', 'malt-custom-filters'); ?></h3>
                <p><?php _e('Horizontal mega panel with two main sections:', 'malt-custom-filters'); ?></p>
                <ul>
                    <li><strong><?php _e('Pièces intérieures:', 'malt-custom-filters'); ?></strong> <?php _e('30+ interior room options in a 3-column grid. Some items have sub-menus (e.g., Bureau → Bureau, Open space).', 'malt-custom-filters'); ?></li>
                    <li><strong><?php _e('Éléments extérieurs:', 'malt-custom-filters'); ?></strong> <?php _e('Exterior elements like Terrasse, Jardin, Piscine, etc.', 'malt-custom-filters'); ?></li>
                </ul>
            </div>
            
            <div class="malt-help-section">
                <h2><?php _e('How Search Works', 'malt-custom-filters'); ?></h2>
                <p><?php _e('When a user selects filter values and clicks the search button:', 'malt-custom-filters'); ?></p>
                <ol>
                    <li><?php _e('The selected values are passed as URL parameters (GET) to the search results page.', 'malt-custom-filters'); ?></li>
                    <li><?php _e('The plugin hooks into Houzez search using the <code>houzez_search_args</code> filter.', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Meta queries are added to filter properties based on the selected values.', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Only properties matching ALL selected filters are displayed.', 'malt-custom-filters'); ?></li>
                </ol>
                
                <div class="malt-help-box">
                    <p><strong><?php _e('Note:', 'malt-custom-filters'); ?></strong> <?php _e('Filters work with AND logic - all selected filters must match for a property to appear in results.', 'malt-custom-filters'); ?></p>
                </div>
            </div>
            
            <div class="malt-help-section">
                <h2><?php _e('Customization Guide', 'malt-custom-filters'); ?></h2>
                
                <h3><?php _e('1. Button Customization', 'malt-custom-filters'); ?></h3>
                <p><?php _e('In the Settings tab, you can customize:', 'malt-custom-filters'); ?></p>
                <ul>
                    <li><?php _e('Button text (default: "Rechercher")', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Button background color', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Button hover color', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Button text color', 'malt-custom-filters'); ?></li>
                </ul>
                
                <h3><?php _e('2. Display Settings', 'malt-custom-filters'); ?></h3>
                <ul>
                    <li><strong><?php _e('Filters Per Row:', 'malt-custom-filters'); ?></strong> <?php _e('Control how many filters appear per row on desktop (1-6).', 'malt-custom-filters'); ?></li>
                    <li><strong><?php _e('Panel Max Height:', 'malt-custom-filters'); ?></strong> <?php _e('Set the maximum height of filter panels (300-1200px).', 'malt-custom-filters'); ?></li>
                </ul>
                
                <h3><?php _e('3. CSS Customization', 'malt-custom-filters'); ?></h3>
                <p><?php _e('You can override styles by adding custom CSS to your theme. All filter elements use prefixed classes:', 'malt-custom-filters'); ?></p>
                <ul>
                    <li><code>.malt-filters-wrapper</code> - <?php _e('Main container', 'malt-custom-filters'); ?></li>
                    <li><code>.malt-filter-item</code> - <?php _e('Individual filter', 'malt-custom-filters'); ?></li>
                    <li><code>.malt-panel</code> - <?php _e('Filter panels', 'malt-custom-filters'); ?></li>
                    <li><code>.malt-search-btn</code> - <?php _e('Search button', 'malt-custom-filters'); ?></li>
                    <li><code>.malt-filter-trigger</code> - <?php _e('Filter trigger button', 'malt-custom-filters'); ?></li>
                    <li><code>.malt-level-item</code> - <?php _e('Hierarchical filter items', 'malt-custom-filters'); ?></li>
                </ul>
                
                <h3><?php _e('4. Modifying Filter Options', 'malt-custom-filters'); ?></h3>
                <p><?php _e('To modify the available filter options, edit the <code>filtersData</code> object in:', 'malt-custom-filters'); ?></p>
                <pre><code>/assets/js/custom-filters.js</code></pre>
                <p><?php _e('The structure is JSON-like and easy to modify. Add or remove items as needed.', 'malt-custom-filters'); ?></p>
            </div>
            
            <div class="malt-help-section">
                <h2><?php _e('Troubleshooting', 'malt-custom-filters'); ?></h2>
                
                <h3><?php _e('Filters Not Appearing', 'malt-custom-filters'); ?></h3>
                <ul>
                    <li><?php _e('Ensure the shortcode <code>[malt_filters]</code> is added to your page.', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Check that the plugin is activated in Plugins → Installed Plugins.', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Clear your browser cache and WordPress cache if using a caching plugin.', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Check browser console for JavaScript errors (F12 → Console tab).', 'malt-custom-filters'); ?></li>
                </ul>
                
                <h3><?php _e('Search Not Working', 'malt-custom-filters'); ?></h3>
                <ul>
                    <li><?php _e('Verify the Search Results Page setting in Settings → Malt Filters.', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Ensure properties have the custom field values set (Type de lieu, Architecture, etc.).', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Check that Houzez theme is active and properly configured.', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Test with a property that has all filter values set to ensure the query works.', 'malt-custom-filters'); ?></li>
                </ul>
                
                <h3><?php _e('Styling Issues', 'malt-custom-filters'); ?></h3>
                <ul>
                    <li><?php _e('Check for CSS conflicts with your theme. Use browser inspector to identify conflicting styles.', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Ensure custom CSS is added with sufficient specificity or use <code>!important</code> if needed.', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Verify that the plugin CSS file is loading (check Network tab in browser dev tools).', 'malt-custom-filters'); ?></li>
                </ul>
                
                <h3><?php _e('Panels Not Closing', 'malt-custom-filters'); ?></h3>
                <ul>
                    <li><?php _e('Click outside the panel area to close it.', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Check for JavaScript errors in browser console.', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Ensure jQuery is loaded (required dependency).', 'malt-custom-filters'); ?></li>
                </ul>
            </div>
            
            <div class="malt-help-section">
                <h2><?php _e('Technical Details', 'malt-custom-filters'); ?></h2>
                
                <h3><?php _e('Custom Meta Fields', 'malt-custom-filters'); ?></h3>
                <p><?php _e('The plugin registers the following custom meta fields for the "property" post type:', 'malt-custom-filters'); ?></p>
                <ul>
                    <li><code>type_lieu</code> - <?php _e('Type de lieu value', 'malt-custom-filters'); ?></li>
                    <li><code>architecture</code> - <?php _e('Architecture style', 'malt-custom-filters'); ?></li>
                    <li><code>decoration</code> - <?php _e('Décoration style', 'malt-custom-filters'); ?></li>
                    <li><code>pieces</code> - <?php _e('Pièces & éléments value', 'malt-custom-filters'); ?></li>
                </ul>
                
                <h3><?php _e('Hooks Used', 'malt-custom-filters'); ?></h3>
                <ul>
                    <li><code>houzez_search_args</code> - <?php _e('Filters Houzez search arguments', 'malt-custom-filters'); ?></li>
                    <li><code>pre_get_posts</code> - <?php _e('Modifies property queries on archive pages', 'malt-custom-filters'); ?></li>
                </ul>
                
                <h3><?php _e('Requirements', 'malt-custom-filters'); ?></h3>
                <ul>
                    <li><?php _e('WordPress 5.0 or higher', 'malt-custom-filters'); ?></li>
                    <li><?php _e('PHP 7.2 or higher', 'malt-custom-filters'); ?></li>
                    <li><?php _e('Houzez theme (for full functionality)', 'malt-custom-filters'); ?></li>
                    <li><?php _e('jQuery (included with WordPress)', 'malt-custom-filters'); ?></li>
                </ul>
            </div>
            
        </div>
        <?php
    }
    
    /**
     * Render general section
     */
    public function render_general_section() {
        echo '<p>' . __('Configure general plugin behavior and search page settings.', 'malt-custom-filters') . '</p>';
    }
    
    /**
     * Render button section
     */
    public function render_button_section() {
        echo '<p>' . __('Customize the search button appearance and text.', 'malt-custom-filters') . '</p>';
    }
    
    /**
     * Render display section
     */
    public function render_display_section() {
        echo '<p>' . __('Control how the filters are displayed on the frontend.', 'malt-custom-filters') . '</p>';
    }
    
    /**
     * Render search page field
     */
    public function render_search_page_field() {
        $options = $this->get_options();
        $search_page = isset($options['search_page_url']) ? $options['search_page_url'] : '';
        
        // Get all pages
        $pages = get_pages(array('sort_column' => 'post_title'));
        ?>
        <select name="<?php echo esc_attr($this->option_name); ?>[search_page_url]" id="search_page_url">
            <option value=""><?php _e('Auto-detect (Recommended)', 'malt-custom-filters'); ?></option>
            <?php foreach ($pages as $page) : ?>
                <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($search_page, $page->ID); ?>>
                    <?php echo esc_html($page->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php _e('Select the page where search results are displayed. Leave as "Auto-detect" to use Houzez default settings.', 'malt-custom-filters'); ?>
        </p>
        <?php
    }
    
    /**
     * Render auto submit field
     */
    public function render_auto_submit_field() {
        $options = $this->get_options();
        $auto_submit = isset($options['enable_auto_submit']) ? $options['enable_auto_submit'] : false;
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr($this->option_name); ?>[enable_auto_submit]" value="1" <?php checked($auto_submit, 1); ?> />
            <?php _e('Automatically submit search when a filter is selected', 'malt-custom-filters'); ?>
        </label>
        <p class="description">
            <?php _e('If enabled, the search will automatically submit when any filter value is selected. Otherwise, users must click the search button.', 'malt-custom-filters'); ?>
        </p>
        <?php
    }
    
    /**
     * Render button text field
     */
    public function render_button_text_field() {
        $options = $this->get_options();
        $button_text = isset($options['button_text']) ? $options['button_text'] : __('Rechercher', 'malt-custom-filters');
        ?>
        <input type="text" 
               name="<?php echo esc_attr($this->option_name); ?>[button_text]" 
               value="<?php echo esc_attr($button_text); ?>" 
               class="regular-text" />
        <p class="description">
            <?php _e('Text displayed on the search button.', 'malt-custom-filters'); ?>
        </p>
        <?php
    }
    
    /**
     * Render button color field
     */
    public function render_button_color_field() {
        $options = $this->get_options();
        $button_color = isset($options['button_color']) ? $options['button_color'] : '#0073aa';
        ?>
        <input type="text" 
               name="<?php echo esc_attr($this->option_name); ?>[button_color]" 
               value="<?php echo esc_attr($button_color); ?>" 
               class="malt-color-picker" 
               data-default-color="#0073aa" />
        <p class="description">
            <?php _e('Background color of the search button.', 'malt-custom-filters'); ?>
        </p>
        <?php
    }
    
    /**
     * Render button hover color field
     */
    public function render_button_hover_color_field() {
        $options = $this->get_options();
        $hover_color = isset($options['button_hover_color']) ? $options['button_hover_color'] : '#005a87';
        ?>
        <input type="text" 
               name="<?php echo esc_attr($this->option_name); ?>[button_hover_color]" 
               value="<?php echo esc_attr($hover_color); ?>" 
               class="malt-color-picker" 
               data-default-color="#005a87" />
        <p class="description">
            <?php _e('Background color when hovering over the search button.', 'malt-custom-filters'); ?>
        </p>
        <?php
    }
    
    /**
     * Render button text color field
     */
    public function render_button_text_color_field() {
        $options = $this->get_options();
        $text_color = isset($options['button_text_color']) ? $options['button_text_color'] : '#ffffff';
        ?>
        <input type="text" 
               name="<?php echo esc_attr($this->option_name); ?>[button_text_color]" 
               value="<?php echo esc_attr($text_color); ?>" 
               class="malt-color-picker" 
               data-default-color="#ffffff" />
        <p class="description">
            <?php _e('Text color of the search button.', 'malt-custom-filters'); ?>
        </p>
        <?php
    }
    
    /**
     * Render filters per row field
     */
    public function render_filters_per_row_field() {
        $options = $this->get_options();
        $filters_per_row = isset($options['filters_per_row']) ? absint($options['filters_per_row']) : 4;
        ?>
        <input type="number" 
               name="<?php echo esc_attr($this->option_name); ?>[filters_per_row]" 
               value="<?php echo esc_attr($filters_per_row); ?>" 
               min="1" 
               max="6" 
               class="small-text" />
        <p class="description">
            <?php _e('Number of filters to display per row on desktop screens (1-6).', 'malt-custom-filters'); ?>
        </p>
        <?php
    }
    
    /**
     * Render panel max height field
     */
    public function render_panel_max_height_field() {
        $options = $this->get_options();
        $max_height = isset($options['panel_max_height']) ? absint($options['panel_max_height']) : 600;
        ?>
        <input type="number" 
               name="<?php echo esc_attr($this->option_name); ?>[panel_max_height]" 
               value="<?php echo esc_attr($max_height); ?>" 
               min="300" 
               max="1200" 
               step="50" 
               class="small-text" /> px
        <p class="description">
            <?php _e('Maximum height of filter panels in pixels. Panels will scroll if content exceeds this height.', 'malt-custom-filters'); ?>
        </p>
        <?php
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['search_page_url'])) {
            $sanitized['search_page_url'] = absint($input['search_page_url']);
        }
        
        if (isset($input['enable_auto_submit'])) {
            $sanitized['enable_auto_submit'] = 1;
        } else {
            $sanitized['enable_auto_submit'] = 0;
        }
        
        if (isset($input['button_text'])) {
            $sanitized['button_text'] = sanitize_text_field($input['button_text']);
        }
        
        if (isset($input['button_color'])) {
            $color = sanitize_hex_color($input['button_color']);
            $sanitized['button_color'] = $color ? $color : '#0073aa';
        }
        
        if (isset($input['button_hover_color'])) {
            $color = sanitize_hex_color($input['button_hover_color']);
            $sanitized['button_hover_color'] = $color ? $color : '#005a87';
        }
        
        if (isset($input['button_text_color'])) {
            $color = sanitize_hex_color($input['button_text_color']);
            $sanitized['button_text_color'] = $color ? $color : '#ffffff';
        }
        
        if (isset($input['filters_per_row'])) {
            $sanitized['filters_per_row'] = absint($input['filters_per_row']);
            $sanitized['filters_per_row'] = max(1, min(6, $sanitized['filters_per_row']));
        }
        
        if (isset($input['panel_max_height'])) {
            $sanitized['panel_max_height'] = absint($input['panel_max_height']);
            $sanitized['panel_max_height'] = max(300, min(1200, $sanitized['panel_max_height']));
        }
        
        return $sanitized;
    }
    
    /**
     * Get options
     */
    public function get_options() {
        return get_option($this->option_name, array());
    }
    
    /**
     * Get option value
     */
    public function get_option($key, $default = '') {
        $options = $this->get_options();
        return isset($options[$key]) ? $options[$key] : $default;
    }
    
    /**
     * Add Help tab
     */
    public function add_help_tab() {
        $screen = get_current_screen();
        
        if (!$screen || $screen->id !== 'settings_page_malt-filters-settings') {
            return;
        }
        
        $screen->add_help_tab(array(
            'id' => 'malt-filters-overview',
            'title' => __('Overview', 'malt-custom-filters'),
            'content' => $this->get_help_content_overview()
        ));
        
        $screen->add_help_tab(array(
            'id' => 'malt-filters-installation',
            'title' => __('Installation', 'malt-custom-filters'),
            'content' => $this->get_help_content_installation()
        ));
        
        $screen->add_help_tab(array(
            'id' => 'malt-filters-usage',
            'title' => __('Usage Guide', 'malt-custom-filters'),
            'content' => $this->get_help_content_usage()
        ));
        
        $screen->add_help_tab(array(
            'id' => 'malt-filters-filters',
            'title' => __('Filter Types', 'malt-custom-filters'),
            'content' => $this->get_help_content_filters()
        ));
        
        $screen->add_help_tab(array(
            'id' => 'malt-filters-customization',
            'title' => __('Customization', 'malt-custom-filters'),
            'content' => $this->get_help_content_customization()
        ));
        
        $screen->add_help_tab(array(
            'id' => 'malt-filters-troubleshooting',
            'title' => __('Troubleshooting', 'malt-custom-filters'),
            'content' => $this->get_help_content_troubleshooting()
        ));
        
        $screen->set_help_sidebar($this->get_help_sidebar());
    }
    
    /**
     * Get help content - Overview
     */
    private function get_help_content_overview() {
        return '<h3>' . __('Plugin Overview', 'malt-custom-filters') . '</h3>' .
               '<p>' . __('Malt Custom Filters is a powerful WordPress plugin designed specifically for the Houzez theme. It replaces the default Houzez search bar with a fully custom, multi-level filtering system.', 'malt-custom-filters') . '</p>' .
               '<h4>' . __('Key Features:', 'malt-custom-filters') . '</h4>' .
               '<ul>' .
               '<li><strong>' . __('Multi-Level Filtering:', 'malt-custom-filters') . '</strong> ' . __('Hierarchical filter system with 2-3 levels of depth for complex property searches.', 'malt-custom-filters') . '</li>' .
               '<li><strong>' . __('Four Filter Types:', 'malt-custom-filters') . '</strong> ' . __('Type de lieu, Architecture, Décoration, and Pièces & éléments filters.', 'malt-custom-filters') . '</li>' .
               '<li><strong>' . __('Houzez Integration:', 'malt-custom-filters') . '</strong> ' . __('Seamlessly integrates with Houzez search functionality using meta queries.', 'malt-custom-filters') . '</li>' .
               '<li><strong>' . __('Theme-Safe:', 'malt-custom-filters') . '</strong> ' . __('Never modifies theme files directly, ensuring compatibility with theme updates.', 'malt-custom-filters') . '</li>' .
               '<li><strong>' . __('Responsive Design:', 'malt-custom-filters') . '</strong> ' . __('Fully responsive interface that works on all devices.', 'malt-custom-filters') . '</li>' .
               '</ul>';
    }
    
    /**
     * Get help content - Installation
     */
    private function get_help_content_installation() {
        return '<h3>' . __('Installation Steps', 'malt-custom-filters') . '</h3>' .
               '<ol>' .
               '<li><strong>' . __('Upload Plugin:', 'malt-custom-filters') . '</strong> ' . __('Upload the entire <code>malt-custom-houzez-filters</code> folder to the <code>/wp-content/plugins/</code> directory.', 'malt-custom-filters') . '</li>' .
               '<li><strong>' . __('Activate Plugin:', 'malt-custom-filters') . '</strong> ' . __('Navigate to Plugins in your WordPress admin and click "Activate" for "Malt Custom Filters".', 'malt-custom-filters') . '</li>' .
               '<li><strong>' . __('Configure Settings:', 'malt-custom-filters') . '</strong> ' . __('Go to Settings → Malt Filters to configure button colors, text, and display options.', 'malt-custom-filters') . '</li>' .
               '<li><strong>' . __('Add Shortcode:', 'malt-custom-filters') . '</strong> ' . __('Add the shortcode <code>[malt_filters]</code> or <code>[custom_filters]</code> to your search page.', 'malt-custom-filters') . '</li>' .
               '<li><strong>' . __('Set Custom Fields:', 'malt-custom-filters') . '</strong> ' . __('Edit your properties and set the custom filter values (Type de lieu, Architecture, Décoration, Pièces) in the meta box.', 'malt-custom-filters') . '</li>' .
               '</ol>';
    }
    
    /**
     * Get help content - Usage
     */
    private function get_help_content_usage() {
        return '<h3>' . __('How to Use the Plugin', 'malt-custom-filters') . '</h3>' .
               '<h4>' . __('1. Adding Filters to a Page', 'malt-custom-filters') . '</h4>' .
               '<p>' . __('Use the shortcode <code>[malt_filters]</code> anywhere on your site where you want the filter interface to appear. This is typically on your property search page.', 'malt-custom-filters') . '</p>' .
               '<p><strong>' . __('Example:', 'malt-custom-filters') . '</strong></p>' .
               '<pre><code>[malt_filters]</code></pre>' .
               '<p>' . __('You can also add a custom CSS class:', 'malt-custom-filters') . '</p>' .
               '<pre><code>[malt_filters class="my-custom-class"]</code></pre>' .
               
               '<h4>' . __('2. Setting Property Filter Values', 'malt-custom-filters') . '</h4>' .
               '<p>' . __('When editing a property in WordPress admin, you will see a "Malt Custom Filters" meta box with the following fields:', 'malt-custom-filters') . '</p>' .
               '<ul>' .
               '<li><strong>Type de lieu:</strong> ' . __('Enter the property type (e.g., "Habitations > Appartement > Appartement moderne")', 'malt-custom-filters') . '</li>' .
               '<li><strong>Architecture:</strong> ' . __('Select from: Classique, Moderne, Industrielle, or Futuriste', 'malt-custom-filters') . '</li>' .
               '<li><strong>Décoration:</strong> ' . __('Select from: Ethnique, Minimaliste, Design, or Contemporain', 'malt-custom-filters') . '</li>' .
               '<li><strong>Pièces & éléments:</strong> ' . __('Enter the room or element (e.g., "Bureau" or "Open space")', 'malt-custom-filters') . '</li>' .
               '</ul>' .
               
               '<h4>' . __('3. How Search Works', 'malt-custom-filters') . '</h4>' .
               '<p>' . __('When a user selects filter values and clicks the search button:', 'malt-custom-filters') . '</p>' .
               '<ol>' .
               '<li>' . __('The selected values are passed as URL parameters (GET) to the search results page.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('The plugin hooks into Houzez search using the <code>houzez_search_args</code> filter.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Meta queries are added to filter properties based on the selected values.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Only properties matching ALL selected filters are displayed.', 'malt-custom-filters') . '</li>' .
               '</ol>';
    }
    
    /**
     * Get help content - Filters
     */
    private function get_help_content_filters() {
        return '<h3>' . __('Understanding the Filter Types', 'malt-custom-filters') . '</h3>' .
               
               '<h4>' . __('1. Type de lieu (Location Type)', 'malt-custom-filters') . '</h4>' .
               '<p>' . __('This is a hierarchical mega panel filter with 2-3 levels:', 'malt-custom-filters') . '</p>' .
               '<ul>' .
               '<li><strong>Level 1:</strong> ' . __('Root categories (Habitations, Bureaux, Industrie, Commerce, Hôtellerie, Autre)', 'malt-custom-filters') . '</li>' .
               '<li><strong>Level 2:</strong> ' . __('Sub-categories (e.g., Habitations → Appartement, Maison, Chalet)', 'malt-custom-filters') . '</li>' .
               '<li><strong>Level 3:</strong> ' . __('Specific types (e.g., Appartement → Appartement moderne, Studio, Loft)', 'malt-custom-filters') . '</li>' .
               '</ul>' .
               '<p>' . __('Users can navigate through levels by clicking items. Only one item can be selected at a time.', 'malt-custom-filters') . '</p>' .
               
               '<h4>' . __('2. Architecture', 'malt-custom-filters') . '</h4>' .
               '<p>' . __('Simple dropdown filter with four options:', 'malt-custom-filters') . '</p>' .
               '<ul>' .
               '<li>Classique</li>' .
               '<li>Moderne</li>' .
               '<li>Industrielle</li>' .
               '<li>Futuriste</li>' .
               '</ul>' .
               
               '<h4>' . __('3. Décoration', 'malt-custom-filters') . '</h4>' .
               '<p>' . __('Simple dropdown filter with four options:', 'malt-custom-filters') . '</p>' .
               '<ul>' .
               '<li>Ethnique</li>' .
               '<li>Minimaliste</li>' .
               '<li>Design</li>' .
               '<li>Contemporain</li>' .
               '</ul>' .
               
               '<h4>' . __('4. Pièces & éléments (Rooms & Elements)', 'malt-custom-filters') . '</h4>' .
               '<p>' . __('Horizontal mega panel with two main sections:', 'malt-custom-filters') . '</p>' .
               '<ul>' .
               '<li><strong>Pièces intérieures:</strong> ' . __('30+ interior room options in a 3-column grid. Some items have sub-menus (e.g., Bureau → Bureau, Open space).', 'malt-custom-filters') . '</li>' .
               '<li><strong>Éléments extérieurs:</strong> ' . __('Exterior elements like Terrasse, Jardin, Piscine, etc.', 'malt-custom-filters') . '</li>' .
               '</ul>' .
               '<p>' . __('Each section displays with an image header. Items with sub-options show an arrow indicator.', 'malt-custom-filters') . '</p>';
    }
    
    /**
     * Get help content - Customization
     */
    private function get_help_content_customization() {
        return '<h3>' . __('Customizing the Plugin', 'malt-custom-filters') . '</h3>' .
               
               '<h4>' . __('1. Button Customization', 'malt-custom-filters') . '</h4>' .
               '<p>' . __('In Settings → Malt Filters, you can customize:', 'malt-custom-filters') . '</p>' .
               '<ul>' .
               '<li>' . __('Button text (default: "Rechercher")', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Button background color', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Button hover color', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Button text color', 'malt-custom-filters') . '</li>' .
               '</ul>' .
               
               '<h4>' . __('2. Display Settings', 'malt-custom-filters') . '</h4>' .
               '<ul>' .
               '<li><strong>Filters Per Row:</strong> ' . __('Control how many filters appear per row on desktop (1-6).', 'malt-custom-filters') . '</li>' .
               '<li><strong>Panel Max Height:</strong> ' . __('Set the maximum height of filter panels (300-1200px).', 'malt-custom-filters') . '</li>' .
               '</ul>' .
               
               '<h4>' . __('3. CSS Customization', 'malt-custom-filters') . '</h4>' .
               '<p>' . __('You can override styles by adding custom CSS to your theme. All filter elements use prefixed classes:', 'malt-custom-filters') . '</p>' .
               '<ul>' .
               '<li><code>.malt-filters-wrapper</code> - Main container</li>' .
               '<li><code>.malt-filter-item</code> - Individual filter</li>' .
               '<li><code>.malt-panel</code> - Filter panels</li>' .
               '<li><code>.malt-search-btn</code> - Search button</li>' .
               '</ul>' .
               
               '<h4>' . __('4. Modifying Filter Options', 'malt-custom-filters') . '</h4>' .
               '<p>' . __('To modify the available filter options, edit the <code>filtersData</code> object in:', 'malt-custom-filters') . '</p>' .
               '<pre><code>/assets/js/custom-filters.js</code></pre>' .
               '<p>' . __('The structure is JSON-like and easy to modify. Add or remove items as needed.', 'malt-custom-filters') . '</p>';
    }
    
    /**
     * Get help content - Troubleshooting
     */
    private function get_help_content_troubleshooting() {
        return '<h3>' . __('Troubleshooting Common Issues', 'malt-custom-filters') . '</h3>' .
               
               '<h4>' . __('Filters Not Appearing', 'malt-custom-filters') . '</h4>' .
               '<ul>' .
               '<li>' . __('Ensure the shortcode <code>[malt_filters]</code> is added to your page.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Check that the plugin is activated in Plugins → Installed Plugins.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Clear your browser cache and WordPress cache if using a caching plugin.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Check browser console for JavaScript errors (F12 → Console tab).', 'malt-custom-filters') . '</li>' .
               '</ul>' .
               
               '<h4>' . __('Search Not Working', 'malt-custom-filters') . '</h4>' .
               '<ul>' .
               '<li>' . __('Verify the Search Results Page setting in Settings → Malt Filters.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Ensure properties have the custom field values set (Type de lieu, Architecture, etc.).', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Check that Houzez theme is active and properly configured.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Test with a property that has all filter values set to ensure the query works.', 'malt-custom-filters') . '</li>' .
               '</ul>' .
               
               '<h4>' . __('Styling Issues', 'malt-custom-filters') . '</h4>' .
               '<ul>' .
               '<li>' . __('Check for CSS conflicts with your theme. Use browser inspector to identify conflicting styles.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Ensure custom CSS is added with sufficient specificity or use <code>!important</code> if needed.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Verify that the plugin CSS file is loading (check Network tab in browser dev tools).', 'malt-custom-filters') . '</li>' .
               '</ul>' .
               
               '<h4>' . __('Panels Not Closing', 'malt-custom-filters') . '</h4>' .
               '<ul>' .
               '<li>' . __('Click outside the panel area to close it.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Check for JavaScript errors in browser console.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Ensure jQuery is loaded (required dependency).', 'malt-custom-filters') . '</li>' .
               '</ul>' .
               
               '<h4>' . __('Getting Support', 'malt-custom-filters') . '</h4>' .
               '<p>' . __('If you continue to experience issues:', 'malt-custom-filters') . '</p>' .
               '<ul>' .
               '<li>' . __('Check WordPress and PHP error logs.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Deactivate other plugins temporarily to check for conflicts.', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Ensure you are using a compatible version of WordPress (5.0+) and PHP (7.2+).', 'malt-custom-filters') . '</li>' .
               '<li>' . __('Contact support with details about your issue, including error messages and steps to reproduce.', 'malt-custom-filters') . '</li>' .
               '</ul>';
    }
    
    /**
     * Get help sidebar
     */
    private function get_help_sidebar() {
        return '<p><strong>' . __('For more information:', 'malt-custom-filters') . '</strong></p>' .
               '<p><a href="https://wordpress.org/support/" target="_blank">' . __('WordPress Support', 'malt-custom-filters') . '</a></p>' .
               '<p><a href="https://houzez.favethemes.com/" target="_blank">' . __('Houzez Theme', 'malt-custom-filters') . '</a></p>';
    }
}

