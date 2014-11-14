define([
    'underscore',
    'backbone',
    'models/geshi'
], function(_, Backbone, GeshiModel) {

    var GeshisCollection = Backbone.Collection.extend({
        model: GeshiModel
    });

    return GeshisCollection;

});
