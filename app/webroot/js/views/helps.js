define([
  'jquery',
  'underscore',
  'backbone',
  'models/app',
  'drop'
], function($, _, Backbone, App, Drop) {

  "use strict";

  var HelpsView = Backbone.View.extend({

    isHelpShown: false,

    // cache for indicator-Views
    _popups: [],
    // cache for DOM-elements
    _elements: null,

    // target="_blank": don't lose text in ajax answering form by jumping away
    tpl: _.template('<a href="<%= webroot %>help/<%= id %>" target="_blank"><i class="fa fa-question-circle"></i></a>'),

    events: function() {
      var out = {};
      out["click " + this.indicatorName] = "toggle";
      return out;
    },

    initialize: function(options) {
      this.indicatorName = options.indicatorName;
      this.elementName = options.elementName;
      this.webroot = options.webroot;

      // @todo should listen to initial 'app view ready' event, decouple from views/app.js
      this.activateHelpButton();
      this.listenTo(App.eventBus, 'change:DOM', this._onDomChange);
    },

    activateHelpButton: function() {
      var $indicator = $(this.indicatorName);
      if (!$indicator) {
        return;
      }
      if (this._isHelpOnPage()) {
        $indicator.addClass('is-active');
      } else {
        $indicator.removeClass('is-active');
      }
    },

    _onDomChange: function() {
      this.activateHelpButton();
      this._reset();
    },

    _isHelpOnPage: function() {
      return this._getElements().length > 0;
    },

    toggle: function(event) {
      event.preventDefault();
      if (this.isHelpShown) {
        this._hide();
      } else {
        this._show();
      }
    },

    _reset: function() {
      this._hide();
      this._elements = null;
      this._popups = [];
    },

    _getElements: function() {
      if (this._elements === null) {
        this._elements = this.$(this.elementName).filter(':visible');
      }
      return this._elements;
    },

    _show: function() {
      this.isHelpShown = true;
      if (!this._isHelpOnPage()) {
        return;
      }

      if (this._popups.length === 0) {
        var that = this;
        this._getElements().each(function() {
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
    },

    _hide: function() {
      this.isHelpShown = false;
      this._popups.forEach(function(element) {
        element.close();
      });
    }
  });

  return HelpsView;

});
