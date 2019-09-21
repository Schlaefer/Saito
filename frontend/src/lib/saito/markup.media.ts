/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import * as _ from 'underscore';

interface IPreFilter {
    cleanUp(text: string): string;
}

/**
 * Filters applied before URL is evaluated
 */
abstract class PreFilter implements IPreFilter {
    public abstract cleanUp(text: string): string;

    /**
     * Create HTML iframe tag
     *
     * @param {object} attr - iframe-tag attributes
     * @returns {string}
     */
    protected createIframe(attr: _.Dictionary<string>): string {
        const defaults = {
            allowfullscreen: 'allowfullscreen',
            frameborder: 0,
            height: 315,
            width: 560,
        };
        _.defaults(attr, defaults);

        const reducer = (memo: string, value: string, key: string) => {
            return memo + key + '="' + value + '" ';
        };
        let attributes = _.reduce(attr, reducer, '');
        attributes = attributes.trim();

        return '<iframe ' + attributes + '></iframe>';
    }
}

class DropboxPreFilter extends PreFilter {
    /**
     * Convert dropbox HTML-page URL to actual file URL
     *
     * @see https://www.dropbox.com/help/201/en
     */
    public cleanUp(text: string): string {
        return text.replace(/https:\/\/www\.dropbox\.com\//, 'https://dl.dropbox.com/');
    }
}

class YoutubePreFilter extends PreFilter {
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

/**
 * Helper for converting multimedia content to BBCode tags
 */
class MarkupMultimedia {
    private preFilters: IPreFilter[];

    public constructor() {
        this.preFilters = [
            new DropboxPreFilter(),
            new YoutubePreFilter(),
        ];
    }

    /**
     * Resolve multimedia input to BBCode syntax
     *
     * @param text - content to embed
     * @param options - converting options
     * @returns BBCode for multimedia element
     */
    public multimedia(text: string, options: object = {}) {
        let textv = $.trim(text);
        const patternEnd = '([\\/?]|$)';

        const patternImage = new RegExp('\\.(png|gif|jpg|jpeg|webp|svg)' + patternEnd, 'i');
        const patternHtml = new RegExp('\\.(mp4|webm|m4v)' + patternEnd, 'i');
        const patternAudio = new RegExp('\\.(m4a|ogg|mp3|wav|opus)' + patternEnd, 'i');
        const patternIframe = /<iframe/i;

        let out = '';

        _.each(this.preFilters, (cleaner) => {
            textv = cleaner.cleanUp(textv);
        });

        if (patternImage.test(textv)) {
            out = this.image(textv);
        } else if (patternHtml.test(textv)) {
            out = this.videoHtml5(textv);
        } else if (patternAudio.test(textv)) {
            out = this.audioHtml5(textv);
        } else if (patternIframe.test(textv)) {
            out = this.videoIframe(textv);
        }

        if (out === '') {
            out = this.embed(textv);
        }
        return out;
    }

    private image(text: string): string {
        return '[img]' + text + '[/img]';
    }

    private videoHtml5(text: string): string {
        return '[video]' + text + '[/video]';
    }

    private audioHtml5(text: string): string {
        return '[audio]' + text + '[/audio]';
    }

    private videoIframe(text: string): string {
        const inner = /<iframe(.*?)>.*?<\/iframe>/i.exec(text);
        if (!inner) {
            return text;
        }
        const innerText = inner[1].replace(/["']/g, '');
        return '[iframe' + innerText + '][/iframe]';
    }

    private embed(text: string): string {
        if (text === '') {
            return text;
        }
        return '[embed]' + text + '[/embed]';
    }
}

export { MarkupMultimedia };
