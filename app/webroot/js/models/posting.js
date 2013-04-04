define([
    'underscore',
    'backbone',
    'models/app'
], function(_, Backbone, App) {

    "use strict";

    var PostingModel = Backbone.Model.extend({

        defaults: {
            isAnsweringFormShown: false,
            html: ''
        },

        fetchHtml: function() {
            $.ajax({
                success: _.bind(function(data) {
                    this.set('html', data);
                }, this),
                type: "post",
                async: false,
                dateType: "html",
                url: App.settings.get('webroot') + 'entries/view/' + this.get('id')
            });
        }

    });

    return PostingModel;
});