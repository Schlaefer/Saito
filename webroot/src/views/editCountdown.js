import $ from 'jquery';
import _ from 'underscore';
import Marionette from 'backbone.marionette';
import moment from 'moment';
import 'jQuery-tinyTimer/jquery.tinytimer';

export default Marionette.View.extend({
  /**
   * time in seconds how long the timer should count down
   */
  _editEnd: null,

  _buttonText: null,

  _$countdownDummy: null,

  _doneAction: 'remove',

  initialize: function (options) {
    this._editEnd = moment(this.model.get('time')).unix() +
      (options.editPeriod * 60);
    // this._editEnd = moment().unix() + 5 ; // debug
    if (moment().unix() > this._editEnd) {
      return;
    }
    if (options.done) {
      this._doneAction = options.done;
    }
    this._buttonText = this.$el.html();
    this._$countdownDummy = $('<span style="display: none;"></span>');
    this.$el.append(this._$countdownDummy);
    this._start();
  },

  _setButtonText: function (timeText) {
    this.$el.text(this._buttonText + ' ' + timeText);
  },

  _onTick: function (remaining) {
    if (remaining.m > 1 || (remaining.m === 1 && remaining.s > 30)) {
      remaining.m = remaining.m + 1;
      this._setButtonText('(' + remaining.m + ' min)');
    } else if (remaining.m === 1) {
      this._setButtonText('(' + remaining.m + ' min ' + remaining.s + ' s)');
    } else {
      this._setButtonText('(' + remaining.s + ' s)');
    }
  },

  _onEnd: function () {
    switch (this._doneAction) {
      case 'disable':
        this._disable();
        break;
      default:
        this._remove();
    }
  },

  _remove: function () {
    this.remove();
  },

  _disable: function () {
    this.$el.attr('disabled', 'disabled');
  },

  _start: function () {
    this._$countdownDummy.tinyTimer({
      to: moment.unix(this._editEnd).toDate(),
      format: '',
      onTick: _.bind(this._onTick, this),
      onEnd: _.bind(this._onEnd, this)
    });
  }

});
