/**
 * Collapse Helper v3.3 - Collapsible sections with localStorage persistence
 * Handles collapsible sections and remembers their state across page loads
 */
(function ($) {
  "use strict";

  // Debug mode (set to false for production)
  let DEBUG = false;

  // localStorage key for collapse states
  const STORAGE_KEY = "company_settings_collapse_states";

  function debugLog(message, data) {
    if (DEBUG) {
      console.log("[CollapseHelper v3.3] " + message, data || "");
    }
  }

  function updateIconState($icon, isExpanded) {
    if ($icon.length) {
      $icon.attr("aria-expanded", isExpanded ? "true" : "false");

      if (isExpanded) {
        $icon.removeClass("fa-chevron-down").addClass("fa-chevron-up");
      } else {
        $icon.removeClass("fa-chevron-up").addClass("fa-chevron-down");
      }

      // Icon updated
    }
  }

  // localStorage functions
  function loadCollapseStates() {
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      const states = stored ? JSON.parse(stored) : {};
      // States loaded from localStorage
      return states;
    } catch (e) {
      debugLog("Error loading collapse states from localStorage", e);
      return {};
    }
  }

  function saveCollapseState(target, expanded) {
    try {
      const states = loadCollapseStates();
      states[target] = expanded;
      localStorage.setItem(STORAGE_KEY, JSON.stringify(states));
      // State saved to localStorage
    } catch (e) {
      debugLog("Error saving collapse state to localStorage", e);
    }
  }

  // Track collapse states in memory
  const collapseStates = new Map();

  function isExpanded(target) {
    return collapseStates.get(target) === true;
  }

  function setExpanded(target, expanded, saveToStorage = true) {
    collapseStates.set(target, expanded);

    if (saveToStorage) {
      saveCollapseState(target, expanded);
    }

    // State updated in memory
  }

  function toggleCollapse($target, $icon, willBeExpanded) {
    const target = "#" + $target.attr("id");

    if (willBeExpanded) {
      // Show animation
      $target.removeClass("collapse").addClass("collapsing");
      $target.css({
        height: "0px",
        overflow: "hidden",
      });

      // Force reflow
      $target[0].offsetHeight;

      // Get natural height
      $target.css("height", "auto");
      const height = $target[0].scrollHeight;
      $target.css("height", "0px");

      // Animate to full height
      $target.css({
        transition: "height 0.35s ease",
        height: height + "px",
      });

      setTimeout(function () {
        $target.removeClass("collapsing").addClass("collapse show");
        $target.css({
          height: "",
          overflow: "",
          transition: "",
        });
        // Show animation completed
      }, 350);
    } else {
      // Hide animation
      const height = $target[0].scrollHeight;
      $target.css({
        height: height + "px",
        transition: "height 0.35s ease",
        overflow: "hidden",
      });
      $target.removeClass("collapse show").addClass("collapsing");

      // Force reflow
      $target[0].offsetHeight;

      // Animate to zero height
      $target.css("height", "0px");

      setTimeout(function () {
        $target.removeClass("collapsing").addClass("collapse");
        $target.css({
          height: "",
          overflow: "",
          transition: "",
        });
        // Hide animation completed
      }, 350);
    }
  }

  function initCollapseHelper() {
    debugLog("Initializing collapse helper v3.3");

    // Convert Bootstrap collapse elements to custom collapse
    $('[data-toggle="collapse"]')
      .removeAttr("data-toggle")
      .attr("data-custom-collapse", "true");

    // Load saved states first
    const savedStates = loadCollapseStates();
    // Load saved states from localStorage

    // Initialize states from localStorage
    $('[data-custom-collapse="true"]').each(function () {
      const $trigger = $(this);
      const target = $trigger.attr("data-target");
      const $target = $(target);
      const $icon = $trigger.find(".collapse-icon");

      if ($target.length && $icon.length) {
        // Check if we have a saved state, otherwise use current DOM state
        let shouldBeExpanded;
        if (savedStates.hasOwnProperty(target)) {
          shouldBeExpanded = savedStates[target];
          debugLog("Using saved state for " + target, shouldBeExpanded);
        } else {
          shouldBeExpanded = $target.hasClass("show");
          debugLog("Using default DOM state for " + target, shouldBeExpanded);
        }

        // Apply the state to the DOM immediately
        if (shouldBeExpanded) {
          $target.addClass("collapse show").removeClass("collapsing");
        } else {
          $target.addClass("collapse").removeClass("show collapsing");
        }

        // Update our internal state (don't save to avoid overwriting)
        setExpanded(target, shouldBeExpanded, false);

        // Update icon state
        updateIconState($icon, shouldBeExpanded);

        // Section initialized
      }
    });

    // Handle click events with complete control
    $(document).on(
      "click.collapseHelper",
      '[data-custom-collapse="true"]',
      function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        const $trigger = $(this);
        const target = $trigger.attr("data-target");
        const $target = $(target);
        const $icon = $trigger.find(".collapse-icon");

        if (!$target.length || !$icon.length) {
          debugLog("Missing target or icon, skipping");
          return false;
        }

        // Get current state from our tracker
        const currentlyExpanded = isExpanded(target);
        const willBeExpanded = !currentlyExpanded;

        // Toggle state

        // Update state tracker and save to localStorage
        setExpanded(target, willBeExpanded, true);

        // Update icon immediately
        updateIconState($icon, willBeExpanded);

        // Perform the animation
        toggleCollapse($target, $icon, willBeExpanded);

        return false;
      }
    );

    debugLog(
      "Collapse helper v3.3 initialized - Bootstrap disabled, localStorage enabled"
    );
  }

  // Initialize when DOM is ready
  $(document).ready(function () {
    debugLog("DOM ready - starting collapse helper v3.3");
    initCollapseHelper();
  });

  // Export for debugging
  window.CollapseHelper = {
    init: initCollapseHelper,
    debug: function (enable) {
      DEBUG = enable;
      debugLog("Debug mode " + (enable ? "enabled" : "disabled"));
    },
    getStates: function () {
      return loadCollapseStates();
    },
    clearStates: function () {
      localStorage.removeItem(STORAGE_KEY);
      debugLog("Cleared all collapse states");
    },
  };
})(jQuery);
