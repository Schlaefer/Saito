/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Collection, Model } from 'backbone';
import { CollectionView, View } from 'backbone.marionette';
import $ from 'jquery';
import * as _ from 'underscore';
import { NoContentView as EmptyView } from 'views/NoContentView';
import { IUploaderOptions } from '../uploader';
import UploaderItemVw from './uploaderItemVw';

class UploaderClVw extends CollectionView<Model, View<Model>, Collection> {
    public constructor(options: IUploaderOptions) {
        _.defaults(options, {
            childView: UploaderItemVw,
            childViewOptions: {
                permission: options.permission,
                userId: options.userId,
            },
            className: 'imageUploader-cards',
            emptyView: EmptyView,
            emptyViewOptions: () => {
                return {
                    model: new Model({ content: $.i18n.__('upl.ncy') }),
                };
            },
        });
        super(...arguments);
    }
}

export default UploaderClVw;
