define([
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'jqueryAutosize'
], function($, _, Backbone, App, jqueryAutosize) {

    "use strict";

    var ShoutboxView = Backbone.View.extend({

        isPrerendered: true,

        events: {
            "keyup form": "formUp",
            "keydown form": "formDown"
        },

        initialize: function(options) {
            this.webroot = App.settings.get('webroot') + 'shouts/';
            this.shouts = this.$el.find('.shouts');
            this.textarea =  this.$el.find('textarea');
            this.slidetabModel = options.slidetabModel;

            this.listenTo(App.status, "change:lastShoutId", this.fetch);
            this.listenTo(this.slidetabModel, "change:isOpen", this.fetch);
            this.listenTo(this.model, "change:html", this.render);

            this.textarea.autosize();
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

        clearForm: function() {
            this.textarea.val('').trigger('autosize');
        },

        submit: function() {
            this.model.set('newShout', this.textarea.val());
        },

        fetch: function() {
            // update shoutbox only if tab is open
            if(this.slidetabModel.get('isOpen')) {
                this.model.fetch();
            }
        },

        render: function(data) {
            if (this.isPrerendered) {
                this.isPrerendered = false;
            } else {
                $(this.shouts).html(this.model.get('html'));
            }
            return this;
        }

    });

    return ShoutboxView;
});
