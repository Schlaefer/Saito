import $ from 'jquery';
import _ from 'underscore';
import App from 'models/app';
import Marionette from 'backbone.marionette';
import EventBus from 'app/vent';

import Application from 'app/core';
import AppView from 'views/app';
import NotificationView from 'modules/notification/notification.ts';
import PrerequisitesTesterView from 'views/prerequisitesTester';
import Html5NotificationModule from 'modules/notification/html5-notification';

import 'lib/jquery.i18n/jquery.i18n.extend';
import 'lib/saito/backbone.initHelper';
import 'lib/saito/backbone.modelHelper';

/**
 * Redirect helper
 *
 * @param {string} destination
 */
window.redirect = function (destination) {
  document.location.replace(destination);
};

/**
 * Global content timer
 */
var contentTimer = {
  show: function () {
    $('#content').css('visibility', 'visible');
    console.warn('DOM ready timed out: show content fallback used.');
    delete this.timeoutID;
  },

  setup: function () {
    this.cancel();
    var self = this;
    this.timeoutID = window.setTimeout(function () {
      self.show();
    }, 5000);
  },

  cancel: function () {
    if (typeof this.timeoutID === "number") {
      window.clearTimeout(this.timeoutID);
      delete this.timeoutID;
    }
  }
};

contentTimer.setup();



var whenReady = function (callback) {
  if ($.isReady) {
    callback();
  } else {
    $(document).ready(callback);
  }
};

var app = {
  fireOnPageCallbacks: function (allCallbacks) {
    var callbacks = allCallbacks.afterAppInit;
    _.each(callbacks, function (fct) {
      fct();
    });

    EventBus.vent.on('isAppVisible', _.once(function (status) {
      var callbacks = allCallbacks.afterViewInit;
      _.each(callbacks, function (fct) {
        fct();
      });
    }));
  },

  configureAjax: function ($, App) {
    // prevent caching of ajax results
    $.ajaxSetup({ cache: false });

    //// set CSRF-token
    $.ajaxPrefilter(function (options, _, xhr) {
      if (xhr.crossDomain) {
        return;
      }
      xhr.setRequestHeader(App.request.csrf.header, App.request.csrf.token);
    });

    //// set JWT-token
    const jwtCookie = document.cookie.match(/Saito-jwt=([^\s;]*)/)
    if (!jwtCookie) {
      return;
    }
    App.settings.set('jwt', jwtCookie[1]);

    $.ajaxPrefilter(function (options, _, xhr) {
      if (xhr.crossDomain) {
        return;
      }
      xhr.setRequestHeader('Authorization', 'bearer ' + App.settings.get('jwt'));
    });
  },

  bootstrapApp: function (event, options) {
    let appView,
      appReady,
      prerequisitesTesterView;

    // do this always first
    App.settings.set(options.SaitoApp.app.settings);
    // init i18n, do this always second
    const language = App.settings.get('language');
    $.i18n.setUrl(App.settings.get('webroot') + 'js/locale/' + language +  '.json');

    App.currentUser.set(options.SaitoApp.currentUser);
    App.request = options.SaitoApp.request;

    app.configureAjax($, App);

    Html5NotificationModule.start();

    //noinspection JSHint
    new NotificationView(EventBus.vent);

    var callbacks = options.SaitoApp.callbacks.beforeAppInit;
    _.each(callbacks, function (fct) {
      fct();
    });

    appReady = function () {
      prerequisitesTesterView = new PrerequisitesTesterView({
        el: $('.app-prerequisites-warnings')
      });

      app.fireOnPageCallbacks(options.SaitoApp.callbacks);
      appView = new AppView({ el: 'body' });
      appView.initFromDom({
        SaitoApp: options.SaitoApp,
        contentTimer: contentTimer
      });
    };

    whenReady(appReady);
  }
};

Application.on('start', app.bootstrapApp);

EventBus.vent.reply('webroot', function () {
  return App.settings.get('webroot');
});
EventBus.vent.reply('apiroot', function () {
  return App.settings.get('apiroot');
});

export default Application;
