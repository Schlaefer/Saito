define(['jquery', 'app/app', 'app/vent', 'marionette',
        'modules/shoutbox/collections/shouts', 'modules/shoutbox/models/shout',
        'modules/shoutbox/views/shouts', 'modules/shoutbox/views/add',
        'modules/shoutbox/views/control',
        'text!modules/shoutbox/templates/layout.html'],
    function($, Application, EventBus, Marionette, ShoutsCollection, ShoutModel, ShoutsCollectionView, ShoutboxAddView, ShoutboxControlView, LayoutTpl) {

      "use strict";

      var ShoutboxModule = Application.module("Shoutbox");

      ShoutboxModule.addInitializer(function(options) {
        var shouts = options.SaitoApp.shouts;
        // @todo
        var webroot = EventBus.reqres.request('webroot');
        var apiroot = EventBus.reqres.request('apiroot');

        if ($("#shoutbox").length) {
          var Shoutbox = {

            // main layout
            layout: null,

            // all viewed shouts
            shoutsCollection: null,

            initialize: function() {
              this.initLayout();
              this.initShoutsCollection();
              this.initAdd();
              this.initShouts();
              this.initControl();
            },

            initShoutsCollection: function() {
              this.shoutsCollection = new ShoutsCollection(shouts, {
                apiroot: apiroot
              });

              var update = _.bind(function() {
                var prevent = !EventBus.reqres.request('slidetab:open', 'shoutbox');
                if (prevent) {
                  return;
                }
                this.shoutsCollection.fetch();
              }, this);

              // always update when slidetab is opened
              EventBus.vent.on('slidetab:open', _.bind(function(data) {
                if (data.slidetab === 'shoutbox') {
                  update();
                }
              }, this));

              // connect external app trigger to issue a reload
              EventBus.commands.setHandler("shoutbox:update", _.bind(function(id) {
                var lastShoutId = this.shoutsCollection.at(0).get('id');
                if (id === lastShoutId) {
                  return;
                }
                update();
              }, this));
            },

            initLayout: function() {
              var ShoutboxLayout = Marionette.Layout.extend({
                el: '#shoutbox',
                template: LayoutTpl,

                regions: {
                  add: '#shoutbox-add',
                  shouts: '#shoutbox-shouts',
                  control: '#shoutbox-control'
                }
              });

              this.layout = new ShoutboxLayout();
              this.layout.render();
            },

            initShouts: function() {
              var shoutsCollectionView = new ShoutsCollectionView({
                collection: this.shoutsCollection,
                webroot: webroot
              });
              this.layout.shouts.show(shoutsCollectionView);
            },

            initControl: function() {
              var shoutsControlView = new ShoutboxControlView();
              this.layout.control.show(shoutsControlView);
            },

            initAdd: function() {
              var addModel = new ShoutModel({
                webroot: webroot,
                apiroot: apiroot
              });
              this.layout.add.show(new ShoutboxAddView({
                model: addModel
              }));
              // reload shouts after new entry
              this.shoutsCollection.listenTo(addModel, 'sync', this.shoutsCollection.fetch);
            }

          };

          Shoutbox.initialize();
        }

      });

      return ShoutboxModule;
    });