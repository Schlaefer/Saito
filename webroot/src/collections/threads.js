import Backbone from 'backbone';
import ThreadModel from 'models/thread';
import App from 'models/app';
import 'backbone.localstorage';
import 'lib/saito/localStorageHelper';

export default Backbone.Collection.extend({

  model: ThreadModel,

  localStorage: (function () {
    var key = App.eventBus.request('app:localStorage:key', 'Threads');
    return new Backbone.LocalStorage(key);
  })(),

  fetch: function (options) {
    if (App.eventBus.request('app:localStorage:available')) {
      return Backbone.Model.prototype.fetch.call(this, options);
    }
  }

});
