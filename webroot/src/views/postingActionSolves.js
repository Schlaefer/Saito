import $ from 'jquery';
import _ from 'underscore';
import Marionette from 'backbone.marionette';
import App from 'models/app';

export default Marionette.View.extend({

  tagName: 'a',

  className: 'btn btn-link btn-solves',

  template: _.template('<i class="fa fa-badge-solves-o fa-lg"></i>'),

  events: {
    "click": '_onClick'
  },

  modelEvents: {
    'change:isSolves': '_toggle'
  },

  initialize: function () {
    if (!this._shouldRender()) {
      return;
    }
    this.$el.attr({
      href: '#',
      title: $.i18n.__('Mark entry as helpful')
    });
    this.render();
  },

  _shouldRender: function () {
    if (!App.currentUser.isLoggedIn()) {
      return false;
    }
    if (this.model.isRoot()) {
      return false;
    }
    if (this.model.get('rootEntryUserId') !== App.currentUser.get('id')) {
      return false;
    }
    return true;
  },

  _onClick: function (event) {
    event.preventDefault();
    this.model.toggle('isSolves');
  },

  _toggle: function () {
    var _$icon = this.$('i'),
      _isSolves = this.model.get('isSolves'),
      _html = '';

    if (_isSolves) {
      _$icon.addClass('solves-isSolved');
      _$icon.removeClass('fa-badge-solves-o');
      _$icon.addClass('fa-badge-solves');
      _html = this.$el.html();
      _html = $(_html).removeClass('fa-lg');
    } else {
      _$icon.removeClass('fa-badge-solves');
      _$icon.addClass('fa-badge-solves-o');
      _$icon.removeClass('solves-isSolved');
    }
    this._toggleGlobal(_html);
  },

  /**
   * Sets other badges on the page, prominently in thread-line.
   *
   * @todo should be handled as state by global model for the entry
   *
   * @param html
   * @private
   */
  _toggleGlobal: function (html) {
    var _$globalIconHook = $('.solves.' + this.model.get('id'));
    _$globalIconHook.html(html);
  },

  onRender: function () {
    this._toggle();
  }

});
