/**
 * Collapse Helper v3.0 - Pure manual implementation
 * Completely replaces Bootstrap collapse functionality
 */
(function($) {
    'use strict';
    
    // Debug mode
    let DEBUG = true;
    
    function debugLog(message, data) {
        if (DEBUG) {
            console.log('[CollapseHelper v3.0] ' + message, data || '');
        }
    }
    
    function updateIconState($icon, isExpanded) {
        if ($icon.length) {
            $icon.attr('aria-expanded', isExpanded ? 'true' : 'false');
            
            if (isExpanded) {
                $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            } else {
                $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }
            
            debugLog('Icon updated', {
                'aria-expanded': $icon.attr('aria-expanded'),
                direction: isExpanded ? 'up' : 'down'
            });
        }
    }
    
    // Track collapse states
    const collapseStates = new Map();
    
    function isExpanded(target) {
        return collapseStates.get(target) === true;
    }
    
    function setExpanded(target, expanded) {
        collapseStates.set(target, expanded);
        debugLog('State updated', {
            target: target,
            expanded: expanded
        });
    }
    
    function toggleCollapse($target, $icon, willBeExpanded) {
        const target = '#' + $target.attr('id');
        
        if (willBeExpanded) {
            // Show animation
            $target.removeClass('collapse').addClass('collapsing');
            $target.css({
                'height': '0px',
                'overflow': 'hidden'
            });
            
            // Force reflow
            $target[0].offsetHeight;
            
            // Get natural height
            $target.css('height', 'auto');
            const height = $target[0].scrollHeight;
            $target.css('height', '0px');
            
            // Animate to full height
            $target.css({
                'transition': 'height 0.35s ease',
                'height': height + 'px'
            });
            
            setTimeout(function() {
                $target.removeClass('collapsing').addClass('collapse show');
                $target.css({
                    'height': '',
                    'overflow': '',
                    'transition': ''
                });
                setExpanded(target, true);
                debugLog('Show animation completed for ' + target);
            }, 350);
            
        } else {
            // Hide animation
            const height = $target[0].scrollHeight;
            $target.css({
                'height': height + 'px',
                'transition': 'height 0.35s ease',
                'overflow': 'hidden'
            });
            $target.removeClass('collapse show').addClass('collapsing');
            
            // Force reflow
            $target[0].offsetHeight;
            
            // Animate to zero height
            $target.css('height', '0px');
            
            setTimeout(function() {
                $target.removeClass('collapsing').addClass('collapse');
                $target.css({
                    'height': '',
                    'overflow': '',
                    'transition': ''
                });
                setExpanded(target, false);
                debugLog('Hide animation completed for ' + target);
            }, 350);
        }
    }
    
    function initCollapseHelper() {
        debugLog('Initializing collapse helper v3.0');
        
        // Remove ALL existing event listeners to prevent conflicts
        $(document).off('.collapseHelper');
        $(document).off('click', '[data-toggle="collapse"]');
        
        // Disable Bootstrap collapse initialization
        $('[data-toggle="collapse"]').removeAttr('data-toggle').attr('data-custom-collapse', 'true');
        
        // Handle click events with complete control
        $(document).on('click.collapseHelper', '[data-custom-collapse="true"]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            const $trigger = $(this);
            const target = $trigger.attr('data-target');
            const $target = $(target);
            const $icon = $trigger.find('.collapse-icon');
            
            if (!$target.length || !$icon.length) {
                debugLog('Missing target or icon, skipping');
                return false;
            }
            
            // Get current state from our tracker
            const currentlyExpanded = isExpanded(target);
            const willBeExpanded = !currentlyExpanded;
            
            debugLog('Pure manual toggle', {
                target: target,
                from: currentlyExpanded ? 'expanded' : 'collapsed',
                to: willBeExpanded ? 'expanded' : 'collapsed'
            });
            
            // Update icon immediately
            updateIconState($icon, willBeExpanded);
            
            // Update state tracker
            setExpanded(target, willBeExpanded);
            
            // Perform the animation
            toggleCollapse($target, $icon, willBeExpanded);
            
            return false;
        });
        
        // Initialize states
        setTimeout(function() {
            $('[data-custom-collapse="true"]').each(function() {
                const $trigger = $(this);
                const target = $trigger.attr('data-target');
                const $target = $(target);
                const $icon = $trigger.find('.collapse-icon');
                
                if ($target.length && $icon.length) {
                    const isCurrentlyExpanded = $target.hasClass('show');
                    setExpanded(target, isCurrentlyExpanded);
                    debugLog('Initialized ' + target + ' as ' + (isCurrentlyExpanded ? 'expanded' : 'collapsed'));
                    updateIconState($icon, isCurrentlyExpanded);
                }
            });
        }, 100);
        
        debugLog('Collapse helper v3.0 initialized - Bootstrap disabled');
    }
    
    // Initialize
    $(document).ready(function() {
        debugLog('DOM ready - starting collapse helper v3.0');
        initCollapseHelper();
    });
    
    // Export
    window.CollapseHelper = {
        init: initCollapseHelper,
        debug: function(enable) {
            DEBUG = enable;
            debugLog('Debug mode ' + (enable ? 'enabled' : 'disabled'));
        }
    };
    
})(jQuery);