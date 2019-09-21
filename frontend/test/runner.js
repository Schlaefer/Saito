import $ from 'jquery';
import 'exports';
import 'lib/jquery.i18n/jquery.i18n.extend.js';
import 'lib/saito/backbone.modelHelper';
import 'lib/saito/underscore.extend';
import App from 'models/app';
import EventBus from 'app/vent.ts';

$.fx.off = true;
window.$ = $;

// prevent appending of ?_<timestamp> requested urls
$.ajaxSetup({cache: true});

// make empty dict available for test cases
$.i18n.setDictionary({});

window.redirect = function (destination) {
  document.location.replace(destination);
};

const testsContext = require.context(".", true, /Spec$/);
testsContext.keys().forEach(testsContext);

App.settings.set('webroot', '/test/root/');
App.settings.set('apiroot', '/test/root/api/v2/');
EventBus.vent.reply('apiroot', function () {
  return App.settings.get('apiroot');
});
