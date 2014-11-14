define([
  'jquery',
  'marionette',
  'lib/leaflet/mq',
  'text!modules/usermap/templates/controls.html'
], function($, Marionette, MQ, Tpl) {

  'use strict';

  // MapControlsView
  return Marionette.ItemView.extend({

    template: _.template(Tpl),

    ui: {
      search: '.js-btn-search',
      locate: '.js-btn-locate',
      clear: '.js-btn-clear',
      spinner: '.saito-usermap-spinner'
    },

    events: {
      // handles clear button
      'click @ui.search': '_search',
      'click @ui.locate': '_locate',
      'click @ui.clear': '_clear'
    },

    initialize: function(options) {
      this.mapLayer = options.mapLayer;
      this.mapModel = options.mapModel;
      this.params = options.params;

      // handles click on map
      this.mapLayer.on('click', this._repin, this);
      // handles locate
      this.mapLayer.on('locationfound', this._repin, this);
      // handles input field
      $(this.params.fields.edit).on('keypress', _.bind(this._onSearchFieldChange, this));
      $(this.params.fields.edit).on('keyup', _.bind(this._updateSearchButtonTitle, this));
    },

    _clear: function(e) {
      e.preventDefault();
      this.model.set({lat: '', lng: '', zoom: ''});
      this._updateFields();
    },

    _search: function(e) {
      e.preventDefault();
      this._activityStart();
      var searchTerm = $(this.params.fields.edit).val();
      MQ.geocode().search(searchTerm)
          .on('success', _.bind(function(e) {
            var best = e.result.best;
            this._repin(best);
          }, this));
    },

    _onSearchFieldChange: function(e) {
      if (e.which === 13) {
        this._search(e);
      }
    },

    _activityStart: function() {
      this.ui.spinner.show();
    },

    _activityStop: function() {
      this.ui.spinner.hide();
    },

    _updateSearchButtonTitle: function() {
      var string = $(this.params.fields.edit).val();
      if (!string) {
        this.ui.search.attr('disabled', 'disabled');
        return;
      }
      this.ui.search.removeAttr('disabled');
      this.ui.search.html($.i18n.__('user.map.b.search', {string: string}));
    },

    _locate: function(e) {
      e.preventDefault();
      this._activityStart();
      this.mapLayer.locate();
    },

    _repin: function(e) {
      var view = this._round({
        lat: e.latlng.lat,
        lng: e.latlng.lng,
        zoom: this.mapLayer.getZoom()
      });
      this._activityStop();
      // update user model -> triggers pin to be set
      this.model.set(view);
      // move viewport to pin
      this.mapModel.set(view);

      this._updateFields();
    },

    _round: function(data) {
      _.each(['lat', 'lng'], function(type) {
        data[type] = data[type].toFixed(4);
      });
      return data;
    },

    onRender: function() {
      this._updateSearchButtonTitle();
    },

    _updateFields: function() {
      var fieldsToUpdate = this.params.fields.update,
          model = this.model;
      _.each(['lat', 'lng', 'zoom'], _.bind(function(type) {
        var cType = type;
        if (fieldsToUpdate && fieldsToUpdate[type]) {
          _.each(fieldsToUpdate[type], function(selector) {
            var $el = $(selector),
                value = model.get(cType);
            if ($el.prop('tagName').toLowerCase() === 'input') {
              $el.val(value);
            } else {
              $el.html(value);
            }
          });
        }
      }, this));
      this.render();
    },

    /**
     * Checks geolocation available
     *
     * @private
     */
    _geolocation: function() {
      return 'geolocation' in navigator;
    },

    serializeData: function() {
      var data = this.model.toJSON();
      data.geolocation = this._geolocation();
      return data;
    }
  });

});
