import $ from 'jquery';
import 'lib/jquery.i18n/jquery.i18n.extend.js';
import Bootstrap from 'bootstrap';
import 'lib/saito/backbone.modelHelper';
import 'lib/saito/underscore.extend';
import App from 'models/app';

$.fx.off = true;
window.$ = $;

// prevent appending of ?_<timestamp> requested urls
$.ajaxSetup({cache: true});

// make empty dict available for test cases
$.i18n.setDict({});

window.redirect = function (destination) {
  document.location.replace(destination);
};

const testsContext = require.context(".", true, /Spec$/);
testsContext.keys().forEach(testsContext);

App.settings.set('webroot', '/test/root/');
