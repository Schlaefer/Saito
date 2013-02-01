define([
    'underscore',
    'backbone',
    'collections/bookmarks'
], function(_, Backbone, BookmarkCollection) {
    var BookmarkModel = Backbone.Model.extend({

        methodToCakePhpUrl: {
            'read': 'view',
            'create': 'add',
            'update': 'edit',
            'delete': 'delete'
        },

        urlRoot: SaitoApp.app.webroot + 'bookmarks/',

        initialize: function() {
            Backbone.sync = Backbone.ajaxSync;
        },

        sync: function(method, model, options) {
            options = options || {};
            options.url = this.urlRoot + model.methodToCakePhpUrl[method.toLowerCase()];
            if (!this.isNew()) {
                options.url = options.url + (options.url.charAt(options.url.length - 1) == '/' ? '' : '/') + this.id;
            }
            Backbone.ajaxSync(method, model, options);
        },

        delete: function() {
            this.destroy();
        }
    });
    return BookmarkModel;
});