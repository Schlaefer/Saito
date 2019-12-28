/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Collection, Model } from 'backbone';

class LeftToRenderCl extends Collection<Model> {
    public constructor() {
        super();
        this.setComparator('time');
    }

    public setComparator(after: string = 'time') {
        after = (after === 'time') ? 'id' : after;
        this.comparator = (model: Model) => {
            return -1 * model.get(after);
        };
    }
}

export default LeftToRenderCl;
