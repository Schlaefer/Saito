import { View } from 'backbone.marionette';
import PostingCollection from 'collections/postings';
import $ from 'jquery';
import App from 'models/app';
import _ from 'underscore';
import { ThreadLineView } from 'views/ThreadLineView';
import AnswerModel from '../modules/answering/models/AnswerModel';
import { ThreadModel } from '../modules/thread/thread';

export default class extends View<ThreadModel> {
    private $rootUl!: JQuery;

    private $subThreadRootIl!: JQuery;

    private postings!: PostingCollection;

    public constructor(options: any = {}) {
        _.defaults(options, {
            className: 'threadBox',
            events: {
                'click .btn-threadCollapse': 'collapseThread',
            },
        });
        super(options);
    }

    public initialize(options: any) {
        this.postings = options.postings;

        this.$rootUl = this.$('ul.root');
        this.$subThreadRootIl = $(this.$rootUl.find('li:not(:first-child)')[0]);

        if (this.model.get('isThreadCollapsed')) {
            this.hide();
        } else {
            this.show();
        }

        if (!App.eventBus.request('app:localStorage:available')) {
            this._hideCollapseButton();
        }

        this.listenTo(App.eventBus, 'newEntry', this._showNewThreadLine);
        this.listenTo(this.model, 'change:isThreadCollapsed', this.toggleCollapseThread);
    }

    private _showNewThreadLine(model: AnswerModel) {
        let threadLine;
        // only append to the id it belongs to
        if (model.get('tid') !== this.model.get('id')) {
            return;
        }

        const leafData = {
            id: model.get('id'),
            isNewToUser: true,
        };

        threadLine = new ThreadLineView({
            collection: this.model.threadlines,
            leafData,
            postings: this.postings,
        });
        this._appendThreadlineToThread(model.get('pid') + '', threadLine.render().$el);
    }

    private _appendThreadlineToThread(pid: string, $el: JQuery) {
        const parent = this.$('.threadLeaf[data-id="' + pid + '"]');
        const existingSubthread = (parent.next().not('.js_threadline').find('ul:first'));
        if (existingSubthread.length === 0) {
            $el.wrap('<ul class="threadTree-node"></ul>')
                .parent()
                .wrap('<li></li>')
                .parent()
                .insertAfter(parent);
        } else {
            existingSubthread.append($el);
        }
    }

    private _hideCollapseButton() {
        this.$('.btn-threadCollapse').css('visibility', 'hidden');
    }

    private collapseThread(event: Event) {
        event.preventDefault();
        this.model.toggle('isThreadCollapsed');
        this.model.save();
    }

    private toggleCollapseThread(model: ThreadModel, isThreadCollapsed: boolean) {
        if (isThreadCollapsed) {
            this.slideUp();
        } else {
            this.slideDown();
        }
    }

    private slideUp() {
        this.$subThreadRootIl.slideUp(300);
        this.markHidden();
    }

    private slideDown() {
        this.$subThreadRootIl.slideDown(300);
        this.markShown();
        // 				$(this.el).find('.ico-threadOpen').removeClass('ico-threadOpen').addClass('ico-threadCollapse');
        // 				$(this.el).find('.btn-threadCollapse').html(this.l18n_threadCollapse);
    }

    private hide() {
        this.$subThreadRootIl.hide();
        this.markHidden();
    }

    private show() {
        this.$subThreadRootIl.show();
        this.markShown();
    }

    private markShown() {
        $(this.el).find('.fa-thread-closed').removeClass('fa-thread-closed').addClass('fa-thread-open');
    }

    private markHidden() {
        $(this.el).find('.fa-thread-open').removeClass('fa-thread-open').addClass('fa-thread-closed');
    }

}
