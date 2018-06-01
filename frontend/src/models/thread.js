import _ from 'underscore';
import Backbone from 'backbone';
import ThreadLinesCollection from 'collections/threadlines';

export default Backbone.Model.extend({

  defaults: {
    isThreadCollapsed: false
  },

  initialize: function () {
    this.threadlines = new ThreadLinesCollection();
  }

});
