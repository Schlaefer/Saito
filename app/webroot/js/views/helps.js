define([
  'jquery',
  'underscore',
  'backbone'
], function($, _, Backbone) {

  "use strict";

  var HelpsView = Backbone.View.extend({

    isHelpShown: false,

    events: function() {
      var out = {};
      out["click " + this.indicatorName] = "toggle";
      return out;
    },

    initialize: function(options) {
      return;
      this.indicatorName = options.indicatorName;
      this.elementName = options.elementName;

      this.activateHelpButton();
      this.placeHelp();
    },

    activateHelpButton: function() {
      if (this.isHelpOnPage()) {
        $(this.indicatorName).removeClass('no-color');
      }
    },

    placeHelp: function() {
      var defaults = {
        trigger: 'manual',
        html: true
      };
      var positions = ['bottom', 'right', 'left'];
      for (var i = 0; i < positions.length; i++) {
        $(this.elementName + '-' + positions[i]).popover(
            $.extend(defaults, {placement: positions[i]})
        );
      }

      $(this.indicatorName).popover({
        placement: 'left',
        trigger: 'manual'
      });
    },

    isHelpOnPage: function() {
      return this.$el.find(this.elementName).length > 0;
    },

    toggle: function(event) {
      event.preventDefault();

      if (this.isHelpShown) {
        this.hide();
      } else {
        this.show();
      }
    },


    show: function() {
      this.isHelpShown = true;
      if (this.isHelpOnPage()) {
        $(this.elementName).popover('show');
      } else {
        $(this.indicatorName).popover('show');
      }
    },

    hide: function() {
      this.isHelpShown = false;
      $(this.elementName).popover('hide');
      $(this.indicatorName).popover('hide');
    }
  });

  return HelpsView;

});
