import _ from 'underscore';
import Bb from 'backbone';
import Bookmarks from 'modules/bookmarks/collections/bookmarksCl';

export default Bb.Model.extend({
  bookmarks: null,

  /**
   * Gets users bookmarks.
   *
   * Fetches the bookmarks from the server
   *
   * @param {object} options
   * - {callback} success
   * - {callback} error
   * @returns {Backbone.Collection} bookmarks collection
   * @public
   */
  getBookmarks: function (options) {
    _.defaults(options, { success: null, error: null });
    if (!this.bookmarks) {
      this.bookmarks = new Bookmarks();
      this.bookmarks.fetch({
        success: options.success,
        error: options.error,
      });
    } else {
      options.success.call(options.context, this.bookmarks, null, options);
    }
    return this.bookmarks;
  },

  isLoggedIn: function () {
    return this.get('id') > 0;
  }

});
