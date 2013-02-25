define([
    'underscore',
    'backbone',
    'models/app',
    'cakeRest'
], function (_, Backbone, App, cakeRest) {

    "use strict";

    var BookmarkModel = Backbone.Model.extend({

        initialize: function () {
            this.webroot = App.settings.get('webroot') + 'bookmarks/';
        }

    });

    _.extend(BookmarkModel.prototype, SaitoApp.Mixins.cakeRest);

    return BookmarkModel;
});