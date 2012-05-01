/**
 * Modal Box for Login Form start
 */
$(document).ready(function() {
  if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {
    return;
  }
  $("#showLoginForm").click(function(event) {
    event.preventDefault();
    showLoginForm(this);
  });
});

function showLoginForm(elem) {

  var url = elem.href;
  $('#modalLoginDialog').height('auto');
  var title= elem.title;
  $('#modalLoginDialog').dialog({
    modal: true,
    title: title,
    width: 420,
    show: 'fade',
    hide: 'fade',
    position: ['center', 120]
  });
  return false;
};
/* Modal Box for Login Form end */

function entries_add_toggle(id) {
  if ($('#posting_formular_slider_' + id ).css('display') == 'none') {
    $('.signature').slideDown('fast');
    $('.c_a_a_b').slideDown('fast');
    // we have #id problems with more than one markItUp on a page
    var html = '<div id="spinner_' + id +'" class="spinner"></div>';
    $('.posting_formular_slider').slideUp('fast').html(html);
  }
  $('#posting_formular_slider_' + id ).slideToggle('fast');
  $('#signature_' + id).slideToggle('fast');
  $('#a_a_b_' + id).slideToggle('fast');
};

function post_to_url_t_a(id, params) {
  var path = webroot + 'entries/view/' + id;
  post_to_url(path, params);

  return false;
};

function post_to_url(path, params, method) {
  method = method || "post";

  var form = document.createElement("form");
  form.setAttribute("method", method);
  form.setAttribute("action", path);

  for(var key in params) {
    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", key);
    hiddenField.setAttribute("value", params[key]);

    form.appendChild(hiddenField);
  }

  document.body.appendChild(form);
  form.submit();

  return false;
};

function _isScrolledIntoView(elem) {
  if ($(elem).length == 0) return true;
  var docViewTop = $(window).scrollTop();
  var docViewBottom = docViewTop + $(window).height();

  var elemTop = $(elem).offset().top;
  var elemBottom = elemTop + $(elem).height();

  return ((elemBottom >= docViewTop) && (elemTop <= docViewBottom));
};

function _isHeigherThanView(elem)
{
  return ($(window).height() <= $(elem).height())	;
};

function scrollToBottom(elem) {
  $(window).delay(1600).scrollTo(elem, 400, {
    'offset': -$(window).height()+30,
    easing: 'swing'
  })
};

function scrollToTop(elem) {
  $(window).scrollTo(elem , 400, {
    'offset': 0,
    easing: 'swing'
  });
};

function getElementIdFromClassOfObject(elem) {
  return elem.attr('class').split(' ')[1];
};

/** markitup helpers start **/
$(document).ready(function() {
  $('body').delegate('#markitup_media_btn', 'click', function(event) {
    event.preventDefault();
		
    $('#markitup_media_message').hide();

    var out = '';
    out = markItUp.multimedia($('#markitup_media_txta').val());

    if ( out == '' ) {
      $('#markitup_media_message').show();
      $('#markitup_media').dialog().parent().effect("shake", {
        times:2
      }, 60);
    } else {
      $.markItUp( {
        replaceWith: out
      });
      $('#markitup_media').dialog('close');
      $('#markitup_media_txta').val('');
    }
  });
});

