import { View } from 'backbone.marionette';
import $ from 'jquery';
import App from 'models/app';
import _ from 'underscore';
import PostingModel from '../models/PostingMdl';

export default class extends View<PostingModel> {

    public constructor(options: any = {}) {
        _.defaults(options, {

            className: 'btn btn-link btn-solves',

            events: {
                click: '_onClick',
            },

            modelEvents: {
                'change:isSolves': '_toggle',
            },
            tagName: 'a',
            template: _.template('<i class="fa fa-badge-solves-o fa-lg"></i>'),
        });
        super(options);
    }

    public initialize() {
        if (!this._shouldRender()) {
            return;
        }
        this.$el.attr({
            href: '#',
            title: $.i18n.__('posting.helpful'),
        });
        this.render();
    }

    public onRender() {
        this._toggle();
    }

    private _shouldRender() {
        if (!App.currentUser.isLoggedIn()) {
            return false;
        }
        if (this.model.isRoot()) {
            return false;
        }
        if (this.model.get('rootEntryUserId') !== App.currentUser.get('id')) {
            return false;
        }
        return true;
    }

    private _onClick(event: Event) {
        event.preventDefault();
        this.model.toggle('isSolves');
    }

    private _toggle() {
        const $icon = this.$('i');
        const isSolves = this.model.get('isSolves');
        let html = '';

        if (isSolves) {
            $icon.addClass('solves-isSolved');
            $icon.removeClass('fa-badge-solves-o');
            $icon.addClass('fa-badge-solves');
            html = this.$el.html();
            $(html).removeClass('fa-lg');
        } else {
            $icon.removeClass('fa-badge-solves');
            $icon.addClass('fa-badge-solves-o');
            $icon.removeClass('solves-isSolved');
        }
        this._toggleGlobal(html);
    }

    /**
     * Sets other badges on the page, prominently in thread-line.
     *
     * @todo should be handled as state by global model for the entry
     *
     * @param html
     */
    private _toggleGlobal(html: string) {
        const $globalIconHook = $('.solves.' + this.model.get('id'));
        $globalIconHook.html(html);
    }
}
