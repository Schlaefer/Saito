import { Collection, Model } from 'backbone';
import { CollectionView, View } from 'backbone.marionette';
import App from 'models/app';
import * as _ from 'underscore';

class SmiliesCollection extends Collection<Model> {
    public modelId(attributes) {
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
    <button class="btn btn-text btn-smiley-font">
        <i class="saito-smiley-font saito-smiley-<%= icon %>"></i>
    </button>
<% } else { %>
    <button class="btn btn-text btn-smiley-image" style="
        background-image: url(<%= webroot %><%= theme %>/img/smilies/<%= icon %>);
    ">
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

    private handleClick(event) {
        event.preventDefault();
        // additional space to prevent smiley concatenation:
        // `:cry:` and `(-.-)zzZ` becomes `:cry:(-.-)zzZ` which outputs
        // smiley image for `:(`
        const text = ' ' + this.model.get('code');
        this.trigger('answering:insert', text, { focus: false });
    }
}

class SmiliesCollectionView extends CollectionView<Model, SmiliesView, SmiliesCollection> {
    public constructor(options: object = {}) {
        _.defaults(options, {
            childView: SmiliesView,
            childViewTriggers: {
                // pass insert on to answering form
                'answering:insert': 'answering:insert',
            },
            className: 'collapsablet panel-input flex-row flex-wrap',
            collection: new SmiliesCollection(),
        });
        super(options);
    }
}

export { SmiliesCollectionView };
