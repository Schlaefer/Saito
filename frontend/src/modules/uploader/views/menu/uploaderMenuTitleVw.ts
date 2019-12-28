/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { $, Model } from 'backbone';
import { View, ViewOptions } from 'backbone.marionette';
import { debounce, defaults, isEmpty, noop} from 'underscore';
import UploaderMenuMdl from './uploaderMenuMdl';

/**
 * Filter by filename or title
 */
class UploaderMenuTitleVw extends View<Model> {
    public constructor(options: ViewOptions<Model> = {}) {
        defaults(options, {
            attributes: {
                placeholder: $.i18n.__('upl.menu.search.t'),
                type: 'text',
            },
            className: 'form-control',
            events: {
                keyup: 'onChangeInput',
            },
            model: UploaderMenuMdl,
            modelEvents: {
                'change:filterTitle': 'onChangeFilterTitle',
            },
            tagName: 'input',
            template: noop,
            ui: {
                input: 'input',
            },
        });

        super(options);

        // Don't render results on each keystroke
        this.setFilterTitle = debounce(this.setFilterTitle, 500);
    }

    protected onChangeInput(event: JQueryEventObject) {
        const element = event.target as HTMLInputElement;
        this.setFilterTitle(element.value);
    }

    protected setFilterTitle(value: string) {
        this.model.set('filterTitle', value);
    }

    protected onChangeFilterTitle(model: UploaderMenuMdl) {
        const newTitle = model.get('filterTitle');
        if (newTitle === undefined) {
            this.el.value = '';
        }
    }
}

export default UploaderMenuTitleVw;
