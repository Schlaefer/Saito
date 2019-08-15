/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as _ from 'underscore';

export default class CategorySelectVw extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            autoselectCategory: false,
            className: 'form-group d-flex',
            events: {
                'change @ui.select': 'onChangeSelect',
            },
            template: _.template(`
                <label class="col-form-label mr-3" for="category-id">
                    <%- $.i18n.__('answer.cat.l') %>
                </label>
                <select name="category_id" class="form-control" tabindex="1" required="required" id="category-id">
                    <% if (!autoselectCategory) { %>
                        <option value=""></option>
                    <% } %>
                    <% for (const [id, title] of Object.entries(categories)) { %>
                        <option
                            value="<%= id %>"
                            <% if (category_id == id) { %>selected="selected"<% } %>
                        >
                            <%- title %>
                        </option>
                    <% } %>
                </select>
            `),
            ui: {
                select: 'select',
            },
        });
        super(options);
    }

    public onRender() {
        this.triggerMethod('change:select');
    }

    public templateContext() {
        return {
            autoselectCategory: this.getOption('autoselectCategory'),
            categories: this.getOption('categories'),
        };
    }

    private onChangeSelect() {
        let categoryId = this.getUI('select').val();
        if (!categoryId || typeof categoryId !== 'string') {
            this.model.set('category_id', undefined);

            return;
        }
        categoryId = parseInt(categoryId, 10);
        this.model.set('category_id', categoryId);
    }
}
