import Backbone from 'backbone';
import BookmarkModel from 'models/bookmark';

export default Backbone.Collection.extend({
  model: BookmarkModel
});
