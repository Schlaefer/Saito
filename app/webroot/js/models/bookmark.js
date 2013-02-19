define([
    'underscore',
    'backbone',
    'cakeRest'
], function(_, Backbone, cakeRest) {

    var BookmarkModel = Backbone.Model.extend({

        initialize: function() {
            Backbone.sync = Backbone.ajaxSync;
            // @td
            this.webroot = SaitoApp.app.settings.webroot + 'bookmarks/';
        }

    });

    _.extend(BookmarkModel.prototype, SaitoApp.Mixins.cakeRest)

    return BookmarkModel;
});