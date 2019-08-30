/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { ModelSaveOptions } from 'backbone';
import PostingModel from 'models/PostingMdl';
import { defaults } from 'underscore';

/**
 * Stores all data required to send a new posting to the server
 */
export default class AnswerModel extends PostingModel {
    /**
     * Ma initializer
     *
     * @param options options
     */
    public initialize(options) {
        this.saitoUrl = 'postings/';
    }
}
