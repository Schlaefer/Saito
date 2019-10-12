/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import * as _ from 'underscore';

interface IPrefilter {
    cleanUp(text: string): string;
}

/**
 * Filters applied before URL is evaluated
 */
abstract class PrefilterAbstract implements IPrefilter {
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

export default PrefilterAbstract;

export { IPrefilter, PrefilterAbstract };
