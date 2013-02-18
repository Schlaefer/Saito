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
	$(window).delay(1600).scrollTo(elem, 300, {
		'offset': -$(window).height()+30,
		easing: 'swing'
	})
};

function scrollToTop(elem) {
	$(window).scrollTo(elem , 300, {
		'offset': 0,
		easing: 'swing'
	});
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
		var patternAudio = /\.(m4a|ogg|mp3|wav|opus)$/i;
		var patternFlash = /\<object/i;
		var patternIframe = /\<iframe/i;

		var out = '';

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

		if ( SaitoApp.app.settings.embedly_enabled == 1 && out === '' ) {
			out = markItUp._embedly(textv);
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
		}
		return out;
	},

	_embedly: function(text) {
		return '[embed]' + text + '[/embed]';
	},

	_createIframe: function(args) {
		return '<iframe src="' + args.url + '" width="425" height="349" frameborder="0" allowfullscreen></iframe>'
	}

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
