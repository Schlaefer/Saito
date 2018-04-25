define([
  'jquery', 'underscore', 'backbone', 'marionette', 'models/app',
  'modules/shoutbox/models/shout',
  'text!modules/shoutbox/templates/add.html',
  'jqueryAutosize'
], function($, _, Backbone, Marionette, App, ShoutModel, Tpl) {

  "use strict";

  var ShoutboxAdd = Marionette.View.extend({

    template: _.template(Tpl),

    _placeholder: {
      a: $.i18n.__('Shout :shortcut', {shortcut: '⌃s'}),
      b: $.i18n.__('Hit enter to mark as read')
    },

    events: {
      "keyup form": "formUp",
      "keydown form": "formDown",
      "blur #shoutbox-input": "_setPlaceholder",
      "focus #shoutbox-input": "_setPlaceholder"
    },

    submit: function() {
      this.model.save(
          {text: this.textarea.val()},
          {
            success: _.bind(function(model, response) {
              // assumes all local shouts are read if user sends a new shout
              App.vent.trigger('shoutbox:mar', {silent: true});
              // update view with latest data coming as answer from the add
              this.collection.reset(response);
            }, this)
          }
      );
    },

    _setPlaceholder: function() {
      // Chrome is to fast to pickup the focus if not deferred
      _.defer(_.bind(function() {
        var _placeholder;
        if (this.textarea.is(':focus')) {
          _placeholder = this._placeholder.b;
        } else {
          _placeholder = this._placeholder.a;
        }
        this.textarea.attr('placeholder', _placeholder);
      }, this));
    },

    /**
     * Workaround for https://bugzilla.mozilla.org/show_bug.cgi?id=33654
     */
    _firefoxRowFix: function() {
      if(navigator.userAgent.toLowerCase().indexOf('firefox') === -1) {
        return;
      }
      var _lineHeight = parseFloat(this.textarea.css('line-height')),
          _lines = this.textarea.attr("rows") * 1;
      this.textarea.css('height', _lines * _lineHeight);
    },

    serializeData: function() {
      return {
        placeholder: this._placeholder.a
      };
    },

    clearForm: function() {
      // trigger resize to shrink the textarea back to one line after
      // entering multi-line text
      this.textarea.val('').trigger('autosize.resize');
    },

    formDown: function(event) {
      if (event.keyCode === 13 && event.shiftKey === false) {
        event.preventDefault();
        if (this.textarea.val().length > 0) {
          this.submit();
          this.clearForm();
        } else {
          App.vent.trigger('shoutbox:mar');
        }
      }
    },

    formUp: function(event) {
      if (this.textarea.val().length > 0) {
        App.eventBus.trigger('breakAutoreload');
      } else if (this.textarea.val().length === 0) {
        App.eventBus.trigger('initAutoreload');
      }
    },

    onShow: function() {
      this.textarea = this.$('#shoutbox-input');
      this._firefoxRowFix();
      this.textarea.autosize();
      this._setPlaceholder();
      $(window).keydown(_.bind(function(event) {
        if (event.ctrlKey === true && event.which === 83) {
          this.textarea.focus();
        }
      }, this));
    }

  });

  return ShoutboxAdd;

});
