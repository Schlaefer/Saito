/**
 * Extension for i18n for CakePHP/Saito
 */
define([
    'jquery',
    'lib/jquery.i18n/jquery.i18n'
], function ($) {
    'use strict';

    $.extend($.i18n, {

        currentString: '',

        setDict: function (dict) {
            this.dict = dict;
        },

        setUrl: function (dictUrl) {
            this.dictUrl = dictUrl;
            this._loadDict();
        },

        _loadDict: function () {
            var success = function(data) {
                this.dict = data;
            };
            success = $.proxy(success, this);
            return $.ajax({
                url: this.dictUrl,
                dataType: 'json',
                async: false,
                cache: true
            }).done(success);
        },

        /**
         * Localice string with tokens
         *
         * Token replacement compatible to CakePHP's String::insert()
         *
         */
        __: function (string, tokens) {
            var isTranslated;
            var translation = string;

            if (typeof this.dict[string] === 'string') {
                isTranslated = this.dict[string] !== '';
                if (isTranslated) {
                    translation = this.dict[string];
                }
            }
            if (typeof tokens === 'object') {
                translation = this._insert(translation, tokens);
            }
            return translation;
        },

        _insert: function (string, tokens) {
            return string.replace(/:([-\w]+)/g, function (token, match) {
                if (typeof tokens[match] !== 'undefined') {
                    return tokens[match];
                }
                return token;
            });
        }
    });

});
