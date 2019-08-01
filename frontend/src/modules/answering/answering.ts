/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Collection, Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as Radio from 'backbone.radio';
import * as $ from 'jquery';
import 'jquery-textrange';
import { CakeFormErrorView } from 'lib/saito/CakeFormErrorView';
import 'lib/saito/jquery.scrollIntoView';
import App from 'models/app';
import { PreviewView } from 'modules/answering/preview';
import { SubjectInputView } from 'modules/answering/SubjectInputView';
import * as _ from 'underscore';
import { unescapeHTML } from 'underscore.string';
import EditCountdownView from './EditCountdown';
import { EditorView } from './editor/EditorView';
import AnswerModel from './models/AnswerModel';

class AnsweringView extends View<Model> {
    /** answering form was loaded via ajax request */
    private ajax: boolean;

    private requestUrl: string;

    private rendered: boolean;

    private sendInProgress: boolean;

    private errorVw: View<Model>;

    private subjectView: View<Model>;

    /** answering form is in posting which is inline-opened */
    private parentThreadline: Model;

    public constructor(options: any = {}) {
        _.defaults(options, {
            events: {
                'click .js-btnCite': '_handleCite',
                'click .js-btnPreview': '_showPreview',
                'click .js-btnPreviewClose': '_closePreview',
                'click @ui.btnPrimary': '_send',
                'keypress .js-subject': '_onKeyPressSubject',
            },
            model: new AnswerModel(),
            modelEvents: {
                change: 'onAnswerModelChange',
            },
            regions: {
                preview: '.preview-wrapper',
            },
            template: _.noop,
            ui: {
                btnPrimary: '.js-btn-primary',
            },
        });
        super(options);
    }

    public initialize(options) {
        this.ajax = _.isUndefined(options.ajax) ? true : false;
        this.parentThreadline = options.parentThreadline || null;
        this.rendered = false;
        this.requestUrl = null;
        this.sendInProgress = false;

        this.requestUrl = App.settings.get('webroot') +
            'entries/add/' +
            (this.model.get('pid') || '');

        // focus can only be set after element is visible in page
        this.listenTo(App.eventBus, 'isAppVisible', this._focusSubject);
    }

    public onRender() {
        if (this.ajax === false) {
            // create new thread on /entries/add
            this._onFormReady();
        } else if (this.rendered === false) {
            this.rendered = true;
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
    private _handleCite() {
        // Without defering a click on a selection which deselects (and should therefore be empty)
        // still holds the previously selected text.
        _.defer(() => {
            let text = window.getSelection().toString();
            if (text !== '') {
                text = App.settings.get('quote_symbol') + ' ' + text;
            } else {
                text = unescapeHTML(this.$('.js-btnCite').data('text'));
            }

            Radio.channel('editor').request('insert:text', text);
        });
    }

    private _onKeyPressSubject(event) {
        // intercepts sending to form's action url when inline answering
        if (event.keyCode === 13) {
            this._send(event);
        }
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

    private _postRendering() {
        this._focusSubject();
        this._onFormReady();

        // On a fast server the answering form might be inserted
        // before the slide down animation from postingSlider is even
        // finished. So we just wait for a little time here.
        // @bogus, Fix/obsolete when implementing a new posting form.
        _.delay(() => {
            this.$el.scrollIntoView('bottom');
        }, 300);
    }

    /**
     * Initialize editor
     *
     * @param selector selector for region with textarea
     */
    private initEditor(selector: string) {
        this.addRegion('editor', selector);

        const el = this.$(selector);
        el.prepend('<div class="js-editor-buttons"></div>');
        el.prepend('<div class="js-rgSmilies"></div>');

        // @todo
        // - change autosize on posting change
        // - attach to answering itself
        const editor = new EditorView({ el, model: this.model });

        this.showChildView('editor', editor);
        editor.render();
    }

    private _onFormReady() {
        this.initEditor('.js-editor');
        this.subjectView = new SubjectInputView({
            el: this.$('.postingform-subject-wrapper'),
            model: this.model,
        });

        /// read metadata
        const data = this.$('.js-data').data();
        const action = _.property(['meta', 'action'])(data);

        /// start edit countdown
        if (action === 'edit') {
            this.model.set('time', data.entry.time);
            const cd = new EditCountdownView({
                done: 'disable',
                el: this.getUI('btnPrimary'),
                startTime: data.entry.time,
            });
        }

        App.eventBus.trigger('change:DOM');
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
        App.eventBus.request('app:navigation:allow');

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
            this.trigger('answering:send:success');
            if (this.parentThreadline !== null) {
                this.parentThreadline.set('isInlineOpened', false);
            }
            App.eventBus.trigger('newEntry', {
                id: responseData.id,
                isNewToUser: true,
                pid: this.model.get('pid'),
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

    /**
     * Called when the posting model changes
     */
    private onAnswerModelChange() {
        /// warn user on input when navigating away
        const fields: string[] = ['subject', 'text'];
        const found = fields.find((field) => !!this.model.get(field));
        const state: string = found ? 'disallow' : 'allow';
        App.eventBus.request('app:navigation:' + state);
    }
}

export { AnsweringView };
