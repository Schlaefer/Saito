import { JsonApiCollection } from 'lib/backbone/jsonApi';
import BookmarkModel from '../models/bookmark';

export default class extends JsonApiCollection {
    /** Bb collection model */
    public model = BookmarkModel;

    protected saitoUrl = 'bookmarks/';

    /** Bb comparator */
    public comparator = (model: BookmarkModel) => {
        return -1 * model.get('id');
    }
}
