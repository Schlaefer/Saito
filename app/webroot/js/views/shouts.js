define([
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'jqueryAutosize'
], function($, _, Backbone, App, jqueryAutosize) {

    "use strict";

    var ShoutboxView = Backbone.View.extend({

        lastId: 0,

        events: {
            "keyup form": "formUp",
            "keydown form": "formDown"
        },

        initialize: function(options) {
            this.webroot = App.settings.get('webroot') + 'shouts/';
            this.shouts = this.$el.find('.shouts');
            this.textarea =  this.$el.find('textarea');
            this.slidetabModel = options.slidetabModel;

            this.listenTo(App.status, "change:lastShoutId", this.poll);
            this.listenTo(this.slidetabModel, "change:isOpen", this.poll);

            this.textarea.autosize();
        },

        formDown: function(event) {
            if (event.keyCode == 13 && event.shiftKey === false) {
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
            $.ajax({
                url: this.webroot + 'add',
                type: "post",
                data: {
                   text: this.textarea.val()
                },
                success: _.bind(function(data) {
                    this.poll();
                }, this)
            });
        },

        poll: function(currentShoutId) {

            // update shoutbox only if tab is open
            if(this.$el.is(":visible") === false) {
                return;
            }

            $.ajax({
                url: this.webroot + 'index',
                data: {
                    lastId: this.lastId
                },
                method: 'post',
                dataType: 'html',
                success: _.bind(function(data) {
                    if (data.length > 0) {
                        this.render(data);
                        this.lastId = $(data).find('.shout:first').data('id');
                    }
                }, this)
            });
        },

        render: function(data) {
            $(this.shouts).html(data);
            return this;
        }

    });

    return ShoutboxView;
});
