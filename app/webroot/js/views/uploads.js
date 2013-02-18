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
                    resizable: false,
                    autoOpen: true,
                    modal: true,
                    draggable: false,
                    width: 850,
                    hide: 'fade'
                });
            }
            return this;
        }

    });

    return UploadsView;

});
