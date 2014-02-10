define([
  'jquery',
  'underscore',
  'backbone',
  'models/app',
  'views/slidetab'
], function($, _, Backbone, App, SlidetabView, ShoutsView) {

  "use strict";

  var SlidetabsView = Backbone.View.extend({

    initialize: function() {
      this.webroot = App.settings.get('webroot');

      this.initCollectionFromDom('.slidetab', this.collection, SlidetabView);

      this.makeSortable();

    },

    makeSortable: function() {
      var webroot = this.webroot;
      this.$el.sortable({
        handle: '.slidetab-tab',
        start: _.bind(function(event, ui) {
          this.$el.css('overflow', 'visible');
        }, this),
        stop: _.bind(function(event, ui) {
          this.$el.css('overflow', 'hidden');
        }, this),
        update: function(event, ui) {
          var slidetabsOrder = $(this).sortable(
              'toArray', {attribute: "data-id"}
          );
          slidetabsOrder = slidetabsOrder.map(function(name) {
            return 'slidetab_' + name;
          });
          // @td make model
          $.ajax({
            type: 'POST',
            url: webroot + 'users/ajax_set',
            data: {
              data: {
                User: {
                  slidetab_order: slidetabsOrder
                }
              }
            },
            dataType: 'json'
          });
        }
      });
    }

  });

  return SlidetabsView;

});