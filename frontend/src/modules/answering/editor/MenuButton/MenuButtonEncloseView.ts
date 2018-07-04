import { AbstractMenuButtonView } from './AbstractMenuButtonView';

class MenuButtonEncloseView extends AbstractMenuButtonView {
    protected handleButton() {
        this.channel.request('wrap:text', this.model.get('openWith'), this.model.get('closeWith'));
    }
}

export { MenuButtonEncloseView };
