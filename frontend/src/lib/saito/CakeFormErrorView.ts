import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as _ from 'underscore';

class CakeFormErrorView extends View<Model> {

    public constructor(options: any = {}) {
        _.defaults(options, {
            template: _.noop,
        });

        super(options);
    }

    public onRender() {
        this.reset();

        this.collection.each((error) => {
            const element = error.get('meta').field;
            const msg = error.get('title');

            this.form(element, msg);
        });
    }

    /**
     * Render notification as form field error.
     */
    private form(element, msg) {
        const tpl = _.template('<div class="invalid-feedback"><%= message %></div>');
        this.$(element).addClass('is-invalid')
            .after(tpl({ message: msg }));
    }

    private reset() {
        this.$('.invalid-feedback').remove();
        this.$('.is-invalid').removeClass('is-invalid');
    }
}

export { CakeFormErrorView };
