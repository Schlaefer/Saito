(function(root, factory) {
  'use strict';
  if (typeof define === "function" && define.amd) {
    define(['jquery'], function(jQuery) {
      return factory(jQuery);
    });
  } else {
    factory(jQuery);
  }
})(this, function($) {
  'use strict';

  /**
   * Inserts text at caret position in textarea
   *
   * Usage: $('#textarea').insertAtCaret('my text');
   */
  $.fn.insertAtCaret = function(text) {
    var textarea = this[0];
    var strPos = 0,
        range;
    var browser = ((textarea.selectionStart || textarea.selectionStart === '0') ? "standard" : (document.selection ? "ie" : false));
    if (browser === "ie") {
      textarea.focus();
      range = document.selection.createRange();
      range.moveStart('character', -textarea.value.length);
      strPos = range.text.length;
    }
    else if (browser === "standard") {
      strPos = textarea.selectionStart;
    }

    var front = (textarea.value).substring(0, strPos);
    var back = (textarea.value).substring(strPos, textarea.value.length);
    textarea.value = front + text + back;
    strPos = strPos + text.length;
    if (browser === "ie") {
      textarea.focus();
      range = document.selection.createRange();
      range.moveStart('character', -textarea.value.length);
      range.moveStart('character', strPos);
      range.moveEnd('character', 0);
      range.select();
    }
    else if (browser === "standard") {
      textarea.selectionStart = strPos;
      textarea.selectionEnd = strPos;
      textarea.focus();
    }

    return this;
  };

});
