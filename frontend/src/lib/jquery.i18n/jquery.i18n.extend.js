/**
 * Extension for i18n for CakePHP/Saito
 */

import $ from 'jquery';
import i18n from 'lib/jquery.i18n/jquery.i18n';
import format from 'string-template';

$.extend($.i18n, {
    /**
     * Localice string with tokens
     *
     * Token replacement compatible to CakePHP's localization
     *
     * @see https://github.com/Matt-Esch/string-template
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
