define([
  'underscore',
  'marionette',
  'text!templates/noContentYetTpl.html',
], function (
  _,
  Marionette,
  Tpl,
) {
  'use strict';
  return Marionette.View.extend({
    template: _.template(Tpl),
  });
});
