define([
    'jquery', 'underscore', 'backbone', 'marionette','models/app',
    'modules/shoutbox/models/shout',
    'text!modules/shoutbox/templates/add.html',
    'jqueryAutosize'
], function($, _, Backbone, Marionette, App, ShoutModel, Tpl) {

    "use strict";

    var ShoutboxAdd = Marionette.ItemView.extend({

        template: Tpl,

        events: {
            "keyup form": "formUp",
            "keydown form": "formDown"
        },

        submit: function() {
            this.model.set('text', this.textarea.val());
        },

        clearForm: function() {
            this.textarea.val('').trigger('autosize');
        },

        formDown: function(event) {
            if (event.keyCode === 13 && event.shiftKey === false) {
                this.submit();
                this.clearForm();
                event.preventDefault();
            }
        },

        formUp: function() {
            if (this.textarea.val().length > 0) {
                App.eventBus.trigger('breakAutoreload');
            } else if (this.textarea.val().length === 0) {
                App.eventBus.trigger('initAutoreload');
            }
        },

        render: function() {
            this.$el.html(this.template);
            this.textarea =  this.$('#shoutbox-input');
            this.textarea.autosize();
            return this;
        }


    });

    return ShoutboxAdd;

});
