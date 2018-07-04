import { Model } from 'backbone';
import { LinkView } from '../Menu/LinkView';
import { AbstractMenuButtonView } from './AbstractMenuButtonView';

class MenuButtonLinkView extends AbstractMenuButtonView {
    protected handleButton() {
        const title = this.channel.request('selected:text') || '';
        const model = new Model({ title, url: '' });
        const view = new LinkView({ model });
        this.listenTo(view, 'link', (link) => {
            const markup = '[url=' + link.url + ']' + link.title + '[/url]';
            this.channel.request('insert:text', markup);
        });
        view.render();
    }
}

export { MenuButtonLinkView };
