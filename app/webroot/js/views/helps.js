define([
  'jquery',
  'underscore',
  'backbone'
], function($, _, Backbone) {

  "use strict";

  var HelpsView = Backbone.View.extend({

    isHelpShown: false,

    tpl: _.template('<div class="shp-tooltip"><a href="<%= webroot %>help/<%= id %>" class="shp-tooltip-link"><i class="fa fa-question-circle"></i></a></div>'),

    events: function() {
      var out = {};
      out["click " + this.indicatorName] = "toggle";
      return out;
    },

    initialize: function(options) {
      this.indicatorName = options.indicatorName;
      this.elementName = options.elementName;
      this.webroot = options.webroot;

      this.activateHelpButton();
    },

    activateHelpButton: function() {
      if (this.isHelpOnPage()) {
        $(this.indicatorName).addClass('is-active');
      }
    },

    isHelpOnPage: function() {
      return this.$(this.elementName).length > 0;
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
        var that = this;
        $(this.elementName).each(function() {
          var $element = $(this),
              id = $element.data('shpid'),
              offset = $element.position();
          var $k = $(that.tpl({id: id, webroot: that.webroot}));
          $element.after($k);
          $k.css({
            left: offset.left + $element.width()/2 - 14,
            top: offset.top - $element.height()/2 - 23
          });
        });

      }
    },

    hide: function() {
      this.isHelpShown = false;
      $('.shp-tooltip').remove();
    }
  });

  return HelpsView;

});
