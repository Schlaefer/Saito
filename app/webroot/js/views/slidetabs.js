define([
    'jquery',
    'underscore',
    'backbone',
    'views/slidetab',
    'views/shouts'
], function($, _, Backbone, SlidetabView, ShoutsView) {

    var SlidetabsView = Backbone.View.extend({

        initialize: function(options) {
            this.initCollectionFromDom('.slidetab', this.collection, SlidetabView);

            if (this.collection.get('shoutbox')) {
                this.initShoutbox('#shoutbox', options.webroot);
            }

        },

        initShoutbox: function(element_n, webroot) {
            new ShoutsView({
                el: "#shoutbox",
                urlBase: webroot
            });
        }

    });

    return SlidetabsView;

});