import EventBus from 'app/vent';
import { JsonApiModel } from 'lib/backbone/jsonApi';

import { View } from 'backbone.marionette';
import * as _ from 'underscore';
import { PostingRichtextView } from 'views/postingRichtext';
import { SpinnerView } from 'views/SpinnerView';

class PreviewModel extends JsonApiModel {
    public defaults() {
        return {
            html: null,
        };
    }

    public urlRoot = () => {
        return EventBus.vent.request('webroot') + 'preview/preview';
    }
}

class PreviewView extends View<PreviewModel> {
    protected template;

    constructor(options: any = {}) {
        options = _.extend(options, {
            className: 'card mb-3',
            modelEvents: {
                error: 'render',
                request: 'onRequest',
                sync: 'render',
            },
            regions: {
                postingBody: '.postingBody-text',
                preview: '.preview',
            },
            template: _.template(`
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
            `),
        });
        super(options);
    }

    public initialize() {
        this.model = new PreviewModel();
    }

    public onRender() {
        if (!this.model.get('html')) {
            return;
        }
        const a = new PostingRichtextView({ el: this.$('.richtext') });
        this.showChildView('postingBody', a);
        a.render();
    }

    private onRequest() {
        this.showChildView('preview', new SpinnerView());
    }
}

export { PreviewView };
