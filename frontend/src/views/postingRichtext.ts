import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import GeshisCollection from 'collections/geshis';
import EmbedJS from 'embed-js';
import instagram from 'embed-plugin-instagram';
import noembed from 'embed-plugin-noembed';
import url from 'embed-plugin-url';
import * as _ from 'underscore';
import GeshiView from 'views/geshi';

class PostingRichtextView extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            template: _.noop,
        });
        super(options);
    }

    public onRender() {
        this.initGeshi('.geshi-wrapper');
        this.initEmbed('.js-embed');
    }

    private initGeshi(elementN: string) {
        const geshiElements = this.$(elementN);
        if (!geshiElements.length) {
            return;
        }
        const geshis = new GeshisCollection();
        geshiElements.each((key, element) => {
            const view = new GeshiView({ el: element, collection: geshis });
        });
    }

    /**
     * Render embeded content through third-party JS engine
     *
     * @param element selector for embed content
     */
    private initEmbed(el: string) {
        const $elements: JQuery = this.$(el);
        if (!$elements.length) {
            return;
        }
        $elements.each((index: number, element: HTMLElement) => {
            const x = new EmbedJS({
                input: element,
                // evaluated from right to left
                plugins: [url(), noembed(), instagram()],
                replaceUrl: true,
            });
            x.render();
        });
    }
}

export { PostingRichtextView };
