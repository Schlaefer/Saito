/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import Marionette from 'backbone.marionette';
import App from 'models/app.js';
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

        /// checkup browser support for hidden tab
        let hidden: string;
        let visibilityChange: string;
        if (typeof document.hidden !== 'undefined') {
            hidden = 'hidden';
            visibilityChange = 'visibilitychange';
        } else if (typeof document.msHidden !== 'undefined') {
            hidden = 'msHidden';
            visibilityChange = 'msvisibilitychange';
        } else if (typeof document.webkitHidden !== 'undefined') {
            hidden = 'webkitHidden';
            visibilityChange = 'webkitvisibilitychange';
        }

        /// browser can't detect a hidden tab
        if (hidden === undefined) {
            return;
        }

        /// tab isn't hidden
        if (!document[hidden]) {
            return;
        }

        /// set badge
        favicon.badge(text);

        /// remove badge on page activation
        const handleVisibilityChange = () => {
            if (!document[hidden]) {
                favicon.reset();
            }
        };
        if (typeof document.addEventListener !== 'undefined') {
            document.addEventListener(visibilityChange, handleVisibilityChange, false);
        }
    }
}

export default new Favicon();
