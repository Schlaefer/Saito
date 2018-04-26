define([
    'jquery',
    'underscore',
    'marionette',
    'models/app',
    'modules/slidetabs/views/slidetab',
    'modules/slidetabs/collections/slidetabs',
], function ($, _, Marionette, App, SlidetabView, SlidetabsCollection) {

    'use strict';

    const SlidetabsView = Marionette.View.extend({
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

    return SlidetabsView;
});
