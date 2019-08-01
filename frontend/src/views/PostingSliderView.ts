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
import { AnsweringView } from 'modules/answering/answering';
import AnswerModel from 'modules/answering/models/AnswerModel';
import * as _ from 'underscore';
import { SpinnerView } from 'views/SpinnerView';

/**
 * Slider beneath a posting which holds the answering form
 */
export default class Marionette extends View<Model> {
    public answeringForm: boolean;

    public parentThreadline;

    public constructor(options: any = {}) {
        _.defaults(options, {
            childViewEvents: {
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
    }

    public initialize(options) {
        this.answeringForm = false;
        this.parentThreadline = options.parentThreadline || null;

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

    private onChildviewAnsweringSendSuccess() {
        this.model.set({ isAnsweringFormShown: false });
    }

    /**
     * Loads the answering form
     */
    private loadAnsweringForm() {
        App.eventBus.request('app:autoreload:stop');
        // show spinner
        if (this.answeringForm === false) {
            this.showChildView('answerRg', new SpinnerView());
        }

        // slide down
        this.$el.slideDown('fast');

        if (this.answeringForm !== false) {
            return;
        }

        /// request form
        const requestUrl = App.settings.get('webroot') +
            'entries/add/' +
            (this.model.get('id') || '');

        $.ajax({
            // Don't append timestamp to requestUrl or Cake's SecurityComponent
            // will blackhole the ajax call in AnsweringView::_sendInline.
            cache: true,
            success: (data) => {
                const model = new AnswerModel({ pid: this.model.get('id') });
                const answeringForm = new AnsweringView({
                    model,
                    parentThreadline: this.parentThreadline,
                    template: _.template(data),
                });

                this.showChildView('answerRg', answeringForm);

                this.answeringForm = true;
            },
            url: requestUrl,
        });
    }

    private hideAnsweringForm() {
        this.$el.slideUp('fast', () => {
            App.eventBus.trigger('change:DOM');
        });
    }

    private hideAllAnsweringForms() {
        // we have #id problems with more than one markItUp on a page
        this.collection.forEach(function(posting) {
            if (posting.get('id') !== this.model.get('id')) {
                posting.set('isAnsweringFormShown', false);
            }
        }, this);
    }
}
