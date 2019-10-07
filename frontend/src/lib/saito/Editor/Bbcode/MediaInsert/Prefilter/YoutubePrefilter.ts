/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import PrefilterAbstract from './PrefilterAbstract';

export default class YoutubePreFilter extends PrefilterAbstract {
    public cleanUp(text: string): string {
        let url: string = text;

        if (/http/.test(text) === false) {
            url = 'http://' + text;
        }

        const regex = /(http|https):\/\/(\w+:?\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i;
        if (!regex.test(url)) {
            return text;
        }

        let domainRegex: RegExp | undefined;
        const matches = url.match(/(https?:\/\/)?(www\.)?(.[^\/:]+)/i);
        const domain = matches ? matches.pop() : null;
        switch (domain) {
            case 'youtu.be':
                domainRegex = /youtu.be\/(.*?)(&.*)?$/;
                break;
            case 'youtube.com':
                domainRegex = /v=(.*?)(&.*)?$/;
                break;
        }

        if (domainRegex !== undefined) {
            if (domainRegex.test(url)) {
                const mt = url.match(domainRegex);
                if (mt) {
                    text = this.createIframe({
                        src: '//www.youtube-nocookie.com/embed/' + mt[1],
                    });
                }
            }
        }

        return text;
    }
}
