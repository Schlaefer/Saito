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
import 'jQuery-tinyTimer/jquery.tinytimer';
import App from 'models/app';
import moment from 'moment';
import * as _ from 'underscore';

export default class EditCountdownView extends View<Model> {
    /**
     * time in seconds how long the timer should count down
     */
    private editEnd: number;

    private buttonText: string;

    private $countdownDummy: JQuery;

    private doneAction: string = 'remove';

    public constructor(options: any = {}) {
        super(options);
    }

    /**
     * Bb initialize
     *
     * @param options
     * - startTime: Date - start time
     */
    public initialize(options) {
        this.editEnd = moment(options.startTime).unix() + (App.settings.get('editPeriod') * 60);
        // this.editEnd = moment().unix() + 5 ; // debug

        if (moment().unix() > this.editEnd) {
            return;
        }
        if (options.done) {
            this.doneAction = options.done;
        }
        this.buttonText = this.$el.html();
        this.$countdownDummy = $('<span style="display: none;"></span>');
        this.$el.append(this.$countdownDummy);
        this._start();
    }

    private _setButtonText(timeText) {
        this.$el.text(this.buttonText + ' ' + timeText);
    }

    private _onTick(remaining) {
        if (remaining.m > 1 || (remaining.m === 1 && remaining.s > 30)) {
            remaining.m = remaining.m + 1;
            this._setButtonText('(' + remaining.m + ' min)');
        } else if (remaining.m === 1) {
            this._setButtonText('(' + remaining.m + ' min ' + remaining.s + ' s)');
        } else {
            this._setButtonText('(' + remaining.s + ' s)');
        }
    }

    private _onEnd() {
        switch (this.doneAction) {
            case 'disable':
                this._disable();
                break;
            default:
                this._remove();
        }
    }

    private _remove() {
        this.remove();
    }

    private _disable() {
        this.$el.attr('disabled', 'disabled');
    }

    private _start() {
        this.$countdownDummy.tinyTimer({
            format: '',
            onEnd: _.bind(this._onEnd, this),
            onTick: _.bind(this._onTick, this),
            to: moment.unix(this.editEnd).toDate(),
        });
    }

}
