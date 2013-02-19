define([
    'underscore',
    'backbone'
], function(_, Backbone) {
    var SlidetabModel = Backbone.Model.extend({

        initialize: function(options) {
            // @td
            this.webroot = SaitoApp.app.settings.webroot;
        },

        sync: function() {
            $.ajax({
                url: this.webroot + "/users/ajax_toggle/show_" + this.get('id')
            });
        }

    });
    return SlidetabModel;
});