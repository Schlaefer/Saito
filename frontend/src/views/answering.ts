import * as autosize from 'autosize';
import { Collection, Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as $ from 'jquery';
import { CakeFormErrorView } from 'lib/saito/CakeFormErrorView';
import 'lib/saito/jquery.insertAtCaret';
import 'lib/saito/jquery.scrollIntoView';
import App from 'models/app';
import { PreviewView } from 'modules/answering/preview';
import { SmiliesCollectionView } from 'modules/answering/smilies';
import { SubjectInputView } from 'modules/answering/SubjectInputView';
import ModalDialog from 'modules/modalDialog/modalDialog';
import UploaderView from 'modules/uploader/uploader';
import * as _ from 'underscore';
import EditCountdown from 'views/editCountdown';
import MediaInsertView from 'views/mediaInsert';

class AnsweringView extends View<Model> {
    /** answering form was loaded via ajax request */
    private ajax: boolean;

    private requestUrl: string;

    private rendered: boolean;

    private answeringForm: any;

    private sendInProgress: boolean;

    private errorVw: View<Model>;

    private subjectView: View<Model>;

    /** answering form is in posting which is inline-opened */
    private parentThreadline: Model;

    public constructor(options: any = {}) {
        _.defaults(options, {
            childViewEvents: {
                'answering:insert': '_insert',
            },
            events: {
                'click .btn-markItUp-Media': '_media',
                'click .btn-markItUp-Smilies': '_handleSmilies',
                'click .btn-markItUp-Upload': 'showUploadForm',
                'click .btn-primary': '_send',
                'click .js-btnCite': '_handleCite',
                'click .js-btnPreview': '_showPreview',
                'click .js-btnPreviewClose': '_closePreview',
                'keypress .js-subject': '_onKeyPressSubject',
            },
            /**
             * same model as the parent PostingView
             */
            model: null,
            regions: {
                preview: '.preview-wrapper',
                smilies: '.js-rgSmilies',
            },
            template: _.noop,
        });
        super(options);
    }

    public initialize(options) {
        this.ajax = _.isUndefined(options.ajax) ? true : false;
        this.answeringForm = false;
        this.parentThreadline = options.parentThreadline || null;
        this.rendered = false;
        this.requestUrl = null;
        this.sendInProgress = false;

        this.requestUrl = App.settings.get('webroot') +
            'entries/add/' + this.model.get('id');

        // focus can only be set after element is visible in page
        this.listenTo(App.eventBus, 'isAppVisible', this._focusSubject);
    }

    public onRender() {
        // create new thread on /entries/add
        if (this.ajax === false) {
            this._onFormReady();
        } else if (this.answeringForm === false) {
            this._requestAnsweringForm();
        } else if (this.rendered === false) {
            this.rendered = true;
            this.$el.html(this.answeringForm);
            _.defer(() => {
                this._postRendering();
            });
        } else {
            App.eventBus.trigger('change:DOM');
        }
        return this;
    }

    public onBeforeDestroy() {
        if (this.errorVw) {
            this.errorVw.destroy();
        }
        this.subjectView.destroy();
    }

    private _disable() {
        this.$('.btn.btn-primary').attr('disabled', 'disabled');
    }

    private _enable() {
        this.$('.btn.btn-primary').removeAttr('disabled');
    }

    /**
     * Quote parent posting
     *
     * @private
     */
    private _handleCite(event) {
        event.preventDefault();
        const parentText = this.$('.js-btnCite').data('text');
        this._insert(parentText);
    }

    private _onKeyPressSubject(event) {
        // intercepts sending to form's action url when inline answering
        if (event.keyCode === 13) {
            this._send(event);
        }
    }

    private _handleSmilies(event) {
        event.preventDefault();

        const region = this.getRegion('smilies');
        if (!region.hasView()) {
            const view = new SmiliesCollectionView();
            view.collection.add((window as any).smiliesData);
            this.showChildView('smilies', view);
        }
        this.getChildView('smilies').$el.collapse('toggle');
    }

    private showUploadForm(event) {
        const answering = this;

        class InsertVw extends View<Model> {
            public constructor(options: object = {}) {
                _.defaults(options, {
                    events: { 'click button': 'handleInsert' },
                    template: _.template('<button class="btn btn-primary"><%- $.i18n.__("upl.btn.insert") %></button>'),
                });
                super(options);
            }
            private handleInsert() {
                const text = '[upload]' + this.model.get('name') + '[/upload]';
                answering._insert(text, { focus: false });
                ModalDialog.hide();
            }
        }

        const uploadsView = new UploaderView({
            InsertVw,
            className: 'imageUploader',
            el: '#markitup_upload',
        });

        ModalDialog.show(uploadsView, { title: $.i18n.__('upl.title'), width: 'max' });
        uploadsView.render();
    }

    /**
     * Inserts text at current cursor position in textfield.
     *
     * @param {string} text text to insert
     * @param {object} options addiontal options
     * - {bool} focus focus textfield after insertion
     * @private
     */
    private _insert(text, options: any = {}) {
        options = _.defaults(options, { focus: true });
        const textarea = this.$('textarea');
        textarea.insertAtCaret(text);
        autosize.update(textarea);

        options.focus ? textarea.focus() : textarea.blur();
    }

    private _media(event) {
        event.preventDefault();
        new MediaInsertView({ model: this.model }).render();
    }

    private _showPreview(event) {
        const form = event.currentTarget.form;
        if (!(this.checkFormValidity(form))) {
            return;
        }

        this.$('.preview-wrapper').slideDown('fast');

        if (!this.getChildView('preview')) {
            this.showChildView('preview', new PreviewView());
        }
        const preview = this.getChildView('preview');

        if (!this.errorVw) {
            this.errorVw = new CakeFormErrorView({
                collection: new Collection(),
                el: form,
            });
        }

        preview.model.save(
            {
                category_id: this.$('#category-id').val(),
                html: null,
                pid: this.$('input[name=pid]').val(),
                subject: this.$('.js-subject').val(),
                text: this.$('textarea').val(),
            },
            {
                error: (model, response, options) => {
                    if (!('errors' in response.responseJSON)) {
                        App.eventBus.trigger('notification', {
                            message: $.i18n.__('preview.e.generic'),
                            type: 'error',
                        });

                        return;
                    }

                    this.errorVw.collection.reset(response.responseJSON.errors);
                },
                success: (mode, response, options) => {
                    this.errorVw.collection.reset();
                },
            },
        ).always(() => {
            this.errorVw.render();
        });
    }

    private _closePreview(event) {
        event.preventDefault();
        this.$('.preview-wrapper').slideUp('fast');
    }

    private _setupTextArea() {
        const $textarea = this.$('textarea');
        autosize($textarea);
    }

    private _requestAnsweringForm() {
        $.ajax({
            // don't append timestamp to requestUrl or Cake's
            // SecurityComponent will blackhole the ajax call in _sendInline()
            cache: true,
            success: (data) => {
                this.answeringForm = data;
                this.render();
            },
            url: this.requestUrl,
        });
    }

    private _postRendering() {
        this.$el.scrollIntoView('bottom');
        this._focusSubject();
        this._onFormReady();
    }

    private _onFormReady() {
        this.subjectView = new SubjectInputView({
            el: this.$('.postingform-subject-wrapper'),
        });

        this._setupTextArea();

        const data = this.$('.js-data').data();
        const action = _.property(['meta', 'action'])(data);
        if (action === 'edit') {
            this.model.set('time', data.entry.time);
            this._addCountdown();
        }
        App.eventBus.trigger('change:DOM');
    }

    /**
     * Adds countdown to Submit button
     *
     * @private
     */
    private _addCountdown() {
        const $submitButton = this.$('.js-btn-primary');
        const editCountdown = new EditCountdown({
            done: 'disable',
            editPeriod: App.settings.get('editPeriod'),
            el: $submitButton,
            model: this.model,
        });
    }

    private _focusSubject() {
        // focus is broken in Mobile Safari iOS 8
        const iOS = window.navigator.userAgent.match('iPad|iPhone');
        if (iOS) {
            return;
        }

        this.$('.postingform input[type=text]:first').focus();
    }

    private _send(event) {
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
    }

    private _sendRedirect(event) {
        event.preventDefault();
        const button: HTMLButtonElement & any = this.$('.btn-primary')[0];
        if (!this.checkFormValidity(button.form)) {
            this.sendInProgress = false;
            return;
        }
        button.disabled = true;
        button.form.submit();
    }

    /**
     * Check form validity and trigger error messages in browser
     */
    private checkFormValidity(form: HTMLFormElement): boolean {
        if (form.checkValidity()) {
            return true;
        }

        // we can't trigger JS validation messages via form.submit()
        // so we create and click this hidden dummy submit button
        const handle = 'js-checkValidityDummy';
        let checkValidityDummy = this.$(handle);
        if (!checkValidityDummy.length) {
            checkValidityDummy = $(
                '<button></button>',
                { class: handle, style: 'display: none;', type: 'submit' },
            ).appendTo(form);
        }
        checkValidityDummy.click();

        return false;
    }

    private _sendInline(event) {
        event.preventDefault();

        const data = this.$('#EntryAddForm').serialize();

        const success = (responseData) => {
            this.model.set({ isAnsweringFormShown: false });
            if (this.parentThreadline !== null) {
                this.parentThreadline.set('isInlineOpened', false);
            }
            App.eventBus.trigger('newEntry', {
                id: responseData.id,
                isNewToUser: true,
                pid: this.model.get('id'),
                tid: responseData.tid,
            });
        };

        const fail = _.bind(function(jqXHR, text) {
            this.sendInProgress = false;
            this._enable();
            App.eventBus.trigger('notification', {
                message: jqXHR.responseText,
                title: text,
                type: 'error',
            });
        }, this);

        const disable = _.bind(this._disable, this);

        $.ajax({
            beforeSend: disable,
            data,
            dataType: 'json',
            type: 'POST',
            url: this.requestUrl,
        }).done(success).fail(fail);
    }
}

export { AnsweringView };
