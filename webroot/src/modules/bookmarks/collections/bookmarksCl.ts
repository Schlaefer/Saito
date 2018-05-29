import { JsonApiCollection } from 'lib/backbone/jsonApi';
import App from 'models/app';
import BookmarkModel from '../models/bookmark';

export default class extends JsonApiCollection {
    /** Bb collection model */
    model = BookmarkModel;

    /** Bb URL property */
    url = () => { return App.settings.get('apiroot') + 'bookmarks/' };

    /** Bb comparator */
    comparator = (model) => {
        return -1 * model.get('id');
    };
};
