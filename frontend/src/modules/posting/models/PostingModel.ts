import { Model } from 'backbone';
import * as $ from 'jquery';
import App from 'models/app';
import _ from 'underscore';

class PostingModel extends Model {
    public defaults() {
        const posting = {
            text: null,
        };
        const meta = {
            html: '',
            isAnsweringFormShown: false,
            isBookmarked: false,
            isSolves: false,
        };

        return _.extend(posting, meta);
    }

    public initialize() {
        this.listenTo(this, 'change:isSolves', this.syncSolved);
    }

    public fetchHtml(options) {
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

    public isRoot(): boolean {
        const pid = this.get('pid');
        if (!_.isNumber(pid)) {
            throw new Error('pid is not a number.');
        }
        return pid === 0;
    }

    private syncSolved() {
        $.ajax({
            dataType: 'json',
            type: 'POST',
            url: App.settings.get('webroot') + 'entries/solve/' + this.get('id'),
        });
    }
}

export { PostingModel };
