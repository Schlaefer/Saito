import _ from 'underscore';
import $ from 'jquery';
import Mn from 'backbone.marionette';
import AppView from 'views/app';

export default Mn.View.extend({
  template: () => {
    return _.template($('#tpl-recentposts').html());
  },
  onRender: function() {
    const av = new AppView();
    av._initThreadLeafs(this.$('.threadLeaf'));
  }
});
