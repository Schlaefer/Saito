/**
 * Extension for i18n for CakePHP/Saito
 */

import $ from 'jquery';
import i18n from 'lib/jquery.i18n/jquery.i18n';
import format from 'string-template';

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
            translation = format(translation, tokens);
        }
        return translation;
    },
});
