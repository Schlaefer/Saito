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

class ProgressBarVw extends View<Model> {
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
                <div class="progress">
                    <div class="progress-bar"
                        role="progressbar"
                        style="width: <%- progress %>%;">
                    </div>
                </div>
            `),
            ui: {
                bar: '.progress-bar',
            },
        });
        super(...arguments);
    }

    /**
     * Callback when progress changes to update progress-bar.
     *
     * @param model This view's model
     * @param value The progress between 0 and 100 percent
     */
    protected onChangeProgress(model: Model, value: number) {
        this.getUI('bar').css('width', value + '%');
    }
}

export default ProgressBarVw;
