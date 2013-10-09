define([
    'jquery', 'underscore', 'backbone', 'marionette', 'models/app',
    'text!modules/shoutbox/templates/shout.html'
], function($, _, Backbone, Marionette, App, Tpl) {

    "use strict";

    var ShoutboxView = Marionette.ItemView.extend({

        initialize: function(options) {
            this.webroot = options.webroot;
        },

        serializeData: function() {
            var data = this.model.toJSON();
            data.user_url = this.webroot + 'users/view/' +
                this.model.get('user_id');
            return data;
        },

        template: function(data) {
            return _.template(Tpl, data);
        }

    });

    return ShoutboxView;
});
