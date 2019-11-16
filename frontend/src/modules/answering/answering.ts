/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as $ from 'jquery';
import 'jquery-textrange';
import CakeFormErrorView from 'lib/saito/CakeFormErrorView';
import 'lib/saito/jquery.scrollIntoView';
import App from 'models/app';
import * as _ from 'underscore';
import { SpinnerView } from 'views/SpinnerView';
import { NotificationType } from '../notification/notification';
import CiteBtnVw from './buttons/CiteBtnVw';
import SubmitButtonVw from './buttons/SubmitButtonView';
import { DraftModel, DraftView } from './Draft';
import { EditorView } from './editor/EditorView';
import { MetaModel } from './Meta';
import AnswerModel from './models/AnswerModel';
import CategorySelect from './views/CategorySelectVw';
import PreviewView from './views/PreviewVw';
import SubjectInputVw from './views/SubjectInputVw';

export default class AnsweringView extends View<AnswerModel> {
    private errorVw!: View<Model>;

    private loaded: boolean;

    private sendInProgress: boolean;

    private metaModel: MetaModel;

    public constructor(options: any = {}) {
        _.defaults(options, {
            childViewEvents: {
                'answer:send:submit': 'onSubmit',
                'answer:validation:error': 'onAnswerValidationError',
            },
            events: {
                'click .js-btnCite': '_handleCite',
                'click .js-btnPreview': 'showPreview',
                'click .js-btnPreviewClose': 'closePreview',
            },
            meta: new MetaModel(),
            model: new AnswerModel(),
            modelEvents: {
                change: 'onAnswerModelChange',
            },
            regions: {
                category: '.js-category',
                cite: '.js-cite',
                drafts: '.js-draft',
                editor: '.js-editor',
                preview: '.js-preview',
                spinner: '.js-spinner',
                subject: '.js-subject',
                submitBtn: '.js-btn-primary',
            },
            template: _.template(`
<div class="entry add">
    <div class="js-preview"></div>
    <div class="postingform card">
        <% if (pid) { %>
            <div class="card-header">
                    <div id="" class="flex-bar-header panel-heading">
                        <div class="first">
                            <button class="js-btnAnsweringClose close" type="button">
                                <i class="saito-icon fa fa-close-widget"></i>
                            </button>
                        </div>
                        <div class="middle"><h2><%- $.i18n.__('answer.reply.t') %></h2></div>
                        <div class="last"></div>
                    </div>
            </div>
        <% } %>
        <div class="card-body" style="position: relative;">
            <div class='js-spinner'></div>
            <form method="post" accept-charset="utf-8" id="EntryAddForm" autocomplete="off" style="display: none;">
                <div class="js-category"></div>
                <div class="js-subject"></div>
                <div class="js-editor"></div>
                <div class="postingform-buttons">
                    <div class="first">
                        <div class="form-group">
                            <div style="display: inline-block;" class="js-btn-primary"></div>
                            <button class="js-btnPreview btn btn-secondary" tabindex="5" type="button">
                                <%- $.i18n.__('answer.btn.preview') %>
                            </button>
                        </div>
                    </div>
                    <div class="middle">
                        <div class='js-cite'></div>
                    </div>
                    <div class="last"></div>
                </div>
                <div class="postingform-info">
                    <span class="postingform-info-editor"></span>
                    &nbsp;
                    <span class='js-draft'></span>
                </div>
            </form>
        </div>
    </div>
</div>
            `),
            ui: {
                draft: 'postingform-info-draft',
                form: 'form',
                info: '.postingform-info-editor',
                last: '.last',
            },
        });

        super(options);

        this.loaded = false;
        this.sendInProgress = false;
        this.metaModel = options.meta;
    }

    public initialize(options: any) {
        /// init Cake Form Error View
        this.errorVw = new CakeFormErrorView({ el: this.$el });
    }

    public onBeforeDestroy() {
        this.errorVw.destroy();
    }

    public onRender() {
        if (this.loaded) {
            return;
        }

        this.showChildView('spinner', new SpinnerView());

        const success = (model: MetaModel) => this.triggerMethod('answering:load:success', model);

        if (this.metaModel.isEmpty()) {
            // Send id to identify an edit of that posting.
            this.metaModel.set('id', this.model.get('id'));
            this.metaModel.fetch({
                // Send pid to find potential drafts.
                data: { pid: this.model.get('pid') },
                error: () => this.triggerMethod('answering:load:error'),
                success,
            });

            return;
        }

        success(this.metaModel);
    }

