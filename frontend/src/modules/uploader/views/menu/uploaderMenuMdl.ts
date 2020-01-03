/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';

class UploaderMenuMdl extends Model {
    public defaults() {
        return {
            filterTitle: undefined,
            filterType: undefined,
            open: false,
            sort: 'time',
        };
    }

    public reset() {
        const defaults = this.defaults();
        delete defaults.open;
        this.set(defaults);
    }
}

export default UploaderMenuMdl;
