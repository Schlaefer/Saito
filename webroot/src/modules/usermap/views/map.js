define([
  'marionette',
  'templateHelpers',
  'lib/leaflet/leaflet',
  'lib/leaflet/mq'
],
    function(Marionette, TemplateHelpers, L, MQ) {

      'use strict';

      // MapView
      return Marionette.View.extend({

        collectionEvents: {
          'add': '_createMarker',
          'change': '_repinMarker'
        },
        modelEvents: {
          'change:lat': '_setView'
        },
        template: {},
        templateHelpers: TemplateHelpers,

        mapLayer: null,
        markersLayer: null,
        markers: {},

        initialize: function() {
          if(this.model.get('type') === 'world') {
            this._zoomView();
          }
          this.markersLayer = new L.MarkerClusterGroup();
          this._map();
        },

        /**
         * Zooms map nice large view
         *
         * @private
         */
        _zoomView: function() {
          var fancyMargin = 40,
              $content = $('#content'),
              content = { width: $content.width(), top: $content.offset().top },
              // aspect ratio Mercator projection: 198 / 120
              map = { height: content.width * (120 / 198) },
              view = { height: $(window).height() };
          view.available = view.height - content.top;
          if (map.height > view.available) {
            map.height = view.available - fancyMargin;
          }
          this.$el.height(map.height);
        },

        _createMarker: function(user) {
          var latlng = user.latlng(),
              type,
              marker = L.marker(latlng);
          // only create marker if user has location set
          if (!latlng[0]) {
            return;
          }
          if (user) {
            var link = this.templateHelpers.User
                .linkToUserProfile(user.get('id'), user.get('name'));
            marker.bindPopup(link);
          }
          // @performance use addLayers bulk operation
          this.markersLayer.addLayer(marker);
          this.markers[user.get('id')] = marker;

          type = this.model.get('type');
          if (type === 'single' || type === 'edit') {
            var data = {
              lat: user.get('lat'),
              lng: user.get('lng')
            };
            if (type === 'edit') {
              data.zoom = user.get('zoom');
            } else {
              data.zoom = this.model.get('maxZoom');
            }
            this.model.set(data);
          }
        },

        _repinMarker: function(user) {
          if (!user.get('lat')) {
            this._clearMarkers();
            return;
          }

          var marker = this.markers[user.get('id')];
          if (!marker) {
            this._createMarker(user);
            return;
          }

          var ll = marker.getLatLng();
          ll.lat = user.get('lat');
          ll.lng = user.get('lng');
          marker.update();
        },

        _clearMarkers: function() {
          this.markers = {};
          this.markersLayer.clearLayers();
        },

        _setView: function() {
          var lat = this.model.get('lat'),
              lng = this.model.get('lng'),
              zoom = this.model.get('zoom');
          this.mapLayer.setView([lat, lng], zoom);
        },

        _map: function() {
          var tilesLayer = MQ.mapLayer();
          this.mapLayer = L.map(this.el, {
            closePopupOnClick: false,
            maxZoom: this.model.get('maxZoom'),
            minZoom: this.model.get('minZoom'),
            maxBounds: this.model.get('maxBounds')
          });
          this._setView();

          this.mapLayer.addLayer(tilesLayer);
          this.mapLayer.addLayer(this.markersLayer);
        }

      });

    });
