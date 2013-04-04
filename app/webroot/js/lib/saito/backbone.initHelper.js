(function (root, factory) {
    if (typeof define === "function" && define.amd) {
        define(["underscore","backbone"], function(_, Backbone) {
            return factory(_ || root._, Backbone || root.Backbone);
        });
    } else {
        factory(_, Backbone);
    }
})(this, function(_, Backbone) {

    /**
     * Init all subviews (models and views) from DOM elements
     *
     * @param element
     * @param collection
     * @param view
     */
    Backbone.View.prototype.initCollectionFromDom = function(element, collection, view) {
        var createElement = function(collection, id, element) {
            collection.add({
                id: id
            });
            new view({
                el: element,
                model: collection.get(id)
            })
        };

        $(element).each(function(){
                createElement(collection, $(this).data('id'), this);
            }
        );
    };

});
