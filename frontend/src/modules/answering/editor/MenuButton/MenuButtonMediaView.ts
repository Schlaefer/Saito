import { MediaInsertView } from '../Menu/MediaInsertView';
import { AbstractMenuButtonView } from './AbstractMenuButtonView';

class MenuButtonMediaView extends AbstractMenuButtonView {
    protected handleButton() {
        const view = new MediaInsertView().render();
        this.listenTo(view, 'out', (out) => {
            this.channel.request('insert:text', out);
        });
    }
}

export { MenuButtonMediaView };
