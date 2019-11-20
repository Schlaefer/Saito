import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import App from 'models/app';
import numeral from 'numeral';
import { defaults, template } from 'underscore';
import TextElipsisVw from 'views/TextElipsisVw';

class UploaderItemFooterVw extends View<Model> {
    public constructor(options: any = {}) {
        defaults(options, {
            className: 'imageUploader-card-details',
            events: {
                'click button': 'handleDelete',
            },
            regions: {
                nameRg: '.js-nameRg',
            },
            template: template(`
                <ul>
                    <li class="js-nameRg"></li>
                    <li>
                        <i class="fa fa-fw fa-calendar-o" ariad-hidden="true"></i>
                        <%- created %>
                    </li>
                    <li>
                        <i class="fa fa-fw fa-floppy-o" ariad-hidden="true"></i>
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

    public onRender() {
        const nameVw = new TextElipsisVw({model: this.model});
        this.showChildView('nameRg', nameVw);
    }

    /**
     * deletes upload
     */
    private handleDelete(event: Event) {
        event.preventDefault();

        this.model.destroy({
            error: (model, response: any) => {
                const msg = response.responseJSON.errors[0];
                App.eventBus.trigger('notification', { message: msg, type: 'error' });
            },
        });
    }

    private templateContext() {
        return {
            created: new Date(this.model.get('created'))
                .toLocaleDateString(App.settings.get('language')),
            filesize: numeral(this.model.get('size')).format('0.0 b'),
        };
    }
}

export default UploaderItemFooterVw;
