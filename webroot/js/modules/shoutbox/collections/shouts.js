define(['underscore', 'backbone', 'modules/shoutbox/models/shout'],
    function(_, Backbone, ShoutModel) {

      'use strict';

      var ShoutsCollection = Backbone.Collection.extend({

        model: ShoutModel,

        initialize: function(shouts, options) {
          this.apiroot = options.apiroot + 'shouts/';
        },

        fetch: function() {
          $.ajax({
            url: this.apiroot,
            dataType: 'json',
            context: this
          }).done(function(data) {
                if (data.length > 0) {
                  this.reset(data);
                }
              });
        }

      });

      return ShoutsCollection;
    });