    /**
     * Handles successful form data load and builds the form in regions
     *
     * @param data request data
     */
    private onAnsweringLoadSuccess(model: MetaModel) {
        this.loaded = true;
        const data = model.attributes;

        this.model.set(data.posting);

        /// init drafts (no drafts for edits)
        const isEdit: boolean = !!this.model.get('id');
        if (!isEdit) {
            const draftModel = new DraftModel({ pid: this.model.get('pid') });

            // update draft when answering input changes
            draftModel.listenTo(this.model, 'change', () => {
                draftModel.set(this.model.pick('subject', 'text'));
            });

            if (data.draft) {
                const { id, subject, text} = data.draft;
                this.model.set({ subject, text });
                // draft model is going to update an existing draft
                draftModel.set('id', id);
            }

            this.showChildView('drafts', new DraftView({ model: draftModel }));
        }

        /// init submit-button
        this.showChildView('submitBtn', new SubmitButtonVw({ model: this.model }));

        /// init category select
        if (this.model.isRoot()) {
            this.showChildView('category', new CategorySelect({
                autoselectCategory: data.meta.autoselectCategory,
                categories: data.editor.categories,
                model: this.model,
            }));
        }

        /// init editor textfield
        this.showChildView(
            'editor',
            new EditorView({
                buttons: data.editor.buttons,
                model: this.model,
                smilies: data.editor.smilies,
            }),
        );

        /// init preview
        const previewView = new PreviewView();
        this.showChildView('preview', previewView);
        previewView.listenTo(this, 'answer:preview:show', previewView.onShow);
        previewView.listenTo(this, 'answer:preview:hide', previewView.onHide);

        /// init subject-field
        const subjectView = new SubjectInputVw({
            max: data.meta.subjectMaxLength,
            model: this.model,
            placeholder: data.meta.subject || $.i18n.__('answer.subject.t'),
        });
        this.showChildView('subject', subjectView);
        subjectView.listenTo(this, 'answering:form:rendered', subjectView.focus);

        /// init cite button
        if (!this.model.isRoot() && data.meta.text) {
            const citeModel = new Model({
                quoteSymbol: data.meta.quoteSymbol,
                text: data.meta.text,
            });
            this.showChildView('cite', new CiteBtnVw({ model: citeModel }));
        }

        /// set editor-info
        if (data.meta.info) {
            this.getUI('info').prepend(data.meta.info);
        }

        /// add additional elements to "last" column in footer
        if (data.meta.last) {
            this.getUI('last').prepend(data.meta.last);
        }

        this.detachChildView('spinner');
        this.getUI('form').show();

        this.triggerMethod('answering:form:rendered', data);
        App.eventBus.trigger('change:DOM');

        // Uploader debug
        // this.$('.btn-markup-Upload').click();
    }

    /**
     * Handles error if loading of metadata for form failes
     */
    private onAnsweringLoadError() {
        App.eventBus.trigger('notification', {
            message: $.i18n.__('api.generic.e.exp'),
            title: $.i18n.__('api.generic.e.t'),
            type: NotificationType.error,
        });
    }

    /**
     * Submit data to server
     */
    private onSubmit() {
        if (this.sendInProgress) {
            return;
        }

        this.disableAnswering();

        if (!this.checkFormValidity()) {
            this.enableAnswering();

            return;
        }

        // @todo @sm more concreate timeout handling
        this.model.save(null, {
            error: () => this.triggerMethod('answering:send:error'),
            success: (model, response, options) => {
                ///  handled errors
                if ('errors' in response) {
                    this.triggerMethod('answer:validation:error', response.errors);

                    return;
                }

                /// success
                App.eventBus.request('app:navigation:allow');
                this.trigger('answering:send:success', model);
            },
        });
    }

    private onAnsweringSendError() {
        App.eventBus.trigger('notification', {
            message: $.i18n.__('api.generic.e.exp'),
            title: $.i18n.__('answer.submit.e.t'),
            type: NotificationType.error,
        });

        this.enableAnswering();
    }

    /**
     * Display validation errors
     *
     * @param errors errors object with validation errors from server
     */
    private onAnswerValidationError(errors?: any) {
        this.errorVw.collection.reset(errors);
        this.errorVw.render();

        this.enableAnswering();
    }

    /**
     * Check form validity and trigger error messages in browser
     */
    private checkFormValidity(): boolean {
        const form: HTMLFormElement & any = this.getUI('form')[0];

        if (form.checkValidity()) {
            return true;
        }

        /// trigger browser native validation messages to be displayed to the user
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

    /**
     * Initiates the preview
     */
    private showPreview() {
        if (!(this.checkFormValidity())) {
            return;
        }

        this.triggerMethod('answer:preview:show', this.model);
    }

    /**
     * Closes the preview
     */
    private closePreview() {
        this.triggerMethod('answer:preview:hide');
    }

    /**
     * Enables the answering form for the user
     *
     * Usually after a form-submit failed due to validation-/request-error
     */
    private enableAnswering() {
        const submitBtn = this.getChildView('submitBtn') as SubmitButtonVw;
        submitBtn.enable();

        const drafts = this.getChildView('drafts') as DraftView;
        if (drafts) {
            drafts.enable();
        }

        this.sendInProgress = false;
    }

    /**
     * Disables answering form for the user
     *
     * Usually after the form is submitting and the request is in progress
     */
    private disableAnswering() {
        this.sendInProgress = true;

        const submitBtn = this.getChildView('submitBtn') as SubmitButtonVw;
        submitBtn.disable();

        const drafts = this.getChildView('drafts') as DraftView;
        if (drafts) {
            drafts.disable();
        }
    }

}
