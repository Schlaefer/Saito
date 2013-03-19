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
            isAlwaysShownInline: App.currentUser.get('user_show_inline'),
            isNewToUser: false,
            posting: '',
            html: ''
        },

        initialize: function() {
            this.webroot = App.settings.get('webroot') + 'entries/';
            this.methodToCakePhpUrl = _.clone(this.methodToCakePhpUrl);
            this.methodToCakePhpUrl.read = 'threadLine/';

            this.listenTo(this, "change:html", this._setIsNewToUser);
        },

        _setIsNewToUser: function() {
            // @bogus performance
            this.set('isNewToUser', $(this.get('html')).data('data-new') === 'new');
        }
    });

    _.extend(ThreadLineModel.prototype, cakeRest);

    return ThreadLineModel;
});