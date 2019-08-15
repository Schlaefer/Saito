/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { View } from 'backbone.marionette';
import App from 'models/app';
import * as _ from 'underscore';
import { SpinnerView } from 'views/SpinnerView';
import AnswerModel from '../models/AnswerModel';
import PreviewModel from '../models/PreviewModel';

export default class PreviewView extends View<PreviewModel> {
    protected template;

    constructor(options: any = {}) {
        options = _.extend(options, {
            className: 'preview-wrapper',
            model: new PreviewModel(),
            modelEvents: {
                error: 'render',
                request: 'onRequest',
                sync: 'render',
            },
            regions: {
                preview: '.preview',
            },
            template: _.template(`
<div class="card mb-3">
    <div class="card-header flex-bar-header">
        <div class="first">
            <button class="js-btnPreviewClose close" type="button">
                <i class="saito-icon fa fa-close-widget"></i>
            </button>
        </div>
        <div class="middle"><h2><%= $.i18n.__('preview.t') %></h2></div>
        <div class="last"></div>
    </div>
    <div class="preview card-body">
        <%= html %>
    </div>
</div>
            `),
        });
        super(options);
    }

    public onHide() {
        this.$el.slideUp('fast');
    }

    public onShow(model: AnswerModel) {
        this.$el.slideDown('fast');

        this.model.save(
            model,
            {
                error: () => {
                    App.eventBus.trigger('notification', {
                        message: $.i18n.__('preview.e.generic'),
                        type: 'error',
                    });
                },
                success: (mode, response, options) => {
                    if ('errors' in response) {
                        this.trigger('answer:validation:error', response.errors);

                        return;
                    }

                    this.trigger('answer:validation:error');
                },
            },
        );
    }

    public onRender() {
        if (!this.model.get('html')) {
            return;
        }
    }

    private onRequest() {
        this.showChildView('preview', new SpinnerView());
    }

}
