define([
], function() {

    SaitoApp.Mixins = SaitoApp.Mixins || {};

    SaitoApp.Mixins.cakeRest = {

        methodToCakePhpUrl: {
            'read': 'view',
            'create': 'add',
            'update': 'edit',
            'delete': 'delete'
        },

        sync: function(method, model, options) {
            this.urlRoot = this.webroot;
            options = options || {};
            options.url = this.urlRoot + model.methodToCakePhpUrl[method.toLowerCase()];
            if (!this.isNew()) {
                options.url = options.url + (options.url.charAt(options.url.length - 1) == '/' ? '' : '/') + this.id;
            }
            Backbone.ajaxSync(method, model, options);
        }

    }

});
