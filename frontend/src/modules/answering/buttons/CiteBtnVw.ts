/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as Radio from 'backbone.radio';
import * as _ from 'underscore';
import { unescapeHTML } from 'underscore.string';

export default class CiteBtn extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            className: 'form-group',
            events: {
                'click button': 'onCite',
            },
            template: _.template(`
                <button class="btn btn-link js-btnCite label" type="button">
                    <%- quoteSymbol %> <%- $.i18n.__('answer.cite.t') %>
                </button>
            `),
        });
        super(options);
    }

    /**
     * Quote parent posting
     *
     * @private
     */
    private onCite() {
        // Without defering a click on a selection which deselects (and should therefore be empty)
        // still holds the previously selected text.
        _.defer(() => {
            let text = window.getSelection().toString();
            if (text !== '') {
                text = this.model.get('quoteSymbol') + ' ' + text;
            } else {
                text = unescapeHTML(this.model.get('text'));
            }

            Radio.channel('editor').request('insert:text', text);
        });
    }
}
