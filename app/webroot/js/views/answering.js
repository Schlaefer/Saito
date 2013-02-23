define([
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'views/uploads',
    'models/preview', 'views/preview'
], function($, _, Backbone,
            App,
            UploadsView,
            PreviewModel, PreviewView
    ) {

    var AnsweringView = Backbone.View.extend({

        rendered: false,
        answeringForm: false,
        preview: false,

        events: {
            "click .btn-previewClose": "_closePreview",
            "click .btn-preview": "_showPreview",

            "click .btn-markItUp-Upload": "_upload"
        },

        initialize: function(options) {
            this.id = options.id;

            //@td
            // this._upload(new Event({}));
        },

        _upload: function(event) {
            event.preventDefault();
            new UploadsView({
                el: '#markitup_upload',
                textarea: this.$('textarea#EntryText')[0]
            });
        },

        _showPreview: function(event) {
            event.preventDefault();

            this.$('.preview').slideDown('fast');

            if (this.preview === false) {
                this.preview = new PreviewModel({
                    webroot: App.settings.get('webroot')
                });
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
                url: App.settings.get('webroot') + 'entries/add/' + this.id,
                success: _.bind(function(data){
                    this.answeringForm = data;
                    this.render();
                }, this)
            });
        },

        _postProcess: function() {
            if(!_isScrolledIntoView(this.$('.posting_formular_slider_bottom'))) {
                scrollToBottom(this.$('.posting_formular_slider_bottom'));
            }
            $('.postingform input[type=text]:first').focus();
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
