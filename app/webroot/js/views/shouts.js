define([
    'jquery',
    'underscore',
    'backbone',
    'jqueryAutosize'
], function($, _, Backbone, jqueryAutosize) {

    var ShoutboxView = Backbone.View.extend({

        refreshTime: 5000,

        lastPoll: false,

        events: {
            "keydown form": "form"
        },

        initialize: function(options) {
            this.urlBase = options.urlBase + 'shouts/';
            this.shouts = this.$el.find('.shouts');
            this.textarea =  this.$el.find('textarea');

            this.textarea.autosize();
            this.poll();
        },

        resizeIt: function() {
            var str = this.$el.val();
            var cols = this.$el.cols;

            var linecount = 0;
            $(str.split("\n")).each( function(l) {
                linecount += Math.ceil( l.length / cols ); // take into account long lines
            } )
           this.$el.rows = linecount + 1;
        },

        form: function(event) {
            this.resizeIt();
            if (event.keyCode == 13) {
                this.submit();
            }

        },

        clearForm: function() {
            this.textarea.val('').trigger('autosize');
        },

        submit: function() {
            $.ajax({
                url: this.urlBase + 'add',
                type: "post",
                data: {
                   text: this.textarea.val()
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
