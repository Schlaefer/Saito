/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { JsonApiCollection, JsonApiModel } from 'lib/backbone/jsonApi';
import UploadFilter from '../lib/uploadFilter';

class UploadsModel extends JsonApiModel {
    protected saitoUrl = 'uploads/';
}

export default class extends JsonApiCollection {
    /** Bb collection model */
    public model = UploadsModel;

    public uploadFilter!: UploadFilter;

    protected saitoUrl = 'uploads/';

    public initialize() {
        this.uploadFilter = new UploadFilter();
    }

    public getFiltered() {
        return this.filter((model) => {
            return this.uploadFilter.filter(model);
        });
    }
}
