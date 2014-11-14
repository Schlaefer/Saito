define([
    'underscore',
    'backbone',
    'models/app',
    'cakeRest'
], function(_, Backbone, App, cakeRest) {

    "use strict";

    var ThreadLineModel = Backbone.Model.extend({

        defaults: {
            isInlineOpened: false,
            shouldScrollOnInlineOpen: true,
            isAlwaysShownInline: false,
            isNewToUser: false,
            posting: '',
            html: ''
        },

        initialize: function() {
            this.webroot = App.settings.get('webroot') + 'entries/';
            this.methodToCakePhpUrl = _.clone(this.methodToCakePhpUrl);
            this.methodToCakePhpUrl.read = 'threadLine/';

            this.set('isAlwaysShownInline', App.currentUser.get('user_show_inline') || false);
        }

    });

    _.extend(ThreadLineModel.prototype, cakeRest);

    return ThreadLineModel;
});