define([
  'app/core',
  'marionette',
  'modules/usermap/views/controls',
  'modules/usermap/views/map',
  'modules/usermap/models/map',
  'modules/usermap/collections/users',
  'text!modules/usermap/templates/layout.html'
],
    function(Application, Marionette, MapController, MapView, MapModel, UsersCollection, LayoutTpl) {

      "use strict";

      var UserMap = Application.module('usermap', {
        startWithParent: false
      });

      UserMap.addInitializer(function() {
        var $usermap = $(".saito-usermap");

        if (!$usermap.length) {
          return;
        }

        var initUserData = $usermap.data('users'),
            users = new UsersCollection(),
            params = $usermap.data('params'),
            Layout = Marionette.Layout.extend({
              el: $usermap,
              template: LayoutTpl,
              regions: {
                mapr: '.saito-usermap-map',
                controls: '.saito-usermap-controls'
              }
            }),
            layout = new Layout().render(),
            controllerView,
            mapModel = new MapModel(params),
            mapView;

        mapView = new MapView({
          // hook directly to layout div to apply 100% height
          el: layout.$('.saito-usermap-map'),
          model: mapModel,
          collection: users
        });
        users.add(initUserData);

        if (params.type === 'edit') {
          mapView.$el.addClass('input');
          controllerView = UserMap.ControlView = new MapController({
            model: users.at(0),
            mapLayer: mapView.mapLayer,
            mapModel: mapModel,
            params: params
          });
          layout.controls.show(controllerView);
        }
      });
      return UserMap;
    });
