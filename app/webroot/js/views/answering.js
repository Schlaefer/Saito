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
        sendInProgress: false,

        /**
         * same model as the parent PostingView
         */
        model: null,

        events: {
            "click .btn-previewClose": "_closePreview",
            "click .btn-preview": "_showPreview",
            "click .btn-markItUp-Upload": "_upload",
            "click .btn-markItUp-Media": "_media",
            "click .btn-submit": "_send",
            "click .btn-cite": "_cite",
            "keypress .inp-subject": "_onKeyPressSubject"
        },

        initialize: function(options) {
            this.parentThreadline = options.parentThreadline || null;

            this.listenTo(App.eventBus, "isAppVisible", this._focusSubject);

            // auto-open upload view for easy developing
            // this._upload(new Event({}));
        },

        _cite: function(event) {
            event.preventDefault();
            var citeContainer = this.$('.cite-container'),
                citeText = this.$('.btn-cite').data('text'),
                currentText = this.$textarea.val();

            this.$textarea.val(citeText + "\n\n" + currentText);
            citeContainer.slideToggle();
            this.$textarea.focus();
        },

        _onKeyPressSubject: function(event) {
            if (event.keyCode === 13) {
                this._send(event);
            }
        },

        _upload: function(event) {
            var uploadsView;
            event.preventDefault();
            uploadsView = new UploadsView({
                el: '#markitup_upload',
                textarea: this.$textarea[0]
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

        _setupTextArea: function() {
            this.$textarea = $('textarea#EntryText');
            this.$textarea.val('');
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

        _postRendering: function() {
            this.$el.scrollIntoView('bottom');
            this._focusSubject();
        },

        _focusSubject: function() {
            this.$('.postingform input[type=text]:first').focus();
        },

        _send: function(event) {
            if (this.sendInProgress) {
                event.preventDefault();
                return;
            }
            this.sendInProgress = true;
            if (this.parentThreadline) {
                this._sendInline(event);
            } else {
                this._sendRedirect(event);
            }
        },

        _sendRedirect: function(event) {
            var button = this.$('.btn-submit')[0];
            event.preventDefault();
            if (typeof button.validity === 'object' &&
                button.form.checkValidity() === false) {
                // we can't trigger JS validation messages via form.submit()
                // so we create and click this hidden dummy submit button
                var submit = _.bind(function() {
                    if (!this.checkValidityDummy) {
                        this.checkValidityDummy = $('<button></button>', {
                            type: 'submit',
                            style: 'display: none;'
                        });
                        $(button).after(this.checkValidityDummy);
                    }
                    this.checkValidityDummy.click();
                }, this);

                submit();
            } else {
                button.disabled = true;
                button.form.submit();
            }
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
                this._setupTextArea();
                _.defer(function(caller) {
                    caller._postRendering();
                }, this);
            }
            return this;
        }

    });

    return AnsweringView;

});
