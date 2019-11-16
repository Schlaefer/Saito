/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as _ from 'underscore';
import moment from 'moment';
import numeral from 'numeral';

class StatsVw extends View<Model> {
    /**
     * Constructor
     * @param options Ma options
     */
    public constructor(options: any = {}) {
        _.defaults(options, {
            classTag: 'imageUploader-card-details',
            modelEvents: {
                'change:progress': 'onChangeProgress',
                'change:fileToUpload': 'onChangeFile'
            },
            template: _.template(`
            <ul>
                <li>
                    <i class="fa fa-fw fa-tachometer" aria-hidden="true"></i>
                    <span class="js-speed"><%- $.i18n.__('upl.add.speed.t') %></span>
                </li>
                <li>
                    <i class="fa fa-fw fa-hourglass-end" aria-hidden="true"></i>
                    <span class="js-time"><%- $.i18n.__('upl.add.remaining.t') %></span>
                </li>
                <li>
                    <i class="fa fa-fw fa-floppy-o" aria-hidden="true"></i>
                    <span class="js-total"><%- $.i18n.__('upl.add.size.t') %></span>
                </li>
            </ul>
            `),
            ui: {
                speed: '.js-speed',
                total: '.js-total',
                time: '.js-time',
            },
        });
        super(...arguments);
    }

    protected onChangeFile(model: Model, file: File) {
        const calc = $.i18n.__('upl.add.calc');
        this.getUI('speed').html(calc);
        this.getUI('time').html(calc);

        let total = '';
        if (file) {
            total = file.size as unknown as string;
        }
        this.getUI('total').html(numeral(total).format('0 b'));
    }

    /**
     * Callback when progress changes to update progress-bar.
     * @param model This view's model
     * @param value The progress between 0 and 100 percent
     */
    protected onChangeProgress(model: Model, value: number) {
        let bitrate = 0;
        const now =  (new Date()).getTime() / 1000;
        const period = now - model.get('start');
        if (period !== 0) {
            bitrate = Math.round(model.get('loaded') / period);
        }
        this.getUI('speed').html(numeral(bitrate).format('0 b') + '/s');


        const total = model.get('fileToUpload').size;
        let remaining = Math.floor((total - model.get('loaded')) / bitrate) + 1;
        remaining = remaining >= 0 ? remaining : 0;
        this.getUI('time').html(moment().add(remaining, 'seconds').fromNow(true));
    }
}

export default StatsVw;
