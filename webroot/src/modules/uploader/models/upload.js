import Backbone from 'backbone';

export default Backbone.Model.extend({
  /**
   * Bb respone parser
   */
  parse: function (response, options) {
    return response.attributes;
  },
});
