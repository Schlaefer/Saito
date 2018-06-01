import _ from 'underscore';
import Backbone from 'backbone';
import App from 'models/app';
import cakeRest from 'lib/saito/backbone.cakeRest';

const ThreadLineModel = Backbone.Model.extend({

  defaults: {
    isInlineOpened: false,
    shouldScrollOnInlineOpen: true,
    isAlwaysShownInline: false,
    isNewToUser: false,
    posting: '',
    html: ''
  },

  initialize: function () {
    this.webroot = App.settings.get('webroot') + 'entries/';
    this.methodToCakePhpUrl = _.clone(this.methodToCakePhpUrl);
    this.methodToCakePhpUrl.read = 'threadLine/';

    this.set('isAlwaysShownInline', App.currentUser.get('user_show_inline') || false);
  }

});

_.extend(ThreadLineModel.prototype, cakeRest);

export default ThreadLineModel;
