define(['underscore', 'backbone'], function(_, Backbone) {

    "use strict";

    var ShoutModel = Backbone.Model.extend({

        initialize: function(options) {
            this.webroot = options.webroot + 'shouts/';
            this.apiroot = options.apiroot + 'shouts/';

            this.listenTo(this, "change:text", this.send);
        },

        send: function() {
            $.ajax({
                url: this.webroot + 'add',
                type: "post",
                data: {
                    text: this.get('text')
                },
                context: this
            }).done(function() {
                    this.trigger('sync');
                });
        }

    });

    return ShoutModel;

});
