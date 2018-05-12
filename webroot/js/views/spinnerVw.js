define([
  'marionette',
  'text!templates/spinner.html',
], function (
  Marionette,
  Tpl,
  ) {
    'use strict';
    return Marionette.View.extend({
      className: 'spinner',
      template: Tpl,
    });
  });