var markItUp = {

  multimedia: function(text) {
    var textv = $.trim(text);

    var patternHtml = /\.(mp4|webm|m4v)$/i;
    var patternAudio = /\.(m4a|ogg|mp3|wav)$/i;
    var patternFlash = /\<object/i;
    var patternIframe = /\<iframe/i;

    if ( patternHtml.test(textv) ) {
      out = markItUp._videoHtml5(textv);
    } else if ( patternAudio.test(textv) ) {
      out = markItUp._audioHtml5(textv);
    } else if ( patternIframe.test(textv) ) {
      out = markItUp._videoIframe(textv);
    } else if ( patternFlash.test(textv) ) {
      out = markItUp._videoFlash(textv);
    } else {
      out = markItUp._videoFallback(textv);
    }

    return out;

  },

  _videoFlash: function(text) {
    var html = "[flash_video]URL|WIDTH|HEIGHT[/flash_video]";

    if (text !== null) {
      html = html.replace('WIDTH', /width="(\d+)"/.exec(text)[1]);
      html = html.replace('HEIGHT', /height="(\d+)"/.exec(text)[1]);
      html = html.replace('URL', /src="([^\"]+)"/.exec(text)[1]);
      return html;
    }
    else {
      return '';
    }
  },

  _videoHtml5: function(text) {
    return	'[video]' + text + '[/video]';
  },

  _audioHtml5: function(text) {
    return	'[audio]' + text + '[/audio]';
  },

  _videoIframe: function(text) {
    var inner = /<iframe(.*?)>.*?<\/iframe>/i.exec(text)[1];
    inner = inner.replace(/["']/g, '');
    var out = '[iframe' + inner + '][/iframe]';
    return out;
  },

  _videoFallback: function(text) {
    var out = '';

    if ( /http/.test(text) == false ) {
      text = 'http://' + text;
    }

    if ( /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i.test(text) ) {
      var domain = text.match(/(https?:\/\/)?(www\.)?(.[^/:]+)/i).pop();
      // youtube shortener
      if ( domain == 'youtu.be' ) {
        if ( /youtu.be\/(.*?)(&.*)?$/.test(text) ) {
          var videoId = text.match(/youtu.be\/(.*?)(&.*)?$/)[1];
          out = markItUp._createIframe({
            url: 'http://www.youtube.com/embed/'+videoId
          });
          out = markItUp._videoIframe(out);
          return out;
        }
      }
      // youtube
      if ( domain == 'youtube.com' ) {
        if ( /v=(.*?)(&.*)?$/.test(text) ) {
          var videoId = text.match(/v=(.*?)(&.*)?$/)[1];
          out = markItUp._createIframe({
            url: 'http://www.youtube.com/embed/'+videoId
          });
          out = markItUp._videoIframe(out);
        }
        return out;
      }
    // is valid url

    }
    return out;
  },

  _createIframe: function(args) {
    return '<iframe src="' + args.url + '" width="425" height="349" frameborder="0" allowfullscreen></iframe>'
  },

};

/** markitup helpers end **/

/**
 * Upload image box start
 */

/**
 * let's greybox call js in parent window
 */
function greyboxGetParentFunction(funcName) {
  var func = null;
  // Child opened in new window e.g. target="blank"
  if (top.window.opener && !top.window.opener.closed) {
    try {
      func = eval("top.window.opener."+funcName);
    } catch (error) {}
  }
  if (!(func)) {
    // Child opened in IFRAME
    try {
      func = eval("top."+funcName);
    } catch (error) { }
  }
  if (!(func)) {
    throw new Error("function \""+funcName+"\" is not in parent window.");
  }
  return func;
}; // end getParentFunction()

function greyboxInsertIntoMarkitup(message) {
  //	$.markItUp({
  //		openWith: message
  //	})
  insertAtCaret('EntryText', message);
  // close the dialog
  //	$(".ui-dialog-titlebar-close").click()
  $dialog.dialog('close');
};


function insertAtCaret(areaId,text) {
  var txtarea = document.getElementById(areaId);
  //    var scrollPos = txtarea.scrollTop;
  var strPos = 0;
  var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
    "ff" : (document.selection ? "ie" : false ) );
  if (br == "ie") {
    txtarea.focus();
    var range = document.selection.createRange();
    range.moveStart ('character', -txtarea.value.length);
    strPos = range.text.length;
  }
  else if (br == "ff") strPos = txtarea.selectionStart;

  var front = (txtarea.value).substring(0,strPos);
  var back = (txtarea.value).substring(strPos,txtarea.value.length);
  txtarea.value=front+text+back;
  strPos = strPos + text.length;
  if (br == "ie") {
    txtarea.focus();
    var range = document.selection.createRange();
    range.moveStart ('character', -txtarea.value.length);
    range.moveStart ('character', strPos);
    range.moveEnd ('character', 0);
    range.select();
  }
  else if (br == "ff") {
    txtarea.selectionStart = strPos;
    txtarea.selectionEnd = strPos;
    txtarea.focus();
  }
//    txtarea.scrollTop = scrollPos;
}

/**
 * Shows modal jQuery dialog for image upload in Posting form
 */
var dialog;
function showUploadDialog(link) {
  //	event.preventDefault();
  $dialog = $('<iframe id="uploadDialog" frameborder=0 src="' + link + '"></iframe>')
  //		var $dialog = $('<div></div>')
  .load(link)
  .dialog({
    autoOpen: false,
    title: 'Upload',
    width: 850,
    height: 500,
    modal: true,
    //			show: 'fade',
    hide: 'fade',
    position: ['center', 20]
  });
  $dialog.dialog('open').css('width', '830').css('height', '95%');
  $dialog.parent().css('height', '90%');
  return false;

}

/** Upload image box end  **/




/*** slidetabs start ***/
function layout_slidetabs_toggle(id) {
  if ($(id).width() < 100) {
    $(id).animate({
      'width': 250
    });
    $(id + ' .content_wrapper').css( 'display','block');
  }
  else {
    $(id).animate({
      'width': 28
    },
    function() {
      $(id + ' .content_wrapper').css('display', 'none')
    }
    );
  }
};
/*** slidetabs end ***/

/*** start view_posting ***/
/**
 * Inits all JS vor a viewed posting
 *
 * The inline view inits with parameter id. This makes shure not all
 * .button_mod_panel but only the newly loaded get the behavior attached.
 * Prevent multiple attachs.
 */
function initViewPosting(id) {
  var id_class = '';
  if(id != undefined) {
    id_class = '.' + id;
  }
  /*** start close button long ***/
  console.log( $('.btn-strip-top'));
  $('.btn-strip-top').unbind('click').bind("click", function (event) {
    new ThreadLine($(this).data('id')).toggle_inline_view();
    event.preventDefault();
  });
  /*** end close button long ***/

  /*** start mod and admin panel ***/
  $('.button_mod_panel .left').addClass('pointer');

  $('.button_mod_panel' + id_class + ' .left').toggle(
    function() {
      // einblenden
      var id = getElementIdFromClassOfObject($(this));
      $('.button_mod_panel.' + id).css('height', 'auto');
      $('.button_mod_panel.' + id + ' .right').css({
        display: 'block'
      });
      $('.button_mod_panel.' + id).animate({
        width: '150px'
      });
    },
    function() {
      // ausblenden
      var id = getElementIdFromClassOfObject($(this));
      $('.button_mod_panel.' + id).animate({
        width: '16px'
      }, function(){
        $('.button_mod_panel.' + id).css('height', '16px');
        $('.button_mod_panel.' + id + ' .right').css({
          display: 'none'
        });
      });
    }
    /*** end mod and admin panel ***/
    ); // end toggle()
}; // initViewPosting()

/**
 * inits all js for viewing the answering form
 * 
 */
function initViewAnswerForm() {
  $('.postingform input[type=text]:first').focus();
} // initViewAnswerForum

function initEntryAdd() {

  // prevent accidently submitting an answer twice
  $('#content').delegate('.btn-submit', 'click',
    function () {
      $(this).attr('disabled', 'disabled');
    }
    );

} // end initViewAd()

function slidetabsMakeSortable() {
  $('#slidetabs').sortable( {
    start: function(event, ui) {
      $('#slidetabs').css('overflow', 'visible');
    },
    stop: function(event, ui) {
      $('#slidetabs').css('overflow', 'hidden');
    },
    update: function(event, ui) {
      var slidetabsOrder = $(this).sortable('toArray');
      $.ajax({
        type: 'POST',
        url: webroot + 'users/ajax_set',
        data: {
          data : {
            User: {
              slidetab_order: slidetabsOrder
            }
          }
        },
        dataType: 'json'
      });
    }
  });
};

function initViewLine(id) {
  var id_class = '';
  if(id != undefined) {
    id_class = '.' + id;
  }

  $(".btn_show_thread" + id_class).bind("click", function (event) {
    new ThreadLine(getElementIdFromClassOfObject($(this))).load_inline_view();
    event.preventDefault();
  });

  if (user_show_inline == 1) {
    $(".link_show_thread" + id_class).bind("click", function (event) {
      new ThreadLine(getElementIdFromClassOfObject($(this))).load_inline_view();
      event.preventDefault();
    });
  }
};

/************* document ready *******************/
$(document).ready( function() {
  Thread.init()
  initViewPosting();
  initViewLine();
  slidetabsMakeSortable();

  // new posting entries/add
  initViewAnswerForm();
}); // end ready()

// expand search field
$(document).ready(function() {
  $("#EntrySearchTerm").focus(function () {
    if ($(this).width() < 300 ) {
      $(this).animate({
        width: '350px'
      },
      "fast"
      );
    }
  });
});

// start toggle [code] highlight/plain
$(document).ready(function() {
  $('.entry').delegate('.geshi-plain-text', 'click', function(event) {

    event.preventDefault();

    // selects '.code' block following the button
    var block = jQuery(this).next();

    // build plain text
    var htmlText = block.html();
    var plainText = "";
    if (jQuery.browser.msie) {
      plainText = htmlText.replace(/\n/g, "+");
      plainText = jQuery(plainText).text().replace(/\+\+/g, "\r");
    } else {
      plainText = block.text().replace(/code /g, "code \n");
    }

    // button action
    if (jQuery(this).html() !== "Show Highlighted Code") {
      jQuery(this).html("Show Highlighted Code");
      block.text(plainText).wrapInner("<pre class=\"code\"></pre>");
      block.data('htmlText', htmlText);
    } else {
      jQuery(this).html("Show Plain Text");
      block.html(block.data('htmlText'));
    }

  }); // end function(event)
}); // end toggle [code]
