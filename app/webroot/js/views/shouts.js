define([
    'jquery',
    'underscore',
    'backbone'
], function($, _, Backbone) {

    var ShoutboxView = Backbone.View.extend({

        refreshTime: 5000,

        lastPoll: false,

        events: {
            "keydown form": "form"
        },

        initialize: function(options) {
            this.urlBase = options.urlBase + 'shouts/';
            this.shouts = this.$el.find('.shouts');
            this.poll();
        },

        form: function(event) {
            if (event.keyCode == 13) {
                this.submit();
            }
        },

        clearForm: function() {
            this.$el.find('textarea').val('');
        },

        submit: function() {
            $.ajax({
                url: this.urlBase + 'add',
                type: "post",
                data: {
                   text: this.$el.find('textarea').val()
                },
                success: _.bind(function(data) {
                    this.clearForm();
                    clearTimeout(this.timeoutId);
                    this.poll(data);
                }, this)
            });
        },

        poll: function() {

            this.timeoutId = setTimeout(_.bind(this.poll, this), this.refreshTime);

            // update shoutbox only if tab is open
            if(this.$el.is(":visible") === false) {
                return;
            }

            $.ajax({
                url: this.urlBase + 'index',
                data: {
                    lastPoll: this.lastPoll
                },
                method: 'post',
                dataType: 'html',
                success: _.bind(function(data) {
                    if (data.length > 0) {
                        this.render(data);
                    }
                    this.lastPoll = Math.round(Date.now() / 1000);
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
