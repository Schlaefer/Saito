import Bb from 'backbone';
import App from 'models/app';
import BookmarkModel from '../models/bookmark';

export default Bb.JsonApiCollection.extend({
  /** Bb collection model */
  model: BookmarkModel,
  /** Bb URL property */
  url: () => { return App.settings.get('apiroot') + 'bookmarks/' },
  /** Bb comparator */
  comparator: function (model) {
    // sort by latest (negate ID for DESC)
    return -1 * model.get('id');
  },
});
