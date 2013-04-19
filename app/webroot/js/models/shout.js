define([
    'underscore',
    'backbone',
    'models/app'
], function(_, Backbone, App) {

    "use strict";

    var ShoutModel = Backbone.Model.extend({
        defaults: {
            html: '',
            newShout: ''
        },

        initialize: function() {
            this.webroot = App.settings.get('webroot') + 'shouts/';

            this.listenTo(this, "change:newShout", this.send);
        },

        fetch: function() {
            $.ajax({
                url: this.webroot + 'index',
                dataType: 'html',
                success: _.bind(function(data) {
                    if (data.length > 0) {
                        this.set({html: data});
                    }
                }, this)
            });
        },

        send: function() {
            $.ajax({
                url: this.webroot + 'add',
                type: "post",
                data: {
                    text: this.get('newShout')
                },
                success: _.bind(function() {
                    this.fetch();
                }, this)
            });
        }
    });

    return ShoutModel;
});
