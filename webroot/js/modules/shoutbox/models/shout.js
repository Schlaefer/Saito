define(['underscore', 'backbone'], function(_, Backbone) {

  "use strict";

  var ShoutModel = Backbone.Model.extend({

    initialize: function(attributes, options) {
      this.urlRoot = options.urlRoot;
    }

  });

  return ShoutModel;

});
