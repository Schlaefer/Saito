/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import PostingModel from 'models/PostingMdl';

/**
 * Stores all data required to send a new posting to the server
 */
export default class AnswerModel extends PostingModel {
    protected saitoUrl = 'postings/';
}
