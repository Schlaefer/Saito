/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { JsonApiModel } from 'lib/backbone/jsonApi';
import * as _ from 'underscore';

export default abstract class PostingModel extends JsonApiModel {
    /**
     * Constructor
     *
     * @param options Bb options
     */
    public constructor(defaults: any = {}, options: any = {}) {
        _.defaults(defaults, {
            category_id: undefined,
            id: undefined,
            pid: undefined,
            subject: '',
            text: '',
        });
        super(defaults, options);
    }

    public isRoot(): boolean {
        return !this.get('pid');
    }
}
