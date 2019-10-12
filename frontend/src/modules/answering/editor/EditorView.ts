/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

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
            className: 'form-group',
            events: {
                'input @ui.text': 'handleInput',
                'keypress @ui.input': 'handleInput',
            },
            regions: {
                buttons: '.js-editor-buttons',
                smilies: '.js-rgSmilies',
            },
            template: _.template(`
                <div class="js-editor-buttons"></div>
                <div class="js-rgSmilies"></div>
                <textarea name="text" class="form-control" rows="4" tabindex=3></textarea>
            `),
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
        // insert text on edit
        this.getUI('text').val(this.model.get('text'));
        this.addMenuButtons();
        autosize(this.getUI('text'));
        this.postContentChanged();
    }

    public onDestroy() {
        autosize.destroy(this.getUI('text'));
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
     * Called when the editor-text changes through user input
     */
    private handleInput() {
        this.model.set('text', this.getUI('text').val());
    }

    /**
     * Called when the editor-text changes through an insert
     */
    private postContentChanged() {
        this.handleInput();
        _.defer(() => autosize.update(this.getUI('text')));
    }

    /**
     * Inserts text at the current cursor position
     *
     * @param textToInsert - Text to insert
     * @param cursor - New cursor position; default: after inserted text
     */
    private insertText(textToInsert: IStringable, cursor?: number) {
        const textarea = this.getUI('text');
        const text = textToInsert.toString();
        if (!cursor) {
            const current = textarea.textrange('get');
            const isTextSelected: boolean = current.length > 0;
            cursor = isTextSelected ? current.start : current.position;
            cursor += text.length;
        }
        textarea.textrange({ method: 'replace', nofocus: true }, text);
        textarea.textrange('setcursor', cursor);
        this.postContentChanged();
    }

    /**
     * Shows or hides the smiley drawer
     */
    private toggleSmilies() {
        const region = this.getRegion('smilies');
        if (!region.hasView()) {
            const view = new SmiliesCollectionView();
            view.collection.add(this.getOption('smilies'));
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

    private addMenuButtons() {
        const collection = new Collection(this.getOption('buttons'));
        const view = new MenuButtonBarView({ collection });
        this.showChildView('buttons', view);
    }
}

export { EditorView };
