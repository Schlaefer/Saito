import $ from 'jquery';
import _ from 'underscore';
import Backbone from 'backbone';
import App from 'models/app';
import EventBus from 'app/vent';

export default Backbone.Model.extend({

  defaults: {
    isOpen: false
  },

  initialize: function () {
    this.webroot = App.settings.get('webroot');
    this.listenTo(this, 'change:isOpen', this.onChangeIsOpen);
  },

  onChangeIsOpen: function () {
    EventBus.vent.trigger('slidetab:open', {
      slidetab: this.get('id'),
      open: this.get('isOpen')
    }
    );
  },

  sync: function () {
    var key = 'show_' + this.get('id');
    $.post(
      this.webroot + 'users/slidetabToggle',
      { slidetabKey: key }
    );
  }

});
