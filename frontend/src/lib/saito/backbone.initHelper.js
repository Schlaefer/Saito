import Backbone from 'backbone';

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
            model: collection.get(id),
            collection: collection
        });
    };

    $(element).each(function(){
            createElement(collection, $(this).data('id'), this);
        }
    );
};
