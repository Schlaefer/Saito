/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import CakeRestModel from 'lib/saito/backbone.cakeRest';
import _ from 'underscore';

class AppStatusModel extends CakeRestModel {
    private stream!: EventSource;

    private settings: any;

    public initialize(attributes: any, options: any) {
        super.initialize(attributes, options);
        this.settings = options.settings;
        this.methodToCakePhpUrl.read = 'status/';
    }

    public start(immediate = true) {
        this.setWebroot(this.settings.get('webroot'));
        // Don't use SSE by default on unknown server-configs
        /*
        if (!!window.EventSource) {
          this._eventStream();
          return;
        }
        */
        // slow polling just to keep the user online
        this._poll(90000, 180000, immediate);
    }

    public setWebroot(webroot: string) {
        this.webroot = webroot + 'status/';
    }

    /**
     * Request status by server-sent events
     */
    private eventStream() {
        this.stream = new EventSource(this.webroot + this.methodToCakePhpUrl.read);
        this.stream.addEventListener('message', (e) => {
            /* @todo
              if (e.origin != 'http://example.com') {
              alert('Origin was not http://example.com');
              return;
              }
              */
            const data = JSON.parse(e.data);
            this.set(data);
        }, false);
    }

    /**
     * Requests status by polling with classic HTTP request.
     *
     * Adjust to sane values taking UserOnlineTable::setOnline() into account, so
     * that users wont get set offline.  Default current default values were great
     * for a shoutbox like feature with immediate and reasonbly fast polling.
     *
     * The time between requests increases if the data from the server is
     * unchanged.
     *
     * @param {int} refreshTimeBase - minimum and start time between request in ms
     * @param {int} refreshTimeMax - maximum time between requests in ms
     * @param {bool} immediate - first request immediately or after refreshTimeBase
     */
    private _poll(refreshTimeBase = 10000, refreshTimeMax = 90000, immediate = true) {
        let   timerId: number;
        let  refreshTimeAct: number;

        const stopTimer = () => {
            if (timerId !== undefined) {
                window.clearTimeout(timerId);
            }
        };

        const resetRefreshTime = () => {
            stopTimer();
            refreshTimeAct = refreshTimeBase;
        };

        const setTimer = () => {
            timerId = window.setTimeout(
                updateAppStatus,
                refreshTimeAct,
            );
        };

        const updateAppStatus = () => {
            setTimer();
            this.fetch({
                success() {
                    refreshTimeAct = Math.floor(
                        refreshTimeAct * (1 + refreshTimeAct / 40000),
                    );
                    if (refreshTimeAct > refreshTimeMax) {
                        refreshTimeAct = refreshTimeMax;
                    }
                },
                error: stopTimer,
            });
        };

        this.listenTo(this, 'change', () => {
            resetRefreshTime();
            setTimer();
        },
        );

        if (immediate) {
            updateAppStatus();
        }

        resetRefreshTime();
        setTimer();
    }
}

export default AppStatusModel;
