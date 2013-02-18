define([
    'jquery',
    'underscore',
    'backbone'
], function($, _, Backbone) {

    var UploadsView = Backbone.View.extend({

        initialize: function(options) {
            this.webroot = options.webroot;
            this.render();
        },

        _getHtml: function() {
            $.ajax({
                url: this.webroot + 'uploads/index',
                success:_.bind(function(data) {
                    this.html = data;
                    this.render();
                }, this)
            });
        },

        render: function() {
            if (_.isEmpty(this.html)) {
                this._getHtml();
            } else {
                this.$('.body').html(this.html)
                this.$el.dialog({
                    title: "Upload",
                    autoOpen: true,
                    modal: true,
                    width: 850,
                    draggable: false,
                    resizable: false,
                    height: $(window).height(),
                    position: {
                        at: "center top"
                    },
                    hide: 'fade'
                });
            }
            return this;
        }

    });

    return UploadsView;

});
