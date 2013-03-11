define([
    'underscore',
    'backbone',
    'models/app'
], function(_, Backbone, App) {

    "use strict";

    var SlidetabModel = Backbone.Model.extend({

        initialize: function() {
            this.webroot = App.settings.get('webroot');
        },

        sync: function() {
            $.ajax({
                url: this.webroot + "users/ajax_toggle/show_" + this.get('id')
            });
        }

    });
    return SlidetabModel;
});