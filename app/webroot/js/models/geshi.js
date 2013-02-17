define([
    'underscore',
    'backbone'
], function(_, Backbone) {

    var GeshiModel = Backbone.Model.extend({

        defaults: {
           'isPlaintext': false
        }

    });
    return GeshiModel;
});