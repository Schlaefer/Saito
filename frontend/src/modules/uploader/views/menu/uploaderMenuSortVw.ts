/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View, ViewOptions } from 'backbone.marionette';
import $ from 'jquery';
import { defaults, template, unique } from 'underscore';
import UploaderMenuMdl from './uploaderMenuMdl';

/**
 * Sort
 */
class UploaderMenuSortVw extends View<Model> {
    public constructor(options: ViewOptions<Model> = {}) {
        defaults(options, {
            events: {
                'change input[type=radio]': 'onChangeSorting',
            },
            model: UploaderMenuMdl,
            modelEvents: {
                'change:sort': 'onChangeSort',
            },
            template: template(`
<% for ([index, field] of fields.entries()) { %>
    <div class="form-check form-check-inline">
        <input
            class="form-check-input"
            type="radio"
            name="uploaderSort"
            id="uploaderSort<%= field.id %>"
            value="<%= field.id %>"
            <% if (index === 0) { %> checked="" <% } %>>
        <label class="form-check-label" for="uploaderSort<%= field.id %>"><%= field.label %></label>
    </div>
<% } %>
            `),
        });

        super(options);
    }

    public templateContext() {
        return {
            fields: [
                { id: 'time', label: $.i18n.__('upl.menu.sort.time')},
                { id: 'size', label: $.i18n.__('upl.menu.sort.size')},
            ],
        };
    }

    protected onChangeSorting(event: JQueryEventObject) {
        const value = $(event.target).val();
        this.model.set('sort', value);
    }

    protected onChangeSort(model: UploaderMenuMdl) {
        const newSort = model.get('sort');
        const input = this.$('input[value=' + newSort + ']')[0] as HTMLFormElement;
        if (!input.checked) {
            input.checked = true;
        }
    }
}

export default UploaderMenuSortVw;
