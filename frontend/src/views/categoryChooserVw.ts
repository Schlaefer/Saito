import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import ModalDialog from 'modules/modalDialog/modalDialog';
import { defaults, template } from 'underscore';

class CategoryChooserVw extends View<Model> {
    public constructor(options: any = {}) {
        defaults(options, {
            model: new Model({ defaults: { isOpen: false } }),
            modelEvents: { 'change:isOpen': '_handleStateIsOpen' },
            template: template($('#tpl-categoryChooser').html()),
        });
        super(options);
    }

    /**
     * Handle state of isOpen
     *
     * @param model this model
     * @param toShow new state
     */
    private _handleStateIsOpen(model: Model, toShow: boolean) {
        if (toShow) {
            ModalDialog.show(this, { title: $.i18n.__('category.title.pl') });
            ModalDialog.on('close', () => { model.set('isOpen', false); });
        }
    }

}

export default CategoryChooserVw;
