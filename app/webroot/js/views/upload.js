define([
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'text!templates/upload.html'
], function($, _, Backbone,
            App,
            uploadTpl
    ) {

    var UploadView = Backbone.View.extend({

        className: "box-content upload_box current",

        events: {
            "click .upload_box_delete": "_removeUpload",
            "click .btn-submit" : "_insert"
        },

        initialize: function(options) {
            this.textarea = options.textarea;

            this.listenTo(this.model, "destroy", this._uploadRemoved);
        },

        _removeUpload: function(event) {
            event.preventDefault();
            this.model.destroy({
                    success:_.bind(function(model, response) {
                        App.eventBus.trigger(
                            'notification',
                             response.SaitoApp.msg[0]
                        );
                    }, this)
                }
            );
        },

        _uploadRemoved: function() {
            this.remove();
        },

        _insert: function(event) {
            event.preventDefault();
            this._insertAtCaret(
                "[upload]" + this.model.get('name') + "[/upload]",
                this.textarea
            );
        },

        _insertAtCaret: function(text, txtarea) {
            //    var scrollPos = txtarea.scrollTop;
            var strPos = 0;
            var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
                "ff" : (document.selection ? "ie" : false ) );
            if (br == "ie") {
                txtarea.focus();
                var range = document.selection.createRange();
                range.moveStart ('character', -txtarea.value.length);
                strPos = range.text.length;
            }
            else if (br == "ff") strPos = txtarea.selectionStart;

            var front = (txtarea.value).substring(0,strPos);
            var back = (txtarea.value).substring(strPos,txtarea.value.length);
            txtarea.value=front+text+back;
            strPos = strPos + text.length;
            if (br == "ie") {
                txtarea.focus();
                var range = document.selection.createRange();
                range.moveStart ('character', -txtarea.value.length);
                range.moveStart ('character', strPos);
                range.moveEnd ('character', 0);
                range.select();
            }
            else if (br == "ff") {
                txtarea.selectionStart = strPos;
                txtarea.selectionEnd = strPos;
                txtarea.focus();
            }
        //    txtarea.scrollTop = scrollPos;

        },

        render: function() {
            this.$el.html(_.template(uploadTpl, this.model.toJSON()));
            return this;
        }

    });

    return UploadView;

});
