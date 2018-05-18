import Backbone from 'backbone';

/**
 * Bool toggle attribute of model
 *
 * @param attribute
 */
Backbone.Model.prototype.toggle = function(attribute) {
    this.set(attribute, !this.get(attribute));
};
