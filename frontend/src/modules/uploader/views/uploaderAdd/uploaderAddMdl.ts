/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';

class UploaderAddMdl extends Model {
    public defaults() {
        return  {
            // Form data with file.
            fileToUpload: null,
            // The amount of data currently transfered.
            loaded: undefined,
            // Progress in percent.
            progress: 0,
            // Start time of an upload as UNIX-timestamp
            start: undefined,
            // Upload is in progress.
            uploadInProgress: false,
        };
    }

    /**
     * Bb validator. Returns error message if validation fails.
     * @param attrs Attributes to check
     * @param options Options
     */
    public validate(attrs: any, options?: any): string|undefined {
        const exists = this.collection.findWhere({ title: attrs.fileToUpload.name });
        if (exists !== undefined) {
            return $.i18n.__('upl.vald.e.fileExists');
        }
    }
}

export default UploaderAddMdl;
