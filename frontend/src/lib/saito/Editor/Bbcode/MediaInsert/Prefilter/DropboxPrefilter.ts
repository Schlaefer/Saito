/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import PrefilterAbstract from './PrefilterAbstract';

export default class DropboxPreFilter extends PrefilterAbstract {
    /**
     * Convert dropbox HTML-page URL to actual file URL
     *
     * @see https://www.dropbox.com/help/201/en
     */
    public cleanUp(text: string): string {
        return text.replace(/https:\/\/www\.dropbox\.com\//, 'https://dl.dropbox.com/');
    }
}
