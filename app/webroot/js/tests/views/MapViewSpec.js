// initialize global leaflet objects
var L = {};
var MQ = {};

define([
  'jquery',
  'underscore',
  'modules/usermap/usermap',
  'text!tests/fixtures/mapViewEdit.html',
  'lib/jquery.i18n/jquery.i18n.extend'
], function($, _, Usermap, mapEditFixture) {

  describe("Map", function() {

    describe("View", function() {

      var _stubLeafletGlobalObjects = function() {
        //
        var MapLayer = {
          on: sinon.stub(),
          addLayer: sinon.stub(),
          setView: sinon.stub()
        };

        var Marker = {
          bindPopup: sinon.spy()
        };

        _.extend(L, {
          addLayer: sinon.spy(),
          map: function() {
            return MapLayer;
          },
          marker: function() {
            return Marker;
          },
          MarkerClusterGroup: function() {
            return  {
              addLayer: sinon.spy()
            };
          }
        });

        _.extend(MQ, {
          mapLayer: sinon.spy()
        });
      };

      beforeEach(function() {
        $.i18n.setDict({});
        _stubLeafletGlobalObjects();
      });

      it('should show location button if browser supports it', function() {
        setFixtures(mapEditFixture);
        Usermap.start();
        sinon.stub(Usermap.ControlView, '_geolocation', function() {
          return true;
        });
        expect($('.saito-usermap')).toContainElement('button.js-btn-locate');
        Usermap.ControlView._geolocation.restore();
      });

      it('should not show location button if browser doesn\'t support it', function() {
        setFixtures(mapEditFixture);
        Usermap.start();
        sinon.stub(Usermap.ControlView, '_geolocation', function() {
          return false;
        });
        expect($('.saito-usermap')).not.toContainElement('button.js-btn-locate');
        Usermap.ControlView._geolocation.restore();
      });

    });

  });
});
