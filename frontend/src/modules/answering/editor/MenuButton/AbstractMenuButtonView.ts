import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as Radio from 'backbone.radio';
import * as _ from 'underscore';

abstract class AbstractMenuButtonView extends View<Model> {
    protected channel: Radio.Channel;

    public constructor(options: any = {}) {
        _.defaults(options, {
            className: 'markupButtonItem',
            events: {
                'click @ui.button': 'handleButton',
            },
            tagName: 'li',
            ui: {
                button: 'button',
            },
        });
        super(options);

        this.channel = Radio.channel('editor');
    }

    public getTemplate() {
        return _.template(`
        <button class="markupButton <%= className %>" title="<%= title %>" type="button">
            <%= name %>
        </button>
        `);
    }

    protected abstract handleButton();
}

export { AbstractMenuButtonView };
