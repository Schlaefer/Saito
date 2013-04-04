define([
    'underscore',
    'backbone'
], function (_, Backbone) {

    "use strict";

    var GeshiModel = Backbone.Model.extend({

        defaults: {
           isPlaintext: false
        }

    });

    return GeshiModel;
});