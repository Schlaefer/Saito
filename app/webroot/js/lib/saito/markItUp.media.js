define(['jquery', 'underscore'], function($, _) {

    "use strict";

    var markItUp = {

        multimedia: function(text, options) {
            var textv = $.trim(text);

            var patternHtml = /\.(mp4|webm|m4v)$/i;
            var patternAudio = /\.(m4a|ogg|mp3|wav|opus)$/i;
            var patternFlash = /<object/i;
            var patternIframe = /<iframe/i;

            var out = '';

            options = options || {};

            _.extend(
                {
                   embedlyEnabled: false
                },
                options
            );

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

            if ( options.embedlyEnabled === true && out === '' ) {
                out = markItUp._embedly(textv);
            }

            return out;

        },

        _videoFlash: function(text) {
            var html = "[flash_video]URL|WIDTH|HEIGHT[/flash_video]";

            if (text !== null) {
                html = html.replace('WIDTH', /width="(\d+)"/.exec(text)[1]);
                html = html.replace('HEIGHT', /height="(\d+)"/.exec(text)[1]);
                html = html.replace('URL', /src="([^"]+)"/.exec(text)[1]);
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
            return '[iframe' + inner + '][/iframe]';
        },

        _videoFallback: function(text) {
            var out = '',
                videoId;

            if ( /http/.test(text) === false ) {
                text = 'http://' + text;
            }

            if ( /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i.test(text) ) {
                var domain = text.match(/(https?:\/\/)?(www\.)?(.[^\/:]+)/i).pop();
                // youtube shortener
                if ( domain === 'youtu.be' ) {
                    if ( /youtu.be\/(.*?)(&.*)?$/.test(text) ) {
                        videoId = text.match(/youtu.be\/(.*?)(&.*)?$/)[1];
                        out = markItUp._createIframe({
                            url: 'http://www.youtube.com/embed/'+videoId
                        });
                        out = markItUp._videoIframe(out);
                        return out;
                    }
                }
                // youtube
                if ( domain === 'youtube.com' ) {
                    if ( /v=(.*?)(&.*)?$/.test(text) ) {
                        videoId = text.match(/v=(.*?)(&.*)?$/)[1];
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
            return '<iframe src="' + args.url + '" width="425" height="349" frameborder="0" allowfullscreen></iframe>';
        }

    };

    return markItUp;

});
