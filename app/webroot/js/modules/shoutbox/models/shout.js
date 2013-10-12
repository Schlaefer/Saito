define(['underscore', 'backbone'], function(_, Backbone) {

  "use strict";

  var ShoutModel = Backbone.Model.extend({

    initialize: function(options) {
      // this.apiroot = options.apiroot + 'shouts/';
      this.webroot = options.webroot + 'shouts/';
      this.collection = options.collection;
    },

    save: function() {
      $.ajax({
        url: this.webroot + 'add',
        type: "post",
        dataType: 'json',
        data: {
          text: this.get('text')
        },
        context: this
      }).done(function(data) {
            // reload shouts after new entry
            this.collection.reset(data);
          });
    }

  });

  return ShoutModel;

});
