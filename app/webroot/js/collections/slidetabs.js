define([
    'underscore',
    'backbone',
    'models/slidetab'
], function(_, Backbone, SlidetabModel) {
    var SlidetabCollection = Backbone.Collection.extend({
        model: SlidetabModel
    });
    return SlidetabCollection;
});
