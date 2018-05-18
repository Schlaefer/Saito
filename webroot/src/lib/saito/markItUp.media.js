import $ from 'jquery';
import _ from 'underscore';

/**
 * Helper for converting multimedia content to BBCode tags
 */
export default {
  /**
   * Filters applied before URL is evaluated
   */
  _preFilters: {
    /**
     * Convert dropbox HTML-page URL to actual file URL
     *
     * @see https://www.dropbox.com/help/201/en
     */
    dropbox: {
      cleanUp: function (text) {
        return text.replace(/https:\/\/www\.dropbox\.com\//, 'https://dl.dropbox.com/');
      }
    },

    /**
     * Resolve various youtube URL formats:
     *
     * - browser URL to video
     * - youtu.be shortener URL
     */
    youtube: {
      cleanUp: function (text) {
        var domain,
          regex,
          url = text,
          videoId;

        if (/http/.test(text) === false) {
          url = 'http://' + text;
        }

        regex = /(http|https):\/\/(\w+:?\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i;
        if (!regex.test(url)) {
          return text;
        }

        domain = url.match(/(https?:\/\/)?(www\.)?(.[^\/:]+)/i).pop();
        switch (domain) {
          case 'youtu.be':
            regex = /youtu.be\/(.*?)(&.*)?$/;
            if (regex.test(url)) {
              videoId = url.match(regex)[1];
            }
            break;
          case 'youtube.com':
            regex = /v=(.*?)(&.*)?$/;
            if (regex.test(url)) {
              videoId = url.match(regex)[1];
            }
            break;
        }

        if (videoId !== undefined) {
          text = this._createIframe({
            src: '//www.youtube.com/embed/' + videoId
          });
        }

        return text;
      },

      /**
       * Create HTML iframe tag
       *
       * @param {object} attr - iframe-tag attributes
       * @returns {string}
       * @private
       */
      _createIframe: function (attr) {
        var defaults, attributes, reducer;

        defaults = {
          width: 560,
          height: 315,
          frameborder: 0,
          allowfullscreen: 'allowfullscreen'
        };
        _.defaults(attr, defaults);


        reducer = function (memo, value, key) {
          return memo + key + '="' + value + '" ';
        };
        attributes = _.reduce(attr, reducer, '');
        attributes = attributes.trim();

        return '<iframe ' + attributes + '></iframe>';
      }
    }
  },

  /**
   * Resolve multimedia input to BBCode syntax
   *
   * @param {string} text - content to embed
   * @param {object} options - converting options
   * @returns {string}
   */
  multimedia: function (text, options) {
    var textv = $.trim(text),
      patternEnd = '([\\/?]|$)',

      patternImage = new RegExp('\\.(png|gif|jpg|jpeg|webp)' + patternEnd, 'i'),
      patternHtml = new RegExp('\\.(mp4|webm|m4v)' + patternEnd, 'i'),
      patternAudio = new RegExp('\\.(m4a|ogg|mp3|wav|opus)' + patternEnd, 'i'),
      patternFlash = /<object/i,
      patternIframe = /<iframe/i,

      out = '';

    options = options || {};
    _.defaults(options, { embedlyEnabled: false });

    _.each(this._preFilters, function (cleaner) {
      textv = cleaner.cleanUp(textv);
    });

    if (patternImage.test(textv)) {
      out = markItUp._image(textv);
    } else if (patternHtml.test(textv)) {
      out = markItUp._videoHtml5(textv);
    } else if (patternAudio.test(textv)) {
      out = markItUp._audioHtml5(textv);
    } else if (patternIframe.test(textv)) {
      out = markItUp._videoIframe(textv);
    } else if (patternFlash.test(textv)) {
      out = markItUp._videoFlash(textv);
    }

    if (options.embedlyEnabled === true && out === '') {
      out = markItUp._embedly(textv);
    }
    return out;
  },

  _image: function (text) {
    return '[img]' + text + '[/img]';
  },

  _videoFlash: function (text) {
    var html = '[flash_video]URL|WIDTH|HEIGHT[/flash_video]';

    if (text !== null) {
      html = html.replace('WIDTH', /width="(\d+)"/.exec(text)[1]);
      html = html.replace('HEIGHT', /height="(\d+)"/.exec(text)[1]);
      html = html.replace('URL', /src="([^"]+)"/.exec(text)[1]);
      return html;
    }
    return '';
  },

  _videoHtml5: function (text) {
    return '[video]' + text + '[/video]';
  },

  _audioHtml5: function (text) {
    return '[audio]' + text + '[/audio]';
  },

  _videoIframe: function (text) {
    var inner = /<iframe(.*?)>.*?<\/iframe>/i.exec(text)[1];
    inner = inner.replace(/["']/g, '');
    return '[iframe' + inner + '][/iframe]';
  },

  _embedly: function (text) {
    return '[embed]' + text + '[/embed]';
  }
};
