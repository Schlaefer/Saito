import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import spinnerTpl from 'templates/spinner.html';
import * as _ from 'underscore';

export default class extends View<Model> {
    protected template;

    constructor(options: any = {}) {
        options = _.extend(options, {
            className: 'list-group-item flex-column align-items-start',
            events: {
                'click @ui.btnDelete': 'handleDelete',
                'click @ui.btnEdit': 'handleEdit',
                'click @ui.btnSave': 'handleSave',
            },
            modelEvents: {
                'change:fetchingData': '_fetchingData',
                'change:rendered': 'render',
            },
            regions: {
                rgComment: '.js-comment',
            },
            template: _.template('<%= rendered %>'),
            ui: {
                btnDelete: '.js-delete',
                btnEdit: '.js-edit',
                btnSave: '.js-save',
                comment: '.js-comment',
            },
        });
        super(options);
    }

    public getTemplate() {
        if (this.model.get('fetchingData')) {
            return spinnerTpl;
        }
        return this.template;
    }

    private _fetchingData() {
        if (this.model.get('fetchingData')) {
            this.render();
        }
    }
}
