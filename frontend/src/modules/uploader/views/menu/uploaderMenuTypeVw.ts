/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Collection, Model } from 'backbone';
import { View, ViewOptions } from 'backbone.marionette';
import $ from 'jquery';
import { defaults, escape, template, unique } from 'underscore';
import UploaderMenuMdl from './uploaderMenuMdl';

/**
 * Shows mime-type selector
 */
class UploaderMenuTypeVw extends View<Model> {
    public constructor(options: ViewOptions<Model> & {collection: Collection}) {
        defaults(options, {
            className: 'form-control',
            events: {
                change: 'onChangeSelect',
            },
            model: UploaderMenuMdl,
            modelEvents: {
                'change:filterType': 'onChangeFilterType',
            },
            tagName: 'select',
            template: template(`
                <option value=""><%= $.i18n.__('upl.menu.type.all') %></option>
            `),
            ui: {
                select: 'select',
            },
        });

        super(options);
    }

    public onRender() {
        const types = unique(this.collection.pluck('mime').map((s) => s.split('/')[0])).sort();
        for (const type of types) {
            let l10n;
            let value;
            // Have every type string written out for l10n in po files
            switch (type) {
                case 'application':
                    value = 'application';
                    l10n =  $.i18n.__('upl.mime.type.app');
                    break;
                case 'audio':
                    value = 'audio';
                    l10n =  $.i18n.__('upl.mime.type.audio');
                    break;
                case 'image':
                    value = 'image';
                    l10n =  $.i18n.__('upl.mime.type.image');
                    break;
                case 'text':
                    value = 'text';
                    l10n =  $.i18n.__('upl.mime.type.text');
                    break;
                case 'video':
                    value = 'video';
                    l10n =  $.i18n.__('upl.mime.type.video');
                    break;
                default:
                    // Filtering for unknown/misc formats not implemented.
                    // PRs welcome.
                    continue;
            }
            this.$el.append('<option value="' + escape(value) + '">' + escape(l10n) + '</option>');
        }
    }

    protected onChangeSelect(event: JQueryEventObject) {
        const select = event.target as HTMLSelectElement;
        this.model.set('filterType', select.value);
    }

    protected onChangeFilterType(model: UploaderMenuMdl) {
        if (model.get('filterType') === undefined) {
            const select = this.el as HTMLSelectElement;
            select.selectedIndex = 0;
        }
    }
}

export default UploaderMenuTypeVw;
