define([
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'lib/saito/markItUp.media',
    'text!templates/mediaInsert.html'
], function($, _, Backbone, App, MarkItUpMedia, mediaInsertTpl) {

    "use strict";

    return Backbone.View.extend({

        template:_.template(mediaInsertTpl),

        events: {
            "click #markitup_media_btn": "_insert"
        },

        initialize: function() {
            if (this.model !== undefined && this.model !== null) {
                this.listenTo(this.model, 'change:isAnsweringFormShown', this.remove);
            }
        },

        _insert: function(event) {
            var out,
                markItUpMedia;

            event.preventDefault();

            this.$('#markitup_media_message').hide();

            markItUpMedia = MarkItUpMedia;
            out = markItUpMedia.multimedia(
                this.$('#markitup_media_txta').val(),
                {embedlyEnabled: App.settings.get('embedly_enabled') === true}
            );

            if (out === '') {
                this._invalidInput();
            } else {
                $.markItUp({replaceWith: out});
                this._closeDialog();
            }
        },

        _invalidInput: function() {
            this.$('#markitup_media_message').show();
            this.$el
                .dialog()
                .parent()
                .effect("shake", {times: 2}, 250);
        },

        _closeDialog: function() {
            this.$el.dialog('close');
            this.$('#markitup_media_txta').val('');
        },

        _showDialog: function() {
            this.$el.dialog({
                show: {effect: "scale", duration: 200},
                hide: {effect: "fade", duration: 200},
                title: $.i18n.__("Multimedia"),
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
            this.$el.html(this.template);
            this._showDialog();
            return this;
        }

    });

});
