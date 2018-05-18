import _ from 'underscore';
import Backbone from 'backbone';
import App from 'models/app';
import cakeRest from 'lib/saito/backbone.cakeRest';

var BookmarkModel = Backbone.Model.extend({

  initialize: function () {
    this.webroot = App.settings.get('webroot') + 'bookmarks/';
  }

});

_.extend(BookmarkModel.prototype, cakeRest);

export default BookmarkModel;
