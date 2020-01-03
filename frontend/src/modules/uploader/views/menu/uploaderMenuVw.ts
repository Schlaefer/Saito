/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { View, ViewOptions } from 'backbone.marionette';
import { defaults, template } from 'underscore';
import UploaderMenuMdl from './uploaderMenuMdl';
import UploaderMenuSortVw from './uploaderMenuSortVw';
import UploaderMenuTitleVw from './uploaderMenuTitleVw';
import UploaderMenuTypeVw from './uploaderMenuTypeVw';

class UploaderMenuVw extends View<UploaderMenuMdl> {
    public constructor(options: ViewOptions<UploaderMenuMdl> = {}) {
        options = defaults(options, {
            className: 'd-flex justify-content-end align-items-center',
            events: {
                'click @ui.btnMenu': 'onClickBtnMenu',
                'click @ui.btnReset': 'onClickBtnReset',
            },
            model: UploaderMenuMdl,
            modelEvents: {
                'change:open': 'onChangeOpen',
            },
            regions: {
                sortRg: '.js-sortRg',
                titleRg: '.js-titleRg',
                typeRg: '.js-typeRg',
            },
            template: template(`
<form style="flex-grow: 1;">
    <div class="js-filter form-row justify-content-center align-items-center mx-3" style="display:none;">
        <div class="js-sortRg col-auto my-1"></div>
        <div class="js-typeRg col-auto my-1"></div>
        <div class="js-titleRg col-auto my-1"></div>
        <button type="button" class="js-btnReset btn btn-link">
            <i class="fa fa-undo" aria-hidden="true"></i>
        </button>
    </div>
</form>
<button type="button" class="js-btnMenu btn btn-link">
    <i class="fa fa-search" aria-hidden="true"></i>
</button>
            `),
            ui: {
                btnMenu: '.js-btnMenu',
                btnReset: '.js-btnReset',
                filter: '.js-filter',
            },
        });
        super(options);
    }

    public onRender() {
        this.showChildView('sortRg', new UploaderMenuSortVw({ model: this.model }));
        this.showChildView('typeRg', new UploaderMenuTypeVw({
            collection: this.collection,
            model: this.model,
        }));
        this.showChildView('titleRg', new UploaderMenuTitleVw({ model: this.model }));
    }

    protected onClickBtnMenu() {
        this.model.set('open', !this.model.get('open'));
    }

    protected onClickBtnReset() {
        this.model.reset();
    }

    protected onChangeOpen(model: UploaderMenuMdl) {
        const $filter = this.getUI('filter');
        if (model.get('open')) {
            $filter.slideDown();
        } else {
            $filter.slideUp();
        }
    }
}

export default UploaderMenuVw;
