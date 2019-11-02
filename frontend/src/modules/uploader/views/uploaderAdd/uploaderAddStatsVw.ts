/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import humanize from 'humanize';
import * as _ from 'underscore';

class StatsVw extends View<Model> {
    /**
     * Constructor
     * @param options Ma options
     */
    public constructor(options: any = {}) {
        _.defaults(options, {
            modelEvents: {
                'change:progress': 'onChangeProgress',
            },
            template: _.template(`
            <div class="m-1 text-monospace" style="font-size: 0.7rem;">
                <i class="fa fa-tachometer" aria-hidden="true"></i>
                <span class="js-speed">0 MB/s</span>
            </div>
            </div>
            `),
            ui: {
                speed: '.js-speed',
            },
        });
        super(...arguments);
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
        this.getUI('speed').html(humanize.filesize('' + bitrate) + '/s');

    }
}

export default StatsVw;
