define(['jquery', 'marionette', 'models/app', 'views/answering',
  'text!templates/spinner.html'],
    function($, Marionette, App, AnsweringView, spinnerTpl) {

      'use strict';

      return Marionette.ItemView.extend({

        answeringForm: false,

        events: {
          "click .btn-answeringClose": "onBtnClose"
        },

        initialize: function(options) {
          this.parentThreadline = options.parentThreadline || null;

          this.listenTo(this.model, 'change:isAnsweringFormShown', this.toggleAnsweringForm);
        },

        onBtnClose: function(event) {
          event.preventDefault();
          this.model.set('isAnsweringFormShown', false);
        },

        toggleAnsweringForm: function() {
          if (this.model.get('isAnsweringFormShown')) {
            this._hideAllAnsweringForms();
            this._showAnsweringForm();
          } else {
            this._hideAnsweringForm();
          }
        },

        _showAnsweringForm: function() {
          App.eventBus.trigger('breakAutoreload');
          if (this.answeringForm === false) {
            this.$el.html(spinnerTpl);
          }
          this.$el.slideDown('fast');
          if (this.answeringForm === false) {
            this.answeringForm = new AnsweringView({
              el: this.$el,
              model: this.model,
              parentThreadline: this.parentThreadline
            });
          }
          this.answeringForm.render();
        },

        _hideAnsweringForm: function() {
          this.$el.slideUp('fast');
          /*
          // @td @bogus
          var parent = this.$el.parent();
          // @td @bogus inline answer
          if (this.answeringForm !== false) {
            this.answeringForm.remove();
            this.answeringForm.undelegateEvents();
            this.answeringForm = false;
          }
          // @td @bogus mix answer
          this.$el.html('');
          var $newEl = $('<div class="postingLayout-slider"></div>');
          this.setElement($newEl);
          parent.append($newEl);
          */
        },

        _hideAllAnsweringForms: function() {
          // we have #id problems with more than one markItUp on a page
          this.collection.forEach(function(posting) {
            if (posting.get('id') !== this.model.get('id')) {
              posting.set('isAnsweringFormShown', false);
            }
          }, this);
        }

      });

    });
