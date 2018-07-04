import * as autosize from 'autosize';
import { Collection, Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as Radio from 'backbone.radio';
import 'jquery-textrange';
import * as _ from 'underscore';
import { SmiliesCollectionView } from './Menu/Smilies';
import { MenuButtonBarView } from './MenuButtonBarView';

class EditorView extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            channelName: 'editor',
            regions: {
                buttons: '.js-editor-buttons',
                smilies: '.js-rgSmilies',
            },
            template: _.noop,
            ui: {
                text: 'textarea',
            },
        });
        super(options);
    }

    public initialize() {
        const channel = Radio.channel('editor');
        channel.reply('insert:text', this.insertText, this);
        channel.reply('selected:text', this.selectedText, this);
        channel.reply('wrap:text', this.wrapText, this);
        this.listenTo(channel, 'smilies:toggle', this.toggleSmilies);
    }

    public onRender() {
        this.addMenuButtons();
        autosize(this.getUI('text'));
        this.postContentChanged();
    }

    public onDestroy() {
        autosize.destroy(this.getUI('text'));
    }

    public insertText(text: string, cursor?: number) {
        this.getUI('text').textrange({ method: 'replace', nofocus: true }, text);
        if (!cursor) {
            cursor = String(this.getUI('text').val()).length;
        }
        this.getUI('text').textrange('setcursor', cursor);
        this.postContentChanged();
    }

    public wrapText(pre?: string, post?: string) {
        const current = this.getUI('text').textrange('get');
        let text: string = current.text;
        let cursor: number = current.start;

        if (pre) {
            text = pre + text;
            // move cursors after opening tag (assuming that tag ist empty)
            cursor += pre.length;
        }
        if (post) {
            text += post;
            if (current.length) {
                // move cursor after closing tag if text was selected and is filling tag
                cursor += current.length + post.length;
            }
        }
        this.insertText(text, cursor);
    }

    public selectedText() {
        return this.getUI('text').textrange('get', 'text');
    }

    /**
     * Shows or hides the smiley drawer
     */
    private toggleSmilies() {
        const region = this.getRegion('smilies');
        if (!region.hasView()) {
            const view = new SmiliesCollectionView();
            const data = this.getUI('text').data('smilies');
            view.collection.add(data);
            this.showChildView('smilies', view);
            this.listenTo(view, 'click:smiley', (smiley) => {
                // additional space to prevent smiley concatenation:
                // `:cry:` and `(-.-)zzZ` becomes `:cry:(-.-)zzZ` which outputs
                // smiley image for `:(`
                this.insertText(smiley.code + ' ');
            });
        }
        this.getChildView('smilies').$el.collapse('toggle');
    }

    private postContentChanged() {
        autosize.update(this.getUI('text'));
    }

    private addMenuButtons() {
        const markupSettings = this.getUI('text').data('buttons');
        const collection = new Collection(markupSettings);
        const view = new MenuButtonBarView({ collection });
        this.showChildView('buttons', view);
    }
}

export { EditorView };
