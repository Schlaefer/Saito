import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import $ from 'jquery';
import App from 'models/app';
import EditCountdownView from 'modules/answering/buttons/EditCountdownBtnView';
import _ from 'underscore';
import BmBtn from 'views/postingActionBookmark';
import DelModal from 'views/postingActionDelete';
import SolvesBtn from 'views/postingActionSolves';

export default class extends View<Model> {
    private jsButtons: any[];

    public constructor(options: any = {}) {
        _.defaults(options, {
            events: {
                'click .js-btn-setAnsweringForm': 'onBtnAnswer',
                'click @ui.btnDelete': 'onBtnDelete',
                'click @ui.btnFixed': 'onToggleFixed',
                'click @ui.btnLocked': 'onToggleLocked',
            },
            ui: {
                btnDelete: '.js-delete',
                btnFixed: '.js-btn-toggle-fixed',
                btnLocked: '.js-btn-toggle-locked',
            },
        });

        super(options);

        this.jsButtons = [BmBtn, SolvesBtn];
        this._initFormElements();
        this.listenTo(this.model, 'change:isAnsweringFormShown', this._toggleAnsweringForm);
    }

    private _initFormElements() {
        _.each(this.jsButtons, (ElementView) => {
            this.$el.append(new ElementView({ model: this.model }).$el);
        });
        const $editButton = this.$('.js-btn-edit');
        if ($editButton.length > 0) {
            const editCountdown = new EditCountdownView({
                el: $editButton,
                startTime: this.model.get('time'),
            });
        }
    }

    private onBtnAnswer(event: Event) {
        event.preventDefault();
        this.model.set('isAnsweringFormShown', true);
    }

    /**
     * Delete posting button click
     */
    private onBtnDelete(event: Event) {
        const diag = new DelModal({ model: this.model }).render();
        event.preventDefault();
    }

    private onToggleFixed(event: Event) {
        event.preventDefault();
        this._sendToggle('fixed');
    }

    private onToggleLocked(event: Event) {
        event.preventDefault();
        this._sendToggle('locked');
    }

    // @todo move into model
    private _sendToggle(key: string) {
        const id = this.model.get('id');
        const webroot = App.settings.get('webroot');
        const url = webroot + 'entries/ajaxToggle/' + id + '/' + key;

        $.ajax({ url, cache: false })
            .done(() => { window.location.reload(true); });
    }

    private _toggleAnsweringForm() {
        if (this.model.get('isAnsweringFormShown')) {
            this.$el.slideUp('fast');
        } else {
            this.$el.slideDown('fast');
        }
    }

}
