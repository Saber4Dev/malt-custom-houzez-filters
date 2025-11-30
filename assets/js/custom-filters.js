/**
 * Malt Custom Filters - JavaScript
 * 
 * Handles multi-level filtering interface for Houzez theme
 */

(function($) {
    'use strict';
    
    var MaltFilters = {
        
        // Filter data structures
        filtersData: {
            type_lieu: {
                label: 'Type de lieu',
                items: [
                    {
                        name: 'Habitations',
                        children: [
                            {
                                name: 'Appartement',
                                children: [
                                    { name: 'Appartement moderne' },
                                    { name: 'Appartement classique' },
                                    { name: 'Studio' },
                                    { name: 'Loft' }
                                ]
                            },
                            {
                                name: 'Maison',
                                children: [
                                    { name: 'Maison individuelle' },
                                    { name: 'Villa' },
                                    { name: 'Maison de ville' }
                                ]
                            },
                            { name: 'Chalet' },
                            { name: 'Château' }
                        ]
                    },
                    {
                        name: 'Bureaux',
                        children: [
                            { name: 'Bureau individuel' },
                            { name: 'Open space' },
                            { name: 'Espace coworking' },
                            { name: 'Siège social' }
                        ]
                    },
                    {
                        name: 'Industrie',
                        children: [
                            { name: 'Entrepôt' },
                            { name: 'Usine' },
                            { name: 'Atelier' }
                        ]
                    },
                    {
                        name: 'Commerce',
                        children: [
                            { name: 'Boutique' },
                            { name: 'Restaurant' },
                            { name: 'Café' },
                            { name: 'Showroom' }
                        ]
                    },
                    {
                        name: 'Hôtellerie',
                        children: [
                            { name: 'Hôtel' },
                            { name: 'Auberge' },
                            { name: 'Gîte' }
                        ]
                    },
                    { name: 'Autre' }
                ]
            },
            
            architecture: {
                label: 'Architecture',
                items: [
                    { name: 'Classique' },
                    { name: 'Moderne' },
                    { name: 'Industrielle' },
                    { name: 'Futuriste' }
                ]
            },
            
            decoration: {
                label: 'Décoration',
                items: [
                    { name: 'Ethnique' },
                    { name: 'Minimaliste' },
                    { name: 'Design' },
                    { name: 'Contemporain' }
                ]
            },
            
            pieces: {
                label: 'Pièces & éléments',
                sections: [
                    {
                        name: 'Pièces intérieures',
                        image: 'interieur.jpg', // Placeholder - should be replaced with actual image URL
                        items: [
                            { name: 'Salon' },
                            { name: 'Cuisine' },
                            { name: 'Chambre' },
                            { name: 'Salle de bain' },
                            { name: 'Bureau', children: [
                                { name: 'Bureau' },
                                { name: 'Open space' }
                            ]},
                            { name: 'Salle à manger' },
                            { name: 'Bibliothèque' },
                            { name: 'Cave à vin' },
                            { name: 'Home cinéma' },
                            { name: 'Gym' },
                            { name: 'Spa' },
                            { name: 'Dressing' },
                            { name: 'Buanderie' },
                            { name: 'Cellier' },
                            { name: 'Garage' },
                            { name: 'Atelier' },
                            { name: 'Salle de jeux' },
                            { name: 'Bureau à domicile' },
                            { name: 'Salle de réunion' },
                            { name: 'Hall d\'entrée' },
                            { name: 'Couloir' },
                            { name: 'Débarras' },
                            { name: 'Chambre d\'amis' },
                            { name: 'Suite parentale' },
                            { name: 'Nursery' },
                            { name: 'Chambre d\'enfant' },
                            { name: 'Salle de bain d\'appoint' },
                            { name: 'WC' },
                            { name: 'Mezzanine' },
                            { name: 'Grenier' }
                        ]
                    },
                    {
                        name: 'Éléments extérieurs',
                        image: 'exterieur.jpg', // Placeholder - should be replaced with actual image URL
                        items: [
                            { name: 'Terrasse' },
                            { name: 'Jardin' },
                            { name: 'Piscine' },
                            { name: 'Balcon' },
                            { name: 'Cour' },
                            { name: 'Parking' },
                            { name: 'Garage extérieur' },
                            { name: 'Abri de jardin' },
                            { name: 'Serre' },
                            { name: 'Potager' },
                            { name: 'Verger' },
                            { name: 'Allée' },
                            { name: 'Portail' },
                            { name: 'Clôture' }
                        ]
                    }
                ]
            }
        },
        
        currentLevel: {},
        activeFilter: null,
        activePanel: null,
        
        /**
         * Initialize filters
         */
        init: function() {
            this.buildPanels();
            this.bindEvents();
            this.loadFromURL();
        },
        
        /**
         * Build all filter panels
         */
        buildPanels: function() {
            var self = this;
            var $panelsContainer = $('.malt-filters-panels');
            
            // Build Type de lieu panel
            this.buildTypeLieuPanel($panelsContainer);
            
            // Build Architecture panel
            this.buildSimplePanel($panelsContainer, 'architecture');
            
            // Build Décoration panel
            this.buildSimplePanel($panelsContainer, 'decoration');
            
            // Build Pièces panel
            this.buildPiecesPanel($panelsContainer);
        },
        
        /**
         * Build Type de lieu mega panel
         */
        buildTypeLieuPanel: function($container) {
            var $panel = $('<div class="malt-panel malt-panel-type-lieu" data-filter="type_lieu"></div>');
            var $level1 = this.buildLevel(this.filtersData.type_lieu.items, 1, 'type_lieu');
            $panel.append($level1);
            $container.append($panel);
        },
        
        /**
         * Build simple dropdown panel
         */
        buildSimplePanel: function($container, filterKey) {
            var $panel = $('<div class="malt-panel malt-panel-simple" data-filter="' + filterKey + '"></div>');
            var $list = $('<ul class="malt-simple-list"></ul>');
            
            var self = this;
            this.filtersData[filterKey].items.forEach(function(item) {
                var $li = $('<li class="malt-item" data-value="' + self.escapeHtml(item.name) + '">' + self.escapeHtml(item.name) + '</li>');
                $list.append($li);
            });
            
            $panel.append($list);
            $container.append($panel);
        },
        
        /**
         * Build Pièces & éléments mega panel
         */
        buildPiecesPanel: function($container) {
            var $panel = $('<div class="malt-panel malt-panel-pieces" data-filter="pieces"></div>');
            var self = this;
            
            this.filtersData.pieces.sections.forEach(function(section) {
                var $section = $('<div class="malt-pieces-section"></div>');
                
                // Create collapsible header with circular image and expand button
                var $header = $('<div class="malt-pieces-header malt-pieces-toggle">' + 
                    '<div class="malt-pieces-image-wrapper">' +
                    '<img src="' + (maltFilters.pluginUrl || '') + '/assets/images/' + section.image + '" alt="' + section.name + '" class="malt-pieces-image" />' +
                    '</div>' +
                    '<h3 class="malt-pieces-title">' + self.escapeHtml(section.name) + '</h3>' +
                    '<button type="button" class="malt-pieces-expand-btn" aria-label="Expand">' +
                    '<span class="malt-pieces-arrow">▼</span>' +
                    '</button>' +
                    '</div>');
                $section.append($header);
                
                // Create collapsible content (hidden by default)
                var $content = $('<div class="malt-pieces-content" style="display: none;"></div>');
                var $grid = $('<div class="malt-pieces-grid"></div>');
                
                section.items.forEach(function(item) {
                    var $item = $('<label class="malt-pieces-item">' +
                        '<input type="checkbox" class="malt-pieces-checkbox" data-value="' + self.escapeHtml(item.name) + '"' +
                        (item.children ? ' data-has-children="true"' : '') + ' />' +
                        '<span class="malt-pieces-label">' + self.escapeHtml(item.name) + '</span>' +
                        '</label>');
                    $grid.append($item);
                });
                
                $content.append($grid);
                $section.append($content);
                $panel.append($section);
            });
            
            $container.append($panel);
        },
        
        /**
         * Build hierarchical level
         */
        buildLevel: function(items, level, filterKey) {
            var self = this;
            var $level = $('<div class="malt-level malt-level-' + level + '" data-level="' + level + '"></div>');
            var $list = $('<ul class="malt-level-list"></ul>');
            
            items.forEach(function(item) {
                var $li = $('<li class="malt-level-item"' + 
                    (item.children ? ' data-has-children="true"' : '') +
                    ' data-value="' + self.escapeHtml(item.name) + '">' +
                    '<span class="malt-item-name">' + self.escapeHtml(item.name) + '</span>' +
                    (item.children ? '<span class="malt-item-arrow">›</span>' : '') +
                    '</li>');
                $list.append($li);
            });
            
            $level.append($list);
            return $level;
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;
            
            // Handle image load errors
            $(document).on('error', '.malt-pieces-image', function() {
                $(this).hide();
            });
            
            // Filter trigger clicks
            $(document).on('click', '.malt-filter-trigger', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $filterItem = $(this).closest('.malt-filter-item');
                var filterKey = $filterItem.data('filter');
                
                self.togglePanel(filterKey);
            });
            
            // Type de lieu level navigation
            $(document).on('click', '.malt-panel-type-lieu .malt-level-item', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $item = $(this);
                var value = $item.data('value');
                var hasChildren = $item.data('has-children');
                
                if (hasChildren) {
                    self.navigateToLevel('type_lieu', value, $item);
                } else {
                    self.selectValue('type_lieu', value);
                }
            });
            
            // Simple panel item clicks
            $(document).on('click', '.malt-panel-simple .malt-item', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var filterKey = $(this).closest('.malt-panel').data('filter');
                var value = $(this).data('value');
                
                self.selectValue(filterKey, value);
            });
            
            // Pièces section header toggle (collapse/expand)
            $(document).on('click', '.malt-pieces-toggle', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Don't trigger if clicking directly on the button (it has its own handler)
                if ($(e.target).closest('.malt-pieces-expand-btn').length) {
                    return;
                }
                
                var $header = $(this);
                var $content = $header.next('.malt-pieces-content');
                var $arrow = $header.find('.malt-pieces-arrow');
                var $btn = $header.find('.malt-pieces-expand-btn');
                
                $content.slideToggle(300);
                $arrow.toggleClass('expanded');
                $header.toggleClass('expanded');
                $btn.toggleClass('expanded');
            });
            
            // Separate handler for expand button
            $(document).on('click', '.malt-pieces-expand-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $btn = $(this);
                var $header = $btn.closest('.malt-pieces-header');
                var $content = $header.next('.malt-pieces-content');
                var $arrow = $header.find('.malt-pieces-arrow');
                
                $content.slideToggle(300);
                $arrow.toggleClass('expanded');
                $header.toggleClass('expanded');
                $btn.toggleClass('expanded');
            });
            
            // Pièces checkbox clicks (multiple selection)
            $(document).on('change', '.malt-pieces-checkbox', function(e) {
                e.stopPropagation();
                
                var $checkbox = $(this);
                var value = $checkbox.data('value');
                var isChecked = $checkbox.is(':checked');
                var hasChildren = $checkbox.data('has-children');
                
                if (hasChildren && isChecked) {
                    // Show submenu for items with children (only when checked)
                    self.showPiecesSubmenu($checkbox.closest('.malt-pieces-item'), value, isChecked);
                } else if (!hasChildren) {
                    // Update selected values for items without children
                    self.updatePiecesSelection(value, isChecked);
                }
            });
            
            // Prevent label clicks from toggling checkbox twice
            $(document).on('click', '.malt-pieces-label', function(e) {
                e.stopPropagation();
            });
            
            // Click outside to close
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.malt-filter-item, .malt-panel').length) {
                    self.closeAllPanels();
                }
            });
            
            // Prevent panel clicks from closing
            $(document).on('click', '.malt-panel', function(e) {
                e.stopPropagation();
            });
        },
        
        /**
         * Toggle panel visibility
         */
        togglePanel: function(filterKey) {
            if (this.activeFilter === filterKey) {
                this.closeAllPanels();
            } else {
                this.closeAllPanels();
                this.openPanel(filterKey);
            }
        },
        
        /**
         * Open panel
         */
        openPanel: function(filterKey) {
            var $panel = $('.malt-panel[data-filter="' + filterKey + '"]');
            var $filterItem = $('.malt-filter-item[data-filter="' + filterKey + '"]');
            
            if ($panel.length) {
                this.activeFilter = filterKey;
                this.activePanel = $panel;
                
                $filterItem.addClass('active');
                $panel.addClass('active');
                
                // Reset to first level for hierarchical panels
                if (filterKey === 'type_lieu') {
                    this.resetTypeLieuPanel();
                }
            }
        },
        
        /**
         * Close all panels
         */
        closeAllPanels: function() {
            $('.malt-filter-item').removeClass('active');
            $('.malt-panel').removeClass('active');
            this.activeFilter = null;
            this.activePanel = null;
        },
        
        /**
         * Reset Type de lieu panel to first level
         */
        resetTypeLieuPanel: function() {
            var $panel = $('.malt-panel-type-lieu');
            $panel.find('.malt-level').removeClass('active');
            $panel.find('.malt-level-1').addClass('active');
        },
        
        /**
         * Navigate to next level in Type de lieu
         */
        navigateToLevel: function(filterKey, parentValue, $parentItem) {
            var $panel = $('.malt-panel[data-filter="' + filterKey + '"]');
            var currentLevel = $panel.find('.malt-level.active').data('level') || 1;
            var nextLevel = currentLevel + 1;
            
            // Find parent item's children
            var parentItem = this.findItemByValue(this.filtersData.type_lieu.items, parentValue);
            if (!parentItem || !parentItem.children) {
                return;
            }
            
            // Check if level already exists
            var $existingLevel = $panel.find('.malt-level-' + nextLevel + '[data-parent="' + this.escapeHtml(parentValue) + '"]');
            
            if ($existingLevel.length) {
                // Show existing level
                $panel.find('.malt-level').removeClass('active');
                $existingLevel.addClass('active');
            } else {
                // Build and add new level
                var $newLevel = this.buildLevel(parentItem.children, nextLevel, filterKey);
                $newLevel.attr('data-parent', this.escapeHtml(parentValue));
                $panel.append($newLevel);
                $panel.find('.malt-level').removeClass('active');
                $newLevel.addClass('active');
            }
        },
        
        /**
         * Find item by value in nested structure
         */
        findItemByValue: function(items, value) {
            for (var i = 0; i < items.length; i++) {
                if (items[i].name === value) {
                    return items[i];
                }
                if (items[i].children) {
                    var found = this.findItemByValue(items[i].children, value);
                    if (found) return found;
                }
            }
            return null;
        },
        
        /**
         * Show pieces submenu
         */
        showPiecesSubmenu: function($item, value, isChecked) {
            // Find the item in data structure
            var subItems = null;
            var self = this;
            
            this.filtersData.pieces.sections.forEach(function(section) {
                section.items.forEach(function(item) {
                    if (item.name === value && item.children) {
                        subItems = item.children;
                    }
                });
            });
            
            if (!subItems) return;
            
            // Remove existing submenu if any
            $('.malt-pieces-submenu').remove();
            
            // Create submenu with checkboxes
            var $submenu = $('<div class="malt-pieces-submenu"></div>');
            subItems.forEach(function(subItem) {
                var $subItem = $('<label class="malt-pieces-subitem">' +
                    '<input type="checkbox" class="malt-pieces-subcheckbox" data-value="' + self.escapeHtml(subItem.name) + '" />' +
                    '<span>' + self.escapeHtml(subItem.name) + '</span>' +
                    '</label>');
                $submenu.append($subItem);
            });
            
            // Position and show submenu
            var offset = $item.offset();
            $submenu.css({
                position: 'absolute',
                top: offset.top + $item.outerHeight(),
                left: offset.left
            });
            
            $('body').append($submenu);
            
            // Handle submenu checkbox change
            $submenu.find('.malt-pieces-subcheckbox').on('change', function() {
                var subValue = $(this).data('value');
                var subChecked = $(this).is(':checked');
                self.updatePiecesSelection(subValue, subChecked);
            });
            
            // Close submenu on outside click
            setTimeout(function() {
                $(document).one('click', function(e) {
                    if (!$(e.target).closest('.malt-pieces-submenu').length) {
                        $submenu.remove();
                    }
                });
            }, 100);
        },
        
        /**
         * Update pieces selection (multiple values)
         */
        updatePiecesSelection: function(value, isChecked) {
            var $hiddenInput = $('#malt-pieces');
            var currentValues = $hiddenInput.val() ? $hiddenInput.val().split(',') : [];
            
            if (isChecked) {
                // Add value if not already present
                if (currentValues.indexOf(value) === -1) {
                    currentValues.push(value);
                }
            } else {
                // Remove value
                var index = currentValues.indexOf(value);
                if (index > -1) {
                    currentValues.splice(index, 1);
                }
            }
            
            // Update hidden input
            $hiddenInput.val(currentValues.join(','));
            
            // Update display label
            var $filterItem = $('.malt-filter-item[data-filter="pieces"]');
            var $valueDisplay = $filterItem.find('.malt-filter-value');
            
            if (currentValues.length > 0) {
                if (currentValues.length === 1) {
                    $valueDisplay.text(currentValues[0]);
                } else {
                    $valueDisplay.text(currentValues.length + ' ' + (currentValues.length === 1 ? 'item' : 'items'));
                }
                $filterItem.addClass('has-value');
            } else {
                $valueDisplay.text('');
                $filterItem.removeClass('has-value');
            }
            
            // Auto-submit if enabled
            if (maltFilters.autoSubmit && currentValues.length > 0) {
                this.submitForm();
            }
        },
        
        /**
         * Select a value
         */
        selectValue: function(filterKey, value) {
            // Update hidden input
            $('#malt-' + filterKey.replace('_', '-')).val(value);
            
            // Update display label
            var $filterItem = $('.malt-filter-item[data-filter="' + filterKey + '"]');
            $filterItem.find('.malt-filter-value').text(value);
            $filterItem.addClass('has-value');
            
            // Close panel
            this.closeAllPanels();
            
            // Auto-submit if enabled
            if (maltFilters.autoSubmit) {
                this.submitForm();
            }
        },
        
        /**
         * Submit the form
         */
        submitForm: function() {
            var self = this;
            var $form = $('#malt-filters-form');
            var piecesValue = $('#malt-pieces').val();
            
            // Convert pieces comma-separated string to array inputs
            if (piecesValue) {
                // Remove existing pieces[] inputs
                $form.find('input[name="pieces[]"]').remove();
                
                // Add new inputs for each piece value
                var piecesArray = piecesValue.split(',');
                piecesArray.forEach(function(piece) {
                    piece = piece.trim();
                    if (piece) {
                        $form.append('<input type="hidden" name="pieces[]" value="' + self.escapeHtml(piece) + '" />');
                    }
                });
            }
            
            $form.submit();
        },
        
        /**
         * Load values from URL parameters
         */
        loadFromURL: function() {
            var self = this;
            var params = new URLSearchParams(window.location.search);
            
            // Load top row fields
            var typeProjet = params.get('type_projet');
            if (typeProjet) {
                $('#malt-type-projet').val(typeProjet);
            }
            
            var reference = params.get('reference');
            if (reference) {
                $('#malt-reference').val(decodeURIComponent(reference));
            }
            
            var location = params.get('location');
            if (location) {
                $('#malt-location').val(decodeURIComponent(location));
            }
            
            // Load filter fields
            ['type_lieu', 'architecture', 'decoration'].forEach(function(key) {
                var value = params.get(key);
                if (value) {
                    $('#malt-' + key.replace('_', '-')).val(value);
                    var $filterItem = $('.malt-filter-item[data-filter="' + key + '"]');
                    $filterItem.find('.malt-filter-value').text(decodeURIComponent(value));
                    $filterItem.addClass('has-value');
                }
            });
            
            // Handle pieces (multiple values)
            var piecesValues = params.getAll('pieces');
            if (piecesValues.length > 0) {
                var piecesString = piecesValues.join(',');
                $('#malt-pieces').val(piecesString);
                var $filterItem = $('.malt-filter-item[data-filter="pieces"]');
                var $valueDisplay = $filterItem.find('.malt-filter-value');
                
                if (piecesValues.length === 1) {
                    $valueDisplay.text(decodeURIComponent(piecesValues[0]));
                } else {
                    $valueDisplay.text(piecesValues.length + ' items');
                }
                $filterItem.addClass('has-value');
                
                // Check the checkboxes
                piecesValues.forEach(function(value) {
                    $('.malt-pieces-checkbox[data-value="' + self.escapeHtml(decodeURIComponent(value)) + '"]').prop('checked', true);
                });
            }
        },
        
        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        MaltFilters.init();
    });
    
})(jQuery);

