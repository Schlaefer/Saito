import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import $ from 'jquery';
import _ from 'underscore';

class GeshiModel extends Model {
    public defaults() {
        return {
            isPlaintext: false,
        };
    }
}

class GeshiView extends View<GeshiModel> {
    public block!: JQuery;
    public htmlText!: string | undefined;
    public plainText!: string | undefined;

    public constructor(options: any = {}) {
        _.defaults(options, {
            events: {
                'click .geshi-plain-text': 'togglePlaintext',
            },
            model: new GeshiModel(),
            template: _.noop,
        });
        super(options);
    }

    public initialize() {
        this.model = new GeshiModel();
        this.block = this.$('.geshi-plain-text').next();

        this.setPlaintextButton();

        this.listenTo(this.model, 'change', this.handleSwitch);
    }

    public handleSwitch() {
        this.setPlaintextButton();
        this.extractPlaintext();
        this.renderText();
        return this;
    }

    private setPlaintextButton() {
        const icon = this.model.get('isPlaintext') ? 'fa-list-ol' : 'fa-align-justify';
        this.$('.geshi-plain-text').html('<i class="fa ' + icon + '"></i>');
    }

    private togglePlaintext(event: Event) {
        event.preventDefault();
        this.model.set('isPlaintext', !this.model.get('isPlaintext'));
    }

    private extractPlaintext() {
        if (this.plainText !== undefined) {
            return;
        }
        this.htmlText = this.block.html();
        if (navigator.appName === 'Microsoft Internet Explorer') {
            this.htmlText = this.htmlText.replace(/\n\r/g, '+');
            this.plainText = $(this.htmlText).text().replace(/\+\+/g, '\r');
        } else {
            this.plainText = this.block.text().replace(/code /g, 'code \n');
        }
    }

    private renderText() {
        if (this.model.get('isPlaintext') && this.plainText) {
            this.block.text(this.plainText).wrapInner('<pre class="geshi-plain code"></pre>');
        } else if (this.htmlText) {
            this.block.html(this.htmlText);
        }
    }
}

export { GeshiView };
