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
    protected createIframe(attr: object): string {
        const defaults = {
            allowfullscreen: 'allowfullscreen',
            frameborder: 0,
            height: 315,
            width: 560,
        };
        _.defaults(attr, defaults);

        const reducer = (memo, value, key) => {
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
    public cleanUp(text) {
        return text.replace(/https:\/\/www\.dropbox\.com\//, 'https://dl.dropbox.com/');
    }
}

class YoutubePreFilter extends PreFilter {
    /**
     * Convert dropbox HTML-page URL to actual file URL
     *
     * @see https://www.dropbox.com/help/201/en
     */
    public cleanUp(text) {
        let url: string = text;
        let videoId: string;

        if (/http/.test(text) === false) {
            url = 'http://' + text;
        }

        let regex = /(http|https):\/\/(\w+:?\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i;
        if (!regex.test(url)) {
            return text;
        }

        const domain: string = url.match(/(https?:\/\/)?(www\.)?(.[^\/:]+)/i).pop();
        switch (domain) {
            case 'youtu.be':
                regex = /youtu.be\/(.*?)(&.*)?$/;
                if (regex.test(url)) {
                    videoId = url.match(regex)[1];
                }
                break;
            case 'youtube.com':
                regex = /v=(.*?)(&.*)?$/;
                if (regex.test(url)) {
                    videoId = url.match(regex)[1];
                }
                break;
        }

        if (videoId !== undefined) {
            text = this.createIframe({
                src: '//www.youtube-nocookie.com/embed/' + videoId,
            });
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

        const patternImage = new RegExp('\\.(png|gif|jpg|jpeg|webp)' + patternEnd, 'i');
        const patternHtml = new RegExp('\\.(mp4|webm|m4v)' + patternEnd, 'i');
        const patternAudio = new RegExp('\\.(m4a|ogg|mp3|wav|opus)' + patternEnd, 'i');
        const patternFlash = /<object/i;
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
        } else if (patternFlash.test(textv)) {
            out = this.videoFlash(textv);
        }

        if (out === '') {
            out = this.embed(textv);
        }
        return out;
    }

    private image(text: string): string {
        return '[img]' + text + '[/img]';
    }

    private videoFlash(text: string): string {
        let html = '[flash_video]URL|WIDTH|HEIGHT[/flash_video]';

        if (text !== null) {
            html = html.replace('WIDTH', /width="(\d+)"/.exec(text)[1]);
            html = html.replace('HEIGHT', /height="(\d+)"/.exec(text)[1]);
            html = html.replace('URL', /src="([^"]+)"/.exec(text)[1]);
            return html;
        }
        return '';
    }

    private videoHtml5(text: string): string {
        return '[video]' + text + '[/video]';
    }

    private audioHtml5(text: string): string {
        return '[audio]' + text + '[/audio]';
    }

    private videoIframe(text: string): string {
        let inner = /<iframe(.*?)>.*?<\/iframe>/i.exec(text)[1];
        inner = inner.replace(/["']/g, '');
        return '[iframe' + inner + '][/iframe]';
    }

    private embed(text: string): string {
        if (text === '') {
            return text;
        }
        return '[embed]' + text + '[/embed]';
    }
}

export { MarkupMultimedia };
