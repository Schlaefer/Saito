import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as $ from 'jquery';
import App from 'models/app';
import * as _ from 'underscore';

class SaitoHelpView extends View<Model> {
    private isHelpShown: boolean;

    // cache for indicator-Views
    private popups: any[];

    // cache for DOM-elements
    private elements;

    /** handler string for a element with popup (.shp) */
    private elementName: string;

    private webroot: string;

    public constructor(options: any = {}) {
        _.defaults(options, {
            events: {
                click: 'toggle',
            },
            scope: 'body',
            template: _.noop,
            // target="_blank": don't lose text in ajax answering form by jumping away
            tpl: _.template(`
                <a class="btn btn-link" href="<%= webroot %>help/<%= id %>" target="_blank">
                    <i class="fa fa-question-circle"></i>
                </a>`),
        });
        super(options);
    }

    public initialize(options) {
        this.isHelpShown = false;
        this.popups = [];
        this.elements = null;

        this.listenTo(App.eventBus, 'change:DOM', this.onDomChange);
    }

    public onRender() {
        this.isHelpOnPage() ? this.$el.addClass('is-active') : this.$el.removeClass('is-active');
    }

    private toggle(): void {
        this.isHelpShown ? this.hide() : this.show();
    }

    private onDomChange(): void {
        this.reset()
            .render();
    }

    private isHelpOnPage(): boolean {
        return this.getElements().length > 0;
    }

    private reset(): this {
        this.hide();
        this.elements = null;
        this.popups = [];

        return this;
    }

    private getElements() {
        if (this.elements === null) {
            this.elements = $(this.getOption('scope'))
                .find(this.getOption('elementName'))
                .filter(this.isVisible);
        }
        return this.elements;
    }

    private isVisible(index, element): boolean {
        return $(element).filter(':visible').length > 0;
    }

    private show(): this {
        this.isHelpShown = true;
        if (!this.isHelpOnPage()) {
            return;
        }

        if (this.popups.length === 0) {
            const webroot = this.getOption('webroot');
            this.getElements().each((index, target) => {
                const $target = $(target);
                const id = $target.data('shpid');
                const content = this.getOption('tpl')({ id, webroot });
                const drop = $target.popover({
                    content,
                    html: true,
                    placement: 'bottom',
                });
                this.popups.push(drop);
            });
        }
        this.popups.forEach((element) => {
            element.popover('show');
        });
        return this;
    }

    private hide(): this {
        this.isHelpShown = false;
        this.popups.forEach((element) => {
            element.popover('hide');
        });
        return this;
    }
}

export { SaitoHelpView };
