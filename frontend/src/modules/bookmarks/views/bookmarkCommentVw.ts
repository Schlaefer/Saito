import { View } from 'backbone.marionette';
import * as _ from 'underscore';
import * as Tpl from '../templates/bookmarkCommentTpl.html';

/**
 * Comment as input
 */
export class CommentInputView extends View<any> {
    constructor(options) {
        options.template = Tpl;
        options.className = 'm-1';
        options.ui = {
            text: 'input',
        };
        options.events = {
            'keyup @ui.text': 'handleKeypress',
        };
        super(options);
    }
    public onRender() {
        this.getUI('text').focus();
    }
    protected handleKeypress(event) {
        event.preventDefault();
        this.model.set('comment', this.getUI('text').val());
    }
}
