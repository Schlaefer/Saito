import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import humanize from 'humanize';
import App from 'models/app';
import { defaults, template } from 'underscore';

class UploaderItemFooterVw extends View<Model> {
    public constructor(options: any = {}) {
        defaults(options, {
            className: 'imageUploader-card-details',
            events: {
                'click button': 'handleDelete',
            },
            template: template(`
                <h6 title="<%- title %>"><%- titleTrunc %></h6>
                <ul>
                    <li>
                        <i class="fa fa-calendar-o" ariad-hidden="true"></i>
                        <%- created %>
                    </li>
                    <li>
                        <i class="fa fa-floppy-o" ariad-hidden="true"></i>
                        <%- filesize %>
                    </li>
                </ul>
                <button class="btn btn-link btnUploadDelete" title="<%- $.i18n.__('upl.del.btn') %>">
                    <i class="fa fa-trash-o"></i>
                </button>
                `),
        });
        super(...arguments);
    }

    /**
     * deletes upload
     */
    private handleDelete(event) {
        event.preventDefault();

        this.model.destroy({
            error: (model, response) => {
                const msg = response.responseJSON.errors[0];
                App.eventBus.trigger('notification', { message: msg, type: 'error' });
            },
        });
    }

    private templateContext() {

        const trunc = (str: string, length: number, truncateStr?: string): string => {
            if (str.length <= length) {
                return str;
            }

            truncateStr = truncateStr || '…';

            return str.slice(0, length * 3 / 5)
                + truncateStr
                + str.slice(str.length - (length * 2 / 5), str.length);
        };

        return {
            created: new Date(this.model.get('created')).toDateString(),
            filesize: humanize.filesize(this.model.get('size')),
            titleTrunc: trunc(this.model.get('title'), 20),
        };
    }
}

export default UploaderItemFooterVw;
