define([
'jquery',
'underscore',
'backbone'
], function($, _, Backbone) {

    "use strict";

    return Backbone.View.extend({

        initialize: function() {
            this.listenTo(this.model, 'change:isAnsweringFormShown', this.remove);
        },

        _showDialog: function() {
            this.$el.dialog({
                show: {effect: "scale", duration: 200},
                hide: {effect: "fade", duration: 200},
                title: "Multimedia", // @td
                resizable: false,
                open: function() {
                    setTimeout(function() {$('#markitup_media_txta').focus();}, 210);
                },
                close: function() {
                    $('#markitup_media_message').hide();
                }
            });
        },

        render: function() {
            this._showDialog();
            return this;
        }

    });

});
