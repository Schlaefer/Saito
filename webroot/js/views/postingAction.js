define([
  'jquery',
  'underscore',
  'marionette',
  'models/app',
  'views/postingActionBookmark',
  'views/postingActionSolves',
  'views/editCountdown'
], function($, _, Marionette, App, BmBtn, SolvesBtn, EditCountdown) {
  'use strict';

  var PostingAction = Marionette.ItemView.extend({

    ui: {
      'btnFixed': '.btn-toggle-fixed',
      'titleFixed': '.title-toggle-fixed',
      'btnLocked': '.btn-toggle-locked',
      'titleLocked': '.title-toggle-locked'
    },

    events: {
      'click .js-btn-setAnsweringForm': 'onBtnAnswer',
      'click @ui.btnFixed': 'onToggleFixed',
      'click @ui.btnLocked': 'onToggleLocked'
    },

    _jsButtons: [BmBtn, SolvesBtn],

    initialize: function() {
      this._initFormElements();
      this.listenTo(this.model, 'change:isAnsweringFormShown', this._toggleAnsweringForm);
    },

    _initFormElements: function() {
      _.each(this._jsButtons, function(View) {
        this.$el.append(new View({model: this.model}).$el);
      }, this);
      var _$editButton = this.$('.js-btn-edit');
      if (_$editButton.length > 0) {
        var editCountdown = new EditCountdown({
          el: _$editButton,
          model: this.model,
          editPeriod: App.settings.get('editPeriod')
        });
      }
    },

    onBtnAnswer: function(event) {
      event.preventDefault();
      this.model.set('isAnsweringFormShown', true);
    },

    onToggleFixed: function(event) {
      event.preventDefault();
      this._sendToggle('fixed');

    },

    onToggleLocked: function(event) {
      event.preventDefault();
      this._sendToggle('locked');
    },

    // @todo move into model
    _sendToggle: function(key) {
      var id = this.model.get('id');
      var $title = this.$(this.ui['title' + _.startCase(key)]);
      var url = App.settings.get('webroot') + '/entries/ajaxToggle/' + id + '/' + key;
      $.ajax({url: url, buffer: false})
        .done(function(data) {
          $title.html(data.html);
        });
    },

    _toggleAnsweringForm: function() {
      if (this.model.get('isAnsweringFormShown')) {
        this.$el.slideUp('fast');
      } else {
        this.$el.slideDown('fast');
      }
    }

  });

  return PostingAction;

});
