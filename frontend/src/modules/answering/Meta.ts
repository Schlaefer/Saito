/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { JsonApiModel } from 'lib/backbone/jsonApi';

interface IAnswerMetaData {
    draft?: {
        id: number,
        subject: string|null,
        text: string|null,
    };
    editor: {
        buttons: any[],
        categories: any[],
        smilies: any[],
    };
    meta: {
        autoselectCategory: boolean,
        info: string,
        isEdit: boolean,
        last: string,
        quoteSymbol: string,
        subject?: string,
        text?: string|null,
        subjectMaxLength: number,
    };
    posting: object;
}

class MetaModel extends JsonApiModel {
    public attributes: IAnswerMetaData;

    /**
     * Ma initializer
     *
     * @param options options
     */
    public initialize(options: object = {}) {
        this.saitoUrl = 'postingmeta/';
    }
}

export { MetaModel };
