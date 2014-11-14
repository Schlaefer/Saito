(function (root, factory) {

    "use strict";

    if (typeof define === "function" && define.amd) {
        define(["underscore","backbone"], function(_, Backbone) {
            return factory(_ || root._, Backbone || root.Backbone);
        });
    } else {
        factory(_, Backbone);
    }
})(this, function(_, Backbone) {

    "use strict";

    /**
     * Bool toggle attribute of model
     *
     * @param attribute
     */
    Backbone.Model.prototype.toggle = function(attribute) {
        this.set(attribute, !this.get(attribute));
    };

});
