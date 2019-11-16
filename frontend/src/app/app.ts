import EventBus from 'app/vent';
import $ from 'jquery';
import App from 'models/app';
import _ from 'underscore';

import 'app/faviconBadge';
import 'app/pageAutoreload';
import 'modules/notification/html5-notification';
import AppView from 'views/app';
import ContentTimer from './ContentTimer';

import { Application } from 'backbone.marionette';
import 'lib/jquery.i18n/jquery.i18n.extend';
import 'lib/saito/backbone.initHelper';
import moment from 'moment';
/// Load numeral.js
import numeral from 'numeral';
// load locales for numeral.js
require('numeral/locales')

interface ISaitoCallbacks {
    beforeAppInit: CallableFunction[];
    afterAppInit: CallableFunction[];
    afterViewInit: CallableFunction[];
}

interface ISaitoAppParams {
    app: {
        settings: any,
    };
    callbacks: ISaitoCallbacks;
    currentUser: any;
    request: any;
    assets: {
        lang: string,
    };
}

class Bootstrap {
    public bootstrap(SaitoApp: ISaitoAppParams) {
        const contentTimer = (new ContentTimer()).start();

        EventBus.vent.reply('webroot', () => App.settings.get('webroot'));
        EventBus.vent.reply('apiroot', () => App.settings.get('apiroot'));

        App.settings.set(SaitoApp.app.settings);

        moment.locale(App.settings.get('language'));
        numeral.locale(App.settings.get('language'));

        $.ajax({
            cache: true,
            dataType: 'json',
            mimeType: 'application/json',
            success: (data) => {
                $.i18n.setDictionary(data);
                App.currentUser.set(SaitoApp.currentUser);
                App.request.set(SaitoApp.request);

                this.configureAjax();

                const callbacks = SaitoApp.callbacks.beforeAppInit;
                _.each(callbacks, (fct) => fct());

                const appReady = () => {
                    this.fireOnPageCallbacks(SaitoApp.callbacks);
                    const appView = new AppView({ el: 'body' });
                    appView.initFromDom({ SaitoApp, contentTimer });
                };
                this.whenReady(appReady);
            },
            url: SaitoApp.assets.lang,
        });
    }

    private whenReady(callback: () => any) {
        if ($.isReady) {
            callback();
        } else {
            $(document).ready(callback);
        }
    }

    private fireOnPageCallbacks(allCallbacks: ISaitoCallbacks) {
        _.each(allCallbacks.afterAppInit, (fct) => fct());

        EventBus.vent.on('isAppVisible', _.once(() => {
            _.each(allCallbacks.afterViewInit, (fct) => fct());
        }));
    }

    private configureAjax() {
        // prevent caching of ajax results
        $.ajaxSetup({ cache: false });

        /// set CSRF-token
        $.ajaxPrefilter((options, originalOptions, xhr) => {
            if (xhr.crossDomain) {
                return;
            }
            const csrf = App.request.getCsrf();
            if (!csrf) {
                throw new Error();
            }
            xhr.setRequestHeader(csrf.header, csrf.token);
        });

        /// set JWT-token
        const jwtCookie = document.cookie.match(/Saito-JWT=([^\s;]*)/);
        if (!jwtCookie) {
            return;
        }
        App.settings.set('jwt', jwtCookie[1]);

        $.ajaxPrefilter((options, originalOptions, xhr) => {
            if (xhr.crossDomain) {
                return;
            }
            xhr.setRequestHeader('Authorization', 'bearer ' + App.settings.get('jwt'));
        });
    }
}

const AppInstance = new Application({ channelName: 'app', region: '' });

AppInstance.on('start', (event: Event, options: {SaitoApp: ISaitoAppParams} ) => {
    new Bootstrap().bootstrap(options.SaitoApp);
});

export default AppInstance;
