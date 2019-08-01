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
import * as _ from 'underscore';
import AnswerModel from './models/AnswerModel';

class SubjectInputModel extends Model {
    public defaults() {
        return {
            length: 0,
            max: null,
            remaining: null,
            value: '',
        };
    }

    /**
     * Backbone initializer
     */
    public initialize() {
        this.listenTo(this, 'change:value', this.updateMeta);
        const max = App.settings.get('subject_maxlength');
        if (!max) {
            throw new Error('No subject_maxlength in App settings.');
        }
        this.set('max', App.settings.get('subject_maxlength'));
        this.updateMeta();
    }

    /**
     * Recalculates the metadata if the subject textfield value changes
     */
    private updateMeta() {
        // Should be _.chars(subject) for counting multibyte chars as one char only, but
        // <input> maxlength attribute also counts all bytes in multibyte char.
        // This shortends the allowed subject by one byte-char per multibyte char,
        // but we can life with that.
        this.set('length', this.get('value').length);
        this.set('remaining', this.get('max') - this.get('length'));
        this.set('percentage', this.get('length') === 0 ? 0 : this.get('length') / this.get('max') * 100);
    }
}

enum ProgressBarState {
    notFull = 'bg-success',
    soonFull = 'bg-warning',
    full = 'bg-danger',
}

class SubjectInputView extends View<Model> {
    private stateModel: SubjectInputModel;

    public constructor(options: any = {}) {
        _.defaults(options, {
            events: {
                // 'input' doesnt catch a keypress when full and 'keypress'
                // doesn't catch paste/delete
                'input @ui.input': 'handleInput',
                'keypress @ui.input': 'handleMax',
            },
            modelEvents: {
                'change:value': 'update',
            },
            ui: {
                counter: '.postingform-subject-count',
                input: 'input',
                progressBar: '.js-progress',
            },
        });
        super(options);
    }

    public initialize() {
        this.stateModel = new SubjectInputModel();
        this.handleInput(); // initialize non-empty input field (edit posting)
        this.update();
    }

    private handleInput() {
        const subject = this.getUI('input').val();
        this.model.set('subject', subject);
        this.stateModel.set('value', subject);
    }

    private update() {
        this.updateCounter();
        this.updateProgressBar();
    }

    private updateCounter() {
        this.getUI('counter').html(this.stateModel.get('remaining'));
    }

    private updateProgressBar() {
        const $progress = this.getUI('progressBar');
        $progress.css('width', this.stateModel.get('percentage') + '%');

        const remaining = this.stateModel.get('remaining');
        if (remaining === 0) {
            this.handleMax();
            return;
        }
        const cssClass = (remaining < 20) ? ProgressBarState.soonFull : ProgressBarState.notFull;
        this.setProgress(cssClass);
    }

    private handleMax() {
        if (this.stateModel.get('percentage') !== 100) {
            return;
        }
        this.setProgress(ProgressBarState.full);
        _.delay(_.bind(this.setProgress, this), 250, ProgressBarState.soonFull);
    }

    private setProgress(cssClass: ProgressBarState) {
        const $progress = this.getUI('progressBar');
        Object.keys(ProgressBarState).forEach((key) => {
            $progress.removeClass(ProgressBarState[key]);
        });
        $progress.addClass(cssClass);
    }
}

export { SubjectInputModel, SubjectInputView };
