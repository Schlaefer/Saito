import _ from 'underscore';
import Marionette from 'backbone.marionette';
import Tpl from 'templates/spinner.html';

export default Marionette.View.extend({
  className: 'spinner',
  template: Tpl,
  ui: {
    progress: '.progress',
  },
  onRender: function() {
    const progress = this.getUI('progress');
    progress.css('visibility', 'hidden');
    _.delay(() => { progress.css('visibility', 'visible'); }, 1000);
  }
});
