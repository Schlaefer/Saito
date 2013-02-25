define([
    'underscore',
    'backbone'
], function (_, Backbone) {

    "use strict";

    var PostingModel = Backbone.Model.extend({

        defaults: {
            isAnsweringFormShown: false
        },

        toggle: function (attribute) {
            this.set(attribute, !this.get(attribute));
        }

    });

    return PostingModel;
});