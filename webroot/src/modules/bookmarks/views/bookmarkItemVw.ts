import * as $ from 'jquery';
import * as _ from 'underscore';
import * as Mn from 'backbone.marionette';
import App from 'models/app';
import * as Tpl from '../templates/bookmarkItemTpl.html';
import AppView from 'views/app';

/**
 * Comment as text
 */
class CommentTextView extends Mn.View<any> {
    constructor(options) {
        options.template = _.template('<%- comment %>');
        options.className = 'm-1';
        super(options)
    };
};

/**
 * Comment as input
 */
class CommentInputView extends Mn.View<any> {
    constructor(options) {
        options.template = _.template('<input type="text" value="<%- comment %>">');
        options.className = 'm-1';
        options.ui = {
            text: 'input',
        };
        options.events = {
            'keyup @ui.text': 'handleKeypress',
        };
        super(options);
    };
    public onRender() {
        this.getUI('text').focus();
    };
    protected handleKeypress(event) {
        event.preventDefault();
        this.model.set('comment', this.getUI('text').val());
    };
};

/**
 * Bookmark Item View
 */
export default class extends Mn.View<any> {
    constructor(options: any = {}) {
        options = _.extend(options, {
            className: 'list-group-item flex-column align-items-start',
            regions: {
                rgComment: '.js-comment',
            },
            ui: {
                btnDelete: '.js-delete',
                btnEdit: '.js-edit',
                btnSave: '.js-save',
                comment: '.js-comment',
            },
            events: {
                'click @ui.btnDelete': 'handleDelete',
                'click @ui.btnEdit': 'handleEdit',
                'click @ui.btnSave': 'handleSave',
            },
            template: Tpl,
        });
        super(options);
    };

    public onRender() {
        this.showChildView('rgComment', new CommentTextView({ model: this.model }));
        const av = new AppView();
        av._initThreadLeafs(this.$('.threadLeaf'));
    };

    protected handleEdit() {
        this.showChildView('rgComment', new CommentInputView({ model: this.model }));
        this.getUI('btnEdit').hide();
        this.getUI('btnSave').show();
    };

    protected handleSave() {
        this._deactivateInteractions();
        this.model.save(null, {
            success: () => {
                this.showChildView('rgComment', new CommentTextView({ model: this.model }));
                this._activateInteractions();
                this.getUI('btnSave').hide();
                this.getUI('btnEdit').show();
            },
            error: () => {
                this._activateInteractions();
                const notification = {
                    message: $.i18n.__('bkm.save.failure'),
                    code: 1527271165,
                    type: 'error',
                };
                App.eventBus.trigger('notification', notification);
            },
        });
    };

    protected handleDelete() {
        this._deactivateInteractions();
        this.$el.hide('slide', null, 500);
        this.model.destroy({
            wait: true,
            error: () => {
                this._activateInteractions();
                this.$el.show('slide', null, 500);
                const notification = {
                    message: $.i18n.__('bkm.delete.failure'),
                    code: 1527277946,
                    type: 'error',
                };
                App.eventBus.trigger('notification', notification);
            },
        });
    };

    /**
     * Deactivates all interaction buttons
     */
    protected _deactivateInteractions() {
        this.getUI('btnDelete').attr('disabled', 'disabled');
        this.getUI('btnEdit').attr('disabled', 'disabled');
        this.getUI('btnSave').attr('disabled', 'disabled');
    };

    /**
     * Activates all interaction buttons
     */
    protected _activateInteractions() {
        this.getUI('btnDelete').removeAttr('disabled');
        this.getUI('btnEdit').removeAttr('disabled');
        this.getUI('btnSave').removeAttr('disabled');

    };
}
