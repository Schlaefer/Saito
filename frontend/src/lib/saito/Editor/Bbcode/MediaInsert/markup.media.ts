/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import * as _ from 'underscore';
import BbcodeTag from './../BbcodeTag';
import DropboxPreFilter from './Prefilter/DropboxPrefilter';
import { IPrefilter } from './Prefilter/PrefilterAbstract';
import YoutubePreFilter from './Prefilter/YoutubePrefilter';

/**
 * Helper for converting multimedia content to BBCode tags
 */
export default class MarkupMultimedia {
    private preFilters: IPrefilter[];

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
    public multimedia(text: string, options: object = {}): IStringable {
        let textv = $.trim(text);
        const patternEnd = '([\\/?]|$)';

        const patternImage = new RegExp('\\.(png|gif|jpg|jpeg|webp|svg)' + patternEnd, 'i');
        const patternHtml = new RegExp('\\.(mp4|webm|m4v)' + patternEnd, 'i');
        const patternAudio = new RegExp('\\.(m4a|ogg|mp3|wav|opus)' + patternEnd, 'i');
        const patternIframe = /<iframe/i;

        _.each(this.preFilters, (cleaner) => {
            textv = cleaner.cleanUp(textv);
        });

        if (textv === '') {
            return textv;
        }

        if (patternImage.test(textv)) {
            return (new BbcodeTag({tag: 'img', content: textv}));
        }

        if (patternHtml.test(textv)) {
            return (new BbcodeTag({tag: 'video', content: textv}));
        }

        if (patternAudio.test(textv)) {
            return (new BbcodeTag({tag: 'audio', content: textv}));
        }

        if (patternIframe.test(textv)) {
            const inner = /<iframe(.*?)>.*?<\/iframe>/i.exec(textv);
            if (!inner) {
                return textv;
            }
            const innerText = inner[1].replace(/["']/g, '').trim();

            return (new BbcodeTag({tag: 'iframe', attributes: innerText}));
        }

        return new BbcodeTag({tag: 'embed', content: text});
    }
}
