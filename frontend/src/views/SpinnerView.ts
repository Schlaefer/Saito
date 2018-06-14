import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as  _ from 'underscore';

class SpinnerView extends View<Model> {
    public constructor(options: object = {}) {
        _.defaults(options, {
            template: _.template(`
<div class="spinner">
    <div class="progress">
        <div class="progress-bar progress-bar-striped progress-bar-animated"
            role="progressbar"
            style="width: 100%;"></div>
    </div>
</div>
            `),
            ui: {
                progress: '.progress',
            },
        });
        super(options);
    }

    public onRender() {
        const progress = this.getUI('progress');
        progress.css('visibility', 'hidden');
        _.delay(() => { progress.css('visibility', 'visible'); }, 1000);
    }
}

export { SpinnerView };
