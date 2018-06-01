import $ from 'jquery';
import _ from 'underscore';
import Marionette from 'backbone.marionette';
import App from 'models/app';
import SlidetabView from 'modules/slidetabs/views/slidetab';
import SlidetabsCollection from 'modules/slidetabs/collections/slidetabs';

export default Marionette.View.extend({
  // set DOM-element so marionette treads it as prerendered
  el: '#slidetabs',

  initialize: function () {
    this.collection = new SlidetabsCollection();
    this.webroot = App.settings.get('webroot');

    this.initCollectionFromDom('.slidetab', this.collection, SlidetabView);

    this.makeSortable();
  },

  makeSortable: function () {
    const webroot = this.webroot;
    this.$el.sortable({
      handle: '.slidetab-tab',
      start: _.bind(function (event, ui) {
        this.$el.css('overflow', 'visible');
      }, this),
      stop: _.bind(function (event, ui) {
        this.$el.css('overflow', 'hidden');
      }, this),
      update: function (event, ui) {
        var slidetabsOrder = $(this).sortable('toArray', { attribute: 'data-id' });
        slidetabsOrder = slidetabsOrder.map(function (name) {
          return 'slidetab_' + name;
        });
        // @todo make model/collection
        $.post(
          webroot + 'users/slidetabOrder',
          { slidetabOrder: slidetabsOrder }
        );
      }
    });
  },
});
