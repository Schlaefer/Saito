define(['moment', 'moment-de'], function(moment) {

  // @todo language switcher

  'use strict';

  // remove `Uhr` from german short time format
  var longDateFormat = moment().lang()._longDateFormat;
  longDateFormat.LT = 'H:mm';
  moment.lang('de', {longDateFormat: longDateFormat});

});