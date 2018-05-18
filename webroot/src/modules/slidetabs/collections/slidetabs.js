import _ from 'underscore';
import Backbone from 'backbone';
import App from 'models/app';
import SlidetabModel from 'modules/slidetabs/models/slidetab';

export default Backbone.Collection.extend({

  model: SlidetabModel,

  initialize: function () {
    App.eventBus.reply('slidetab:open', this.isOpen, this);
  },

  // returns if particular slidetab is open or not
  isOpen: function (id) {
    return this.get(id).get('isOpen');
  }

});
