import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import $ from 'jquery';
import App from 'models/app';
import _ from 'underscore';
import BookmarksCl from '../modules/bookmarks/collections/bookmarksCl';

export default class extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            className: 'btn btn-link',
            events: {
                click: 'handleClick',
            },
            modelEvents: {
                'change:isBookmarked': '_toggle',
            },
            tagName: 'btn',
            template: _.template('<i class="fa fa-lg"></i>'),
        });
        super(options);
    }

    public initialize() {
        if (!this._shouldRender()) {
            return;
        }
        this.$el.attr('href', '#');
        this.render();
    }

    public onRender() {
        this._toggle();
    }

    private _shouldRender() {
        if (!App.currentUser.isLoggedIn()) {
            return false;
        }
        return true;
    }

    private handleClick() {
        const success = (bookmarks: BookmarksCl) => {
            if (this.model.get('isBookmarked')) {
                const model = bookmarks.findWhere({ entry_id: this.model.get('id') });
                model.destroy();
                this.model.set('isBookmarked', false);
            } else {
                bookmarks.create({
                    entry_id: this.model.get('id'),
                    user_id: App.currentUser.get('id'),
                });
                this.model.set('isBookmarked', true);
            }

        };
        App.currentUser.getBookmarks({ success });
    }

    private _toggle() {
        const $icon = this.$('i');
        if (this.model.get('isBookmarked')) {
            $icon.removeClass('fa-bookmark-o');
            $icon.addClass('fa-bookmark');
            this.$el.attr('title', $.i18n.__('bmk.isBookmarked'));
        } else {
            $icon.removeClass('fa-bookmark');
            $icon.addClass('fa-bookmark-o');
            this.$el.attr('title', $.i18n.__('bmk.doBookmark'));
        }
    }
}
