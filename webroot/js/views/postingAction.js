define([
  'jquery',
  'underscore',
  'marionette',
  'models/app',
  'views/postingActionBookmark',
  'views/postingActionSolves',
  'views/editCountdown'
], function($, _, Marionette, App, BmBtn, SolvesBtn, EditCountdown) {
  "use strict";

  var PostingAction = Marionette.ItemView.extend({

    events: {
      "click .js-btn-setAnsweringForm": "onBtnAnswer"
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