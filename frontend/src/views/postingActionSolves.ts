import { View } from 'backbone.marionette';
import $ from 'jquery';
import App from 'models/app';
import _ from 'underscore';
import { isNumber } from 'util';
import PostingModel from '../models/PostingMdl';

export default class extends View<PostingModel> {

    public constructor(options: any = {}) {
        _.defaults(options, {

            className: 'btn btn-link btn-solves',

            events: {
                click: '_onClick',
            },

            modelEvents: {
                'change:solves': 'onChangeSolves',
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
        return this.model.get('showSolvedBtn');
    }

    private _onClick(event: Event) {
        event.preventDefault();
        const solves = this.model.get('solves');
        if (isNumber(solves) && solves > 0) {
            this.model.set('solves', 0);
        } else {
            this.model.set('solves', this.model.get('tid'));
        }
    }

    private onChangeSolves() {
        this._toggle();
    }

    private _toggle() {
        const $icon = this.$('i');
        const isSolves = this.model.get('solves');
        let html = '';

        if (isSolves === 0) {
            $icon.removeClass('fa-badge-solves');
            $icon.addClass('fa-badge-solves-o');
            $icon.removeClass('solves-isSolved');
        } else {
            $icon.addClass('solves-isSolved');
            $icon.removeClass('fa-badge-solves-o');
            $icon.addClass('fa-badge-solves');
            html = this.$el.html();
            $(html).removeClass('fa-lg');
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
