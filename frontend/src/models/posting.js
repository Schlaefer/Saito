import _ from 'underscore';
import Marionette from 'backbone.marionette';
import App from 'models/app';
import 'lib/saito/backbone.modelHelper';

export default Backbone.Model.extend({
  defaults: {
    isBookmarked: false,
    isSolves: false,
    isAnsweringFormShown: false,
    html: ''
  },

  initialize: function () {
    this.listenTo(this, 'change:isSolves', this.syncSolved);
  },

  isRoot: function () {
    var _pid = this.get('pid');
    if (!_.isNumber(_pid)) {
      throw 'pid is not a number.';
    }
    return _pid === 0;
  },

  syncSolved: function () {
    $.ajax({
      url: App.settings.get('webroot') + 'entries/solve/' + this.get('id'),
      type: 'POST',
      dateType: 'json'
    });
  },

  fetchHtml: function (options) {
    $.ajax({
      success: _.bind(function (data) {
        this.set('html', data);
        if (options && options.success) { options.success(); }
      }, this),
      type: 'POST',
      dateType: "html",
      url: App.settings.get('webroot') + 'entries/view/' + this.get('id')
    });
  }

});
