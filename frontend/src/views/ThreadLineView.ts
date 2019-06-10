import { View } from 'backbone.marionette';
import * as PostingCollection from 'collections/postings';
import $ from 'jquery';
import 'lib/saito/jquery.scrollIntoView';
import App from 'models/app';
import ThreadLineModel from 'models/threadline';
import { PostingModel } from 'modules/posting/models/PostingModel';
import { PostingLayoutView } from 'modules/posting/postingLayout';
import threadlineSpinnerTpl from 'templates/threadline-spinner.html';
import _ from 'underscore';
import { SpinnerView } from 'views/SpinnerView';

class ThreadLineView extends View<PostingModel> {
    private postings: PostingCollection;

    private postingModel: PostingModel;

    private spinnerTpl;

    public constructor(options: any = {}) {
        _.defaults(options, {
            className: 'threadLeaf',
            events: {
                'click @ui.btnClose': 'toggleInlineOpen',
                'click @ui.btnShowThread': 'toggleInlineOpen',
                'click @ui.linkShowThread': 'toggleInlineOpenFromLink',
            },
            /** Posting collection */
            postings: null,
            spinnerTpl: threadlineSpinnerTpl,
            tagName: 'li',
            template: _.noop,
            ui: {
                btnClose: '.js-btn-strip',
                btnShowThread: '.btn_show_thread',
                linkShowThread: '.link_show_thread',
            },
        });
        super(options);
    }

    public initialize(options) {
        this.postings = options.postings;

        this.spinnerTpl = options.spinnerTpl;

        this.model = new ThreadLineModel({
            id: options.leafData.id,
            isNewToUser: options.leafData.isNewToUser,
        });
        if (options.el === undefined) {
            this.model.fetch();
        } else {
            this.model.set({ html: this.el });
        }
        this.collection.add(this.model, { silent: true });
        this.attributes = { 'data-id': options.id };

        this.listenTo(this.model, 'change:isInlineOpened', this._toggleInlineOpened);
        this.listenTo(this.model, 'change:html', this.render);
    }

    public onRender() {
        const newHtml = this.model.get('html');
        if (newHtml.length > 0) {
            const $oldEl = this.$el;
            const $newEl = $(this.model.get('html'));
            this.setElement($newEl);
            $oldEl.replaceWith($newEl);
        }
    }

    private toggleInlineOpenFromLink(event) {
        if (this.model.get('isAlwaysShownInline')) {
            this.toggleInlineOpen(event);
        }
    }

    /**
     * shows and hides the element that contains an inline posting
     */
    private toggleInlineOpen(event) {
        event.preventDefault();
        this.model.toggle('isInlineOpened');
    }

    private _toggleInlineOpened(model, isInlineOpened) {
        if (!isInlineOpened) {
            this._closeInlineView();
            return;
        }
        if (!this.model.get('isContentLoaded')) {
            this.$('.threadLine').after(this.spinnerTpl);
            this._insertContent();
            this.model.set('isContentLoaded', true);
        }
        this._showInlineView();
    }

    private _insertContent() {
        if (!this.hasRegion('threadInlineSlider')) {
            this.addRegion('threadInlineSlider', '.threadInline-slider');
        }
        this.showChildView('threadInlineSlider', new SpinnerView());

        const id = this.model.get('id');
        this.postingModel = new PostingModel({ id });
        this.postingModel.fetchHtml({
            success: () => {
                const $childEl = this.$('.threadInline-slider');
                $childEl.html(this.postingModel.get('html'));
                const postingView = new PostingLayoutView({
                    collection: this.postings,
                    el: this.$('.threadInline-slider'),
                    model: this.postingModel,
                    parentThreadline: this.model,
                }).render();
                this.showChildView('threadInlineSlider', postingView);
                this.postings.add(this.postingModel);
            },
        });
    }

    private _showInlineView() {
        const postShow = () => {
            const shouldScrollOnInlineOpen = this.model.get('shouldScrollOnInlineOpen');
            if (shouldScrollOnInlineOpen) {
                if (this.$el.scrollIntoView('isInView') === false) {
                    this.$el.scrollIntoView('bottom');
                }
            } else {
                // @bogus What is this about? - Schlaefer 2019-06-04
                this.model.set('shouldScrollOnInlineOpen', true);
            }
        };

        this.$('.threadLine').fadeOut(100, () => {
            // performance: show() instead slide()
            // this.$('.js-thread_inline.' + id).slideDown(0,
            this.$('.js-thread_inline').show(0, postShow);
        });
    }

    private _closeInlineView() {
        App.eventBus.trigger('change:DOM');
        this.$('.js-thread_inline').hide(0, () => {
            this.$el.find('.threadLine').slideDown();
            this._scrollLineIntoView();
        });
    }

    /**
     * if the line is not in the browser windows at the moment
     * scroll to that line and highlight it
     */
    private _scrollLineIntoView() {
        const threadline = this.$('.threadLine');
        if (threadline.scrollIntoView('isInView')) {
            return;
        }
        threadline.scrollIntoView('top').effect('highlight', { times: 1 }, 3000);
    }

}

export { ThreadLineView };
