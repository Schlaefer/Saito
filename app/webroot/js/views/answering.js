define([
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'views/uploads', 'views/mediaInsert',
    'models/preview', 'views/preview',
    'lib/saito/jquery.scrollIntoView'
], function($, _, Backbone,
            App,
            UploadsView, MediaInsertView,
            PreviewModel, PreviewView
    ) {

    "use strict";

    var AnsweringView = Backbone.View.extend({

        rendered: false,
        answeringForm: false,
        preview: false,
        mediaView: false,

        /**
         * same model as the parent PostingView
         */
        model: null,

        events: {
            "click .btn-previewClose": "_closePreview",
            "click .btn-preview": "_showPreview",
            "click .btn-markItUp-Upload": "_upload",
            "click .btn-markItUp-Media": "_media",
            "click .btn-submit.js-inlined": "_sendInline"
        },

        initialize: function() {
            this.listenTo(App.eventBus, "isAppVisible", this._focusSubject);
            // this._upload(new Event({}));
        },

        _upload: function(event) {
            var uploadsView;

            event.preventDefault();

            uploadsView = new UploadsView({
                el: '#markitup_upload',
                textarea: this.$('textarea#EntryText')[0]
            });
        },

        _media: function() {
            event.preventDefault();

            if(this.mediaView === false) {
                this.mediaView = new MediaInsertView({
                    el: '#markitup_media',
                    model: this.model
                });
            }
            this.mediaView.render();
        },

        _showPreview: function(event) {
            event.preventDefault();

            this.$('.preview').slideDown('fast');

            if (this.preview === false) {
                this.preview = new PreviewModel();
                new PreviewView({
                    el: this.$('.preview .content'),
                    model: this.preview
                });
            }

            this.preview.set('data', this.$('form').serialize());
        },

        _closePreview: function(event) {
            event.preventDefault();
            this.$('.preview').slideUp('fast');
        },

        _requestAnsweringForm: function() {
            $.ajax({
                url: App.settings.get('webroot') + 'entries/add/' + this.model.get('id'),
                success: _.bind(function(data){
                    this.answeringForm = data;
                    this.render();
                }, this)
            });
        },

        _postProcess: function() {
            this.$el.scrollIntoView('bottom');
            this._focusSubject();
        },

        _focusSubject: function() {
            this.$('.postingform input[type=text]:first').focus();
        },

        _sendInline: function(event) {
            $.ajax({
                url: App.settings.get('webroot') + "entries/add",
                type: "POST",
                data: this.$("#EntryAddForm").serialize(),
                beforeSend:_.bind(function() {
                    this.$('.btn.btn-submit').attr('disabled', 'disabled');
                }, this),
                success:_.bind(function(data) {
                    event.preventDefault();
                    App.eventBus.trigger('appendThreadLine', {
                        parrentId: this.model.get('id') ,
                        html: data
                    });
                }, this)
            });
        },

        render: function() {
            if (this.answeringForm === false) {
                this._requestAnsweringForm();
            } else if (this.rendered === false) {
                this.rendered = true;
                this.$el.html(this.answeringForm);
                _.defer(function(caller){
                   caller._postProcess();
                }, this);
            }
            return this;
        }

    });

    return AnsweringView;

});
