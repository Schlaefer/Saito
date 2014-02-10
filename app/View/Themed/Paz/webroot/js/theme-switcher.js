define(['jquery', 'underscore', 'marionette'], function($, _, Marionette) {

  'use strict';

  var ThemeSwitcher = Marionette.ItemView.extend({

    el: '#js-themeSwitcher',

    _preset: null,

    events: {
      'click': '_switchTheme'
    },

    initialize: function(options) {
      this._preset = options.preset;
      // if valus is not recognized use default
      if (!this.templates[this._preset]) {
        this._preset = Object.keys(this.templates)[0];
      }
      this.render();
    },

    templates: {
      theme: '<i class="fa fa-sun-o"></i>',
      night: '<i class="fa fa-moon-o"></i>'
    },

    _switchTheme: function(event) {
      event.preventDefault();
      var keys = Object.keys(this.templates);
      var i =  keys.indexOf(this._preset);
      var next = i + 1;
      if (next >= keys.length) {
        next = 0;
      }
      this._preset = keys[next];
      localStorage.theme = this._preset;
      document.location.reload(true);
    },

    render: function() {
      this.$el.hide();
      this.$el.html(this.templates[this._preset]);
      this.$el.fadeIn();
    }

  });

  return ThemeSwitcher;

});