define([
    'jquery',
    'underscore',
    'backbone',
    'text!templates/spinner.html'
], function($, _, Backbone,
    spinnerTpl) {

    var PreviewView = Backbone.View.extend({

        initialize: function() {
            this.render();

            this.listenTo(this.model, "change:data", this._spinner)
            this.listenTo(this.model, "change:rendered", this.render)
        },

       _spinner: function() {
           this.$el.html(spinnerTpl);
       },

        render: function() {
            this.$el.html(this.model.get('rendered'));
            return this;
        }

    });

    return PreviewView;

});
