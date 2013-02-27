define([
	'underscore',
	'backbone',
    'models/app'
], function (_, Backbone, App) {

    "use strict";

    var ThreadLineModel = Backbone.Model.extend({

        defaults: {
            isInlineOpened: false,
            isAlwaysShownInline: false,
            isNewToUser: false,
            posting: ''
        }

    });

    return ThreadLineModel;

});