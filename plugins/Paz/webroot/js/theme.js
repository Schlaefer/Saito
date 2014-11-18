/*jshint -W031 */
define(['jquery', 'underscore', 'marionette', 'app/core'],
  function($, _, Marionette, App) {

    'use strict';

    var TopMenuModule = App.module('TopMenuModule', function(Module) {

      var HeaderView = Marionette.ItemView.extend({
        events: {
          'click #js-top-menu-open': '_topMenuOpen',
          'click #js-top-menu-close': '_topMenuOpen'
        },

        _topMenuClose: function(event) {
          event.preventDefault();
          this.$el.addClass('headerClosed');
          localStorage.headerClosed = true;
        },

        _topMenuOpen: function(event) {
          event.preventDefault();
          _.defer(function() { window.scrollTo(0, 0); });
          this.$el.removeClass('headerClosed');
          localStorage.headerClosed = false;
        }

      });

      Module.addInitializer(function() {
        new HeaderView({el: $('body')});
      });

    });

    var ThemeSwitcherModule = App.module('ThemeSwitcherModule',
      function(Module) {

        var ThemeSwitcher = Marionette.ItemView.extend({

          el: '#js-themeSwitcher',

          _preset: null,

          events: {
            'click': '_switchTheme'
          },

          initialize: function(options) {
            this._preset = options.SaitoApp.app.theme.preset;
            // if value is not recognized use default
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
            var i = keys.indexOf(this._preset);
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

        Module.addInitializer(function(options) {
          new ThemeSwitcher(options);
        });

      });

  });
