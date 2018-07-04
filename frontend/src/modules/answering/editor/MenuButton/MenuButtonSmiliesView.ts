import { AbstractMenuButtonView } from './AbstractMenuButtonView';

class MenuButtonSmiliesView extends AbstractMenuButtonView {
    protected handleButton() {
        this.channel.trigger('smilies:toggle');
    }
}

export { MenuButtonSmiliesView };
