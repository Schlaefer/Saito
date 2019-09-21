/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Collection, Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as _ from 'underscore';

/**
 * Renders Cake validation errors on a form
 *
 * Expects an collection with errors. Error model should contain:
 * - source - The object with...
 *   - field - CSS-selector for the field.
 * - title - The Error message.
 */
export default class CakeFormErrorView extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            collection: new Collection(),
            template: _.noop,
        });

        super(options);
    }

    public onRender() {
        this.reset();

        this.collection.each((error) => {
            const element = error.get('source').field;
            const msg = error.get('title');

            this.renderErrors(element, msg);
        });
    }

    /**
     * Removes all error messages
     */
    private reset(): void {
        this.$('.invalid-feedback').remove();
        this.$('.is-invalid').removeClass('is-invalid');
    }

    /**
     * Attach error message to input elements
     *
     * @param selector Selector for input field with errors.
     * @param msg Error message to display.
     */
    private renderErrors(selector: string, msg: string): void {
        /// Add bootstrap compatible invalid selector in input fields.
        const input = this.$(selector);
        input.addClass('is-invalid');

        const tpl = _.template('<div class="invalid-feedback" style="display: block;"><%- message %></div>');

        /// Appends the error msg at a dedicated place.
        const dedicatedElement = this.findDedicatedElement(input);
        if (dedicatedElement) {
            dedicatedElement.html(tpl({ message: msg }));

            return;
        }

        /// Just appends the error msg after the input field.
        input.after(tpl({ message: msg }));
    }

    /**
     * Tries to find a dedicated element where to put the error message.
     *
     * Searches the input-field surrounding for a .vld-msg element.
     *
     * @param element HTML input element the error message belongs to.
     */
    private findDedicatedElement(element: JQuery<HTMLElement>): JQuery<HTMLElement> | false {
        let dedicatedElement: JQuery<HTMLElement> | null = null;
        let level: number = 0;
        let parent = element;
        // We assume that the dedicated element isn't miles up in the DOM tree.
        const maxLevel: number = 5;
        while (level < maxLevel) {
            parent = parent.parent();
            if (parent.get(0) === this.$el.get(0)) {
                // Don't leave the form.
                break;
            }
            if (parent.find(':input').length > 1) {
                // We left the HTML subtree of the input.
                break;
            }
            dedicatedElement = parent.find('.vld-msg');
            if (dedicatedElement.length) {
                // Element was found.
                break;
            }
            level++;
        }

        if (dedicatedElement && dedicatedElement.length) {
            return dedicatedElement;
        }

        return false;
    }
}
