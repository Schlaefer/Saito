/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import { defaults, template } from 'underscore';
import EditCountdownView from './EditCountdownBtnView';

export default class SubmitButtonView extends View<Model> {
    constructor(options: any = {}) {
        options = defaults(options, {
            attributes: {
                tabindex: 4,
                type: 'button',
            },
            className: 'btn btn-primary',
            id: 'btn-primary',
            tagName: 'button',
            template: template(`
            <%- $.i18n.__('answer.btn.sbmt') %>
            `),
            triggers: {
                click: 'answer:send:submit',
            },
        });
        super(options);
    }

    public onRender() {
        if (this.model.get('time')) {
            const cd = new EditCountdownView({
                done: 'disable',
                el: this.$el,
                startTime: this.model.get('time'),
            });
        }
    }

    public enable() {
        this.$el.removeAttr('disabled');
        this.$('span.spinner-border').remove();
    }

    public disable() {
        this.$el.attr('disabled', 'disabled');
        this.$el.prepend('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    }
}
