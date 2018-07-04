import { Model } from 'backbone';
import * as _ from 'underscore';
import { AbstractMenuButtonView } from './AbstractMenuButtonView';

class MenuButtonSeparator extends AbstractMenuButtonView {
    public getTemplate() {
        return _.template('<div class="markupSeparator"></div>');
    }

    protected handleButton() {
        // not implement
    }
}

export { MenuButtonSeparator };
