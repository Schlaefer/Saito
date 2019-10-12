/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import Marionette from 'backbone.marionette';
import App from 'models/app';

/**
 * Sets up and manages autoreloading the current page.
 */
class Autoreload extends Marionette.Object {
    /**
     * Autoreload timer
     */
    private autoPageReloadTimer: number|null = null;

    /**
     * Bb initialize
     */
    public initialize() {
        App.eventBus.reply('app:autoreload:start', this.initAutoreload, this);
        App.eventBus.reply('app:autoreload:stop', this.breakAutoreload, this);
    }

    /**
     * Sets up autorelad-timer
     *
     * @param period Autoreload time in seconds
     */
    private initAutoreload(period: number) {
        if (typeof period !== 'number') {
            return;
        }
        if (period < 60) {
            period = 600;
        }
        const url = window.location.pathname;
        const reload = () => {
            window.location.href = url;
        };

        this.breakAutoreload();
        this.autoPageReloadTimer = window.setTimeout(reload, period * 1000);
    }

    /**
     * Breaks autoreload by clearing the autoreload-timer
     */
    private breakAutoreload() {
        if (!this.autoPageReloadTimer) {
            return;
        }
        window.clearTimeout(this.autoPageReloadTimer);
        this.autoPageReloadTimer = null;
    }
}

export default new Autoreload();
