import Bb from 'backbone';
import App from 'models/app';
import UploadModel from '../models/upload';

export default Bb.Collection.extend({
  /** Bb collection model */
  model: UploadModel,

  /**
   * Bb initializer
   */
  initialize: function (options) {
    this.url = App.settings.get('apiroot') + 'uploads/';
  },

  /**
   * Bb comparator
   */
  comparator: function (model) {
    // sort by latest firest (negate ID for DESC)
    return -1 * model.get('id');
  },

  /**
   * Bb response parser
   */
  parse: function (response, options) {
    return response.data;
  },
});
