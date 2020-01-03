/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { isEmpty } from 'underscore';

/**
 * Implements filtering of upload models
 */
class UploadFilter {
    /**
     * Search for upload file title/name
     */
    protected title: string|undefined;

    /**
     * Search string for upload type
     */
    protected mime: string|undefined;

    /**
     * Mime-type setter
     * @param type Set mime subtype to check against
     */
    public setMime(type: string): void {
        this.mime = isEmpty(type) ? undefined : type;
    }

    /**
     * Title search setter
     * @param title Title search string
     */
    public setTitle(title: string): void {
        this.title = isEmpty(title) ? undefined : title;
    }

    /**
     * Reset filter (unfiltered)
     */
    public reset(): void {
        this.mime = undefined;
        this.title = undefined;
    }

    /**
     * Filter callback to check if a model should be shown
     * @param model Model to apply the filter to
     * @returns True if model should be shown, false otherwise.
     */
    public filter(model: Model): boolean {
        /// Allow other cards (i.e. add new upload) without filtering
        const isCardOfUploadedItem: boolean = (model.get('mime') !== undefined);
        if (!isCardOfUploadedItem) {
            return true;
        }

        /// Check for mime type
        if (this.mime !== undefined) {
            if (model.get('mime').indexOf(this.mime) !== 0) {
                return false;
            }
        }

        /// Check for title
        if (this.title !== undefined) {
            const title = model.get('title') + ' ' + model.get('name');
            if (title.toLowerCase().indexOf(this.title.toLowerCase()) === -1) {
                return false;
            }
        }

        return true;
    }

}

export default UploadFilter;
