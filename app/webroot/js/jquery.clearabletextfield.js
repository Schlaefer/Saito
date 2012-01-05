 /*
  * Clearable Text Field - jQuery plugin version 0.3.2
  * Copyright (c) 2009 Tatsuya Ono
  *
  * http://github.com/ono/clearable_text_field
  *
  * Dual licensed under the MIT and GPL licenses:
  *   http://www.opensource.org/licenses/mit-license.php
  *   http://www.gnu.org/licenses/gpl.html
  */
(function($) {
  $.fn.clearableTextField = function() {
    if ($(this).length>0) {
      $(this).bind('keyup change paste cut', onSomethingChanged);
    
      $(this).each( function(){
        $(this).data('original-padding-right', $(this).css('padding-right'));
        $(this).data('original-width', $(this).width());
        trigger($(this));
      });      
    }
  }
  
  function onSomethingChanged() {
    trigger($(this), true);
  }
  
  function trigger(input, set_focus) {
    if(input.val().length>0){
      add_clear_button(input, set_focus);
    } else {
      remove_clear_button(input, set_focus);
    }    
  }
  
  function add_clear_button(input, set_focus) {
    if (input.attr('has_clearable_button')!="1") {
      input.attr('has_clearable_button',"1");
      var wrap = input.parent();
      if (!wrap.hasClass('clear_button_wrapper')) {
        wrap = input.wrap('<div class="clear_button_wrapper" style="margin:0;padding:0;position:relative; display:inline;" />');
      }
      
      // appends div
      input.after("<div class='text_clear_button'></div>");
    
      var clear_button = input.next();
      var w = clear_button.outerHeight(), h = clear_button.outerHeight();
      
      input.css('padding-right', parseInt(input.data('original-padding-right')) + w + 1 + 4);
      input.width(input.width() - w - 1 - 4);
          
      var pos = input.position();
      var style = {};  
      style['left'] = pos.left + input.outerWidth(false) - (w+2+5);
      var offset = Math.round((input.outerHeight(true) - h)/2.0);
      style['top'] = pos.top + offset;
            
      clear_button.css(style);
          
      clear_button.click(function(){
        input.val('');
        trigger(input);
        input.change();
      });
      
      if (set_focus && set_focus!=undefined) input.focus();
    }
  }
  
  function remove_clear_button(input, set_focus) {
    var clear_button = input.next();
    
    if (input.attr('has_clearable_button')=="1") {
      input.removeAttr('has_clearable_button');
      clear_button.remove();
      var w = clear_button.width();

      input.css('padding-right', parseInt(input.data('original-padding-right')));
      input.width(input.data('original-width'));
    }

    if (set_focus && set_focus!=undefined) input.focus();
  }
  
})(jQuery);