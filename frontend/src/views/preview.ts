import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import spinnerTpl from 'templates/spinner.html';
import * as _ from 'underscore';

export default class extends View<Model> {
    protected template;

    constructor(options: any = {}) {
        options = _.extend(options, {
            modelEvents: {
                'change:html': 'render',
            },
            template: _.template('<%= html %>'),
        });
        super(options);
    }

    public getTemplate() {
        if (this.model.get('html') === null) {
            return spinnerTpl;
        }
        return this.template;
    }
}
