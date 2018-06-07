import * as Mn from 'backbone.marionette';
import * as $ from 'jquery';
import App from 'models/app';
import * as _ from 'underscore';
import AppView from 'views/app';
import * as Tpl from '../templates/bookmarkItemTpl.html';
import { CommentInputView } from './bookmarkCommentVw';

/**
 * Comment as text
 */
class CommentTextView extends Mn.View<any> {
    constructor(options) {
        options.template = _.template('<%- comment %>');
        options.className = 'm-1';
        super(options);
    }
}

/**
 * Bookmark Item View
 */
export default class extends Mn.View<any> {
    constructor(options: any = {}) {
        options = _.extend(options, {
            className: 'list-group-item flex-column align-items-start',
            events: {
                'click @ui.btnDelete': 'handleDelete',
                'click @ui.btnEdit': 'handleEdit',
                'click @ui.btnSave': 'handleSave',
            },
            regions: {
                rgComment: '.js-comment',
            },
            template: Tpl,
            ui: {
                btnDelete: '.js-delete',
                btnEdit: '.js-edit',
                btnSave: '.js-save',
                comment: '.js-comment',
            },
        });
        super(options);
    }

    public onRender() {
        this.showChildView('rgComment', new CommentTextView({ model: this.model }));
        const av = new AppView();
        av._initThreadLeafs(this.$('.threadLeaf'));
    }

    protected handleEdit() {
        this.showChildView('rgComment', new CommentInputView({ model: this.model }));
        this.getUI('btnEdit').hide();
        this.getUI('btnSave').show();
    }

    protected handleSave() {
        this._deactivateInteractions();
        this.model.save(null, {
            error: () => {
                this._activateInteractions();
                const notification = {
                    code: 1527271165,
                    message: $.i18n.__('bkm.save.failure'),
                    type: 'error',
                };
                App.eventBus.trigger('notification', notification);
            },
            success: () => {
                this.showChildView('rgComment', new CommentTextView({ model: this.model }));
                this._activateInteractions();
                this.getUI('btnSave').hide();
                this.getUI('btnEdit').show();
            },
        });
    }

    protected handleDelete() {
        this._deactivateInteractions();
        this.$el.hide('slide', null, 500);
        this.model.destroy({
            error: () => {
                this._activateInteractions();
                this.$el.show('slide', null, 500);
                const notification = {
                    code: 1527277946,
                    message: $.i18n.__('bkm.delete.failure'),
                    type: 'error',
                };
                App.eventBus.trigger('notification', notification);
            },
            wait: true,
        });
    }

    /**
     * Deactivates all interaction buttons
     */
    protected _deactivateInteractions() {
        this.getUI('btnDelete').attr('disabled', 'disabled');
        this.getUI('btnEdit').attr('disabled', 'disabled');
        this.getUI('btnSave').attr('disabled', 'disabled');
    }

    /**
     * Activates all interaction buttons
     */
    protected _activateInteractions() {
        this.getUI('btnDelete').removeAttr('disabled');
        this.getUI('btnEdit').removeAttr('disabled');
        this.getUI('btnSave').removeAttr('disabled');

    }
}
