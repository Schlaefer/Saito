import { Collection, Model } from 'backbone';
import { CollectionView, View } from 'backbone.marionette';
import App from 'models/app';
import * as _ from 'underscore';

class SmiliesCollection extends Collection<Model> {
    public modelId(attributes: any) {
        // Collection is filled with all codes for all smilies.
        // "icon" is unique for smilies on all codes so only one smiley
        // per code is put into the collection.
        return attributes.icon;
    }
}

class SmiliesView extends View<Model> {
    public constructor(options: object = {}) {
        _.defaults(options, {
            events: {
                'click @ui.button': 'handleClick',
            },
            model: Model,
            tagName: 'span',
            template: _.template(`
<% if (type === 'font') { %>
    <button class="btn btn-text btn-smiley-font" type="button">
        <i class="saito-smiley-font saito-smiley-<%= icon %>"></i>
    </button>
<% } else { %>
    <button class="btn btn-text btn-smiley-image" type="button"
        style="background-image: url(<%= webroot %><%= theme %>/img/smilies/<%= icon %>);"
    >
</button>
<% } %>`),
            ui: {
                button: 'button',
            },
        });
        super(options);
    }

    public templateContext() {
        return {
            theme: App.settings.get('theme').toLowerCase(),
            webroot: App.settings.get('webroot'),
        };
    }

    private handleClick() {
        this.trigger('click:smiley', { code: this.model.get('code') });
    }
}

class SmiliesCollectionView extends CollectionView<Model, SmiliesView, SmiliesCollection> {
    public constructor(options: object = {}) {
        _.defaults(options, {
            childView: SmiliesView,
            childViewTriggers: {
                'click:smiley': 'click:smiley',
            },
            className: 'postingform-smilies',
            collection: new SmiliesCollection(),
        });
        super(options);
    }
}

export { SmiliesCollectionView };
