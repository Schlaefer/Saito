/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { defaults as _defaults } from 'underscore';
import AnswerModel from './AnswerModel';

/**
 * Stores all data required to send a new posting to the server
 */
export default class PreviewModel extends AnswerModel {
    protected saitoUrl: string;
    /**
     * Constructor
     *
     * @param options Bb options
     */
    public constructor(defaults: any = {}, options: any = {}) {
        _defaults(defaults, {
            html: undefined,
        });

        super(defaults, options);

        this.saitoUrl = 'preview/preview';
    }
}
