/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as  _ from 'underscore';

class TextElipsisVw extends View<Model> {
    public constructor(options: {field?: string, model: Model, modelEvents?: any}) {
        _.defaults(options, {
            cutOff: 5,
            field: 'title',
            modelEvents: {},
            template: _.template(`
            <div class="textElipsis-wrap" title="<%- title %>">
                <div class="textElipsis-start">
                    <i class="fa fa-fw fa-file-o" aria-hidden="true"></i>
                    <%- titleStart %></div><div class="textElipsis-end"><%- titleTrunc %></div>
            </div>
            `),
        });

        options.modelEvents['change:' + options.field] = 'render';

        super(options);
    }

    public templateContext() {
        const sliceStart = (str: string, length: number): string => {
            return str.slice(0, str.length - length);
        };

        const sliceEnd = (str: string, length: number): string => {
            return str.slice(str.length - length, str.length);
        };

        const cutOff = this.getOption('cutOff');
        const field = this.getOption('field');
        const text = this.model.get(field);

        return {
            title: text || '',
            titleStart: text ? sliceStart(text, cutOff) : '',
            titleTrunc: text ? sliceEnd(text, cutOff) : '',
        };
    }
}

export default TextElipsisVw;
