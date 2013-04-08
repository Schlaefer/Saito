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

        initialize: function(options) {
            this.parentThreadline = options.parentThreadline || null;
            this.listenTo(App.eventBus, "isAppVisible", this._focusSubject);

            // autoopen upload view for easy developing
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

        _media: function(event) {
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
            var previewModel;
            event.preventDefault();
            this.$('.preview').slideDown('fast');
            if (this.preview === false) {
                previewModel = new PreviewModel();
                this.preview = new PreviewView({
                    el: this.$('.preview .content'),
                    model: previewModel
                });
            }
            this.preview.model.set('data', this.$('form').serialize());
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
            event.preventDefault();
            $.ajax({
                url: App.settings.get('webroot') + "entries/add",
                type: "POST",
                dataType: 'json',
                data: this.$("#EntryAddForm").serialize(),
                beforeSend:_.bind(function() {
                    this.$('.btn.btn-submit').attr('disabled', 'disabled');
                }, this),
                success:_.bind(function(data) {
                    this.model.set({isAnsweringFormShown: false});
                    if(this.parentThreadline !== null) {
                        this.parentThreadline.set('isInlineOpened', false);
                    }
                    App.eventBus.trigger('newEntry', {
                        tid: data.tid,
                        pid: this.model.get('id'),
                        id: data.id
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
