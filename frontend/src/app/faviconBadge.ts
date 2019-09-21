/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import Marionette from 'backbone.marionette';
import App from 'models/app';
// tslint:disable-next-line
const Favico = require('favico.js');

/**
 * Shows content on favicon
 */
class Favicon extends Marionette.Object {
    /**
     * Bb initialize
     */
    public initialize() {
        App.eventBus.reply('app:favicon:badge', this.set, this);
    }

    /**
     * Shows a string
     */
    private set(text: string) {
        const favicon = new Favico({
            animation: 'fade',
            type: 'rectangle',
        });

        let visibilityChange: string | null = null;

        const isHiddenFct = (): boolean | undefined => {
            /// checkup browser support for hidden tab
            let hidden: boolean | undefined;
            if (typeof document.hidden !== 'undefined') {
                hidden = document.hidden;
                visibilityChange = 'visibilitychange';
            } else if (typeof document.msHidden !== 'undefined') {
                hidden = document.msHidden;
                visibilityChange = 'msvisibilitychange';
            } else if (typeof document.webkitHidden !== 'undefined') {
                hidden = document.msHidden;
                visibilityChange = 'webkitvisibilitychange';
            }

            return hidden;
        };

        /// browser can't detect a hidden tab or tab isn't hidden
        const isHidden = isHiddenFct();
        if (isHidden === undefined || isHidden === false) {
            return;
        }

        /// set badge
        favicon.badge(text);

        /// remove badge on page activation
        const handleVisibilityChange = () => {
            if (!isHiddenFct()) {
                favicon.reset();
            }
        };
        if (typeof document.addEventListener !== 'undefined' && visibilityChange) {
            document.addEventListener(visibilityChange, handleVisibilityChange, false);
        }
    }
}

export default new Favicon();
