(function ($, _, Marionette) {
  const HeaderView = Marionette.View.extend({
    events: {
      'click #js-top-menu-open': '_topMenuOpen',
      'click #js-top-menu-close': '_topMenuClose'
    },

    _topMenuClose: function (event) {
      event.preventDefault();
      this.$el.addClass('headerClosed');
      localStorage.headerClosed = true;
    },

    _topMenuOpen: function (event) {
      event.preventDefault();
      _.defer(function () { window.scrollTo(0, 0); });
      this.$el.removeClass('headerClosed');
      localStorage.headerClosed = false;
    }
  });

  const ThemeSwitcherView = Marionette.View.extend({
    _preset: null,

    events: {
      'click': '_switchTheme'
    },

    initialize: function (options) {
      this._preset = SaitoApp.app.theme.preset;
      // if value is not recognized use default
      if (!this.templates[this._preset]) {
        this._preset = Object.keys(this.templates)[0];
      }
      this.render();
    },

    templates: {
      theme: '<i class="fa fa-moon-o"></i>',
      night: '<i class="fa fa-sun-o"></i>'
    },

    _switchTheme: function (event) {
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

    render: function () {
      this.$el.hide();
      this.$el.html(this.templates[this._preset]);
      this.$el.fadeIn();
    }

  });

  const TopMenu = new Marionette.Application({
    onStart: function () {
      new HeaderView({ el: $('body') });
      new ThemeSwitcherView({ el: '#js-themeSwitcher' });
    }
  });

  TopMenu.start();
})($, _, Marionette);

// export default TopMenu;
