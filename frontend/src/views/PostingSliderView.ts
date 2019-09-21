/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import App from 'models/app';
import AnsweringView from 'modules/answering/answering';
import AnswerModel from 'modules/answering/models/AnswerModel';
import * as _ from 'underscore';
import { PostingModel } from '../modules/posting/models/PostingModel';

/**
 * Slider beneath a posting which holds the answering form
 */
export default class Marionette extends View<Model> {
    public answeringForm: boolean;

    public parentThreadline: PostingModel | null;

    public constructor(options: any = {}) {
        _.defaults(options, {
            childViewEvents: {
                'answering:form:rendered': 'onAnsweringFormRendered',
                'answering:load:error': 'onChildviewAnsweringLoadError',
                'answering:send:success': 'onChildviewAnsweringSendSuccess',
            },
            events: {
                'click @ui.btnClose': 'onBtnClose',
            },
            regions: {
                answerRg: '.js-answer-wrapper',
            },
            template: _.template('<div class="js-answer-wrapper"></div>'),
            ui: {
                btnClose: '.js-btnAnsweringClose',
            },
        });

        super(options);

        this.answeringForm = false;
        this.parentThreadline = options.parentThreadline || null;
    }

    public initialize(options: any) {
        this.listenTo(this.model, 'change:isAnsweringFormShown', this.toggleAnsweringForm);
    }

    private onBtnClose() {
        this.model.set('isAnsweringFormShown', false);
    }

    private toggleAnsweringForm() {
        if (this.model.get('isAnsweringFormShown')) {
            this.hideAllAnsweringForms();
            this.loadAnsweringForm();
        } else {
            this.hideAnsweringForm();
        }
    }

    private onChildviewAnsweringSendSuccess(model: AnswerModel) {
        const id = model.get('id');

        /// Inline answer
        if (this.parentThreadline !== null) {
            this.model.set({ isAnsweringFormShown: false });

            this.parentThreadline.set('isInlineOpened', false);
            App.eventBus.trigger('newEntry', model);

            return;
        }

        /// redirect
        let action =  App.request.getAction();

        switch (action) {
            case ('mix'):
                break;
            default:
                action = 'view';
        }

        const root: string = App.settings.get('webroot');
        window.redirect(root + 'entries/' + action + '/' + id);
    }

    private onChildviewAnsweringLoadError() {
        this.model.set({ isAnsweringFormShown: false });
        const answerRg = this.getRegion('answerRg');
        answerRg.empty();
        this.answeringForm = false;
    }

    private onAnsweringFormRendered() {
        // wait for the slide-down to finish if still in progress
        _.delay(() => {
            this.$el.scrollIntoView('bottom');
        }, 350);
    }

    /**
     * Loads the answering form
     */
    private loadAnsweringForm() {
        App.eventBus.request('app:autoreload:stop');
        // slide down
        this.$el.slideDown('fast');

        if (this.answeringForm !== false) {
            return;
        }

        const model = new AnswerModel({ pid: this.model.get('id') });
        const answeringForm = new AnsweringView({ model });

        this.showChildView('answerRg', answeringForm);
        this.answeringForm = true;
    }

    private hideAnsweringForm() {
        this.$el.slideUp('fast', () => {
            App.eventBus.trigger('change:DOM');
        });
    }

    private hideAllAnsweringForms() {
        // we have #id problems with more than one markItUp on a page
        this.collection.forEach((posting) => {
            if (posting.get('id') !== this.model.get('id')) {
                posting.set('isAnsweringFormShown', false);
            }
        }, this);
    }
}
