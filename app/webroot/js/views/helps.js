define([
  'jquery',
  'underscore',
  'backbone',
  'drop'
], function($, _, Backbone, Drop) {

  "use strict";

  var HelpsView = Backbone.View.extend({

    isHelpShown: false,

    _popups: [],

    tpl: _.template('<a href="<%= webroot %>help/<%= id %>"><i class="fa fa-question-circle"></i></a>'),

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
        if (this._popups.length === 0) {
          var that = this;
          $(this.elementName).each(function() {
            var $element = $(this),
                id = $element.data('shpid'),
                $k = that.tpl({id: id, webroot: that.webroot});
            that._popups.push(new Drop({
              target: this,
              content: $k,
              classes: 'drop-theme-arrows',
              position: 'top center'
            }));
          });
        }
        this._popups.forEach(function(element) {
          element.open();
        });
      }
    },

    hide: function() {
      this.isHelpShown = false;
      this._popups.forEach(function(element) {
        element.close();
      });
    }
  });

  return HelpsView;

});
