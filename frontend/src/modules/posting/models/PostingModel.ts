import { Model } from 'backbone';
import * as $ from 'jquery';
import App from 'models/app';
import PostingMdl from 'models/PostingMdl';
import _ from 'underscore';

class PostingModel extends PostingMdl {
    public saitoUrl: string;

    public constructor(defaults: any = {}, options: any = {}) {
        _.defaults(defaults, {
            html: '',
            isAnsweringFormShown: false,
            isBookmarked: false,
        });
        super(defaults, options);

        // This model is currently not used for sending
        this.saitoUrl = 'foo';
    }

    public initialize() {
        this.listenTo(this, 'change:solves', this.onChangeSolves);
    }

    public fetchHtml(options: any) {
        $.ajax({
            dataType: 'html',
            success: (data) => {
                this.set('html', data);
                if (options && options.success) { options.success(); }
            },
            type: 'POST',
            url: App.settings.get('webroot') + 'entries/view/' + this.get('id'),
        });
    }

    private onChangeSolves() {
        $.ajax({
            dataType: 'json',
            type: 'POST',
            url: App.settings.get('webroot') + 'entries/solve/' + this.get('id'),
        });
    }
}

export { PostingModel };
