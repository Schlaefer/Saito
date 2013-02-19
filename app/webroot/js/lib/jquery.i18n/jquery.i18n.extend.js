/**
 * Extension for i18n for CakePHP/Saito
 */
define([
    'jquery',
    'lib/jquery.i18n/jquery.i18n'
], function($) {

    $.extend($.i18n, {

        currentString: '',

        setDict: function(dict) {
           this.dict = dict;
        },

        setUrl: function(dictUrl) {
            this.dictUrl = dictUrl;
            this._loadDict();
        },

        _loadDict: function() {
            return $.ajax({
                url: this.dictUrl,
                dataType: 'json',
                async: false,
                success: $.proxy(function(data) {
                    this.dict = data;
                }, this)
            });
        },

        /**
         * Localice string with tokens
         *
         * Token replacement compatible to CakePHP's String::insert()
         *
         */
        __: function(string, tokens) {
            var out = '';

            if (typeof this.dict[string] === 'string' && this.dict[string] !== "") {
                out = this.dict[string];
                if (typeof tokens === 'object') {
                    out = this._insert(out, tokens);
                }
            } else {
                out = string;
            }

            return out;

        },

        _insert: function(string, tokens) {
            return string.replace(/:([-\w]+)/g, function(token, match, number, text){
                if(typeof tokens[match] !== "undefined") {
                    return tokens[match];
                }
                return token;
            });
        }
    });

});
