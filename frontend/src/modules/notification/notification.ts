import * as Mn from 'backbone.marionette';
import * as Radio from 'backbone.radio';
import * as $ from 'jquery';
import App from 'models/app';
import * as _ from 'underscore';
import * as PNotify from './../../../../node_modules/pnotify/lib/umd/PNotify.js';

enum NotificationType {
    error = 'error',
    info = 'info',
    success = 'success',
    warning = 'warning',
}

interface INotification {
    message: string;
    title?: string;
    channel?: string;
    type?: NotificationType;
}

class NotificationRenderer extends Mn.Object {
    constructor(eventBus: Radio.Channel) {
        super(...arguments);
        this.listenTo(eventBus, 'notification', this._showMessages);
        this.listenTo(eventBus, 'notificationUnset', this._unset);
    }

    /**
     * Handles message rendering
     *
     * options can be a single message:
     *
     * {
     *  `message` message to display,
     *  `title` "title (optional)",
     *  `type` "error|notice(default)|warning|success",
     *  `channel` "notification(default)|form"
     *  `element` ".input_selector" if `channel` is "form"
     * }
     *
     * or array with a msg property and a message list:
     *
     * [{message:…}, {message:…}]
     *
     * @param options
     */
    private _showMessages(message: INotification | INotification[]) {
        if (Array.isArray(message)) {
            _.each(message, function(msg) {
                this._showMessages(msg);
            }, this);

            return;
        }
        this._showMessage(message);
    }

    /**
     * Renders a single message
     *
     * @param msg single message
     * @private
     */
    private _showMessage(msg: INotification) {
        msg.channel = msg.channel || 'notification';
        // msg.title = msg.title || $.i18n.__(msg.type);
        msg.message = $.i18n.__(msg.message.trim());

        switch (msg.channel) {
            case 'popover':
            // this._popover(msg);
            // break;
            default:
                this._showNotification(msg);
                break;
        }
    }

    private _unset(msg) {
        if (msg === 'all') {
            $('.error-message').remove();
        }
    }

    private _showNotification(options: INotification) {
        const delay: number = 5000;
        const logOptions = {
            addclass: 'flash',
            delay,
            history: false,
            icon: false,
            text: options.message,
            title: options.title || false,
        };
        let type: string = options.type;

        switch (type) {
            case 'success':
                logOptions.addclass += ' flash-success';
                break;
            case 'warning':
                type = 'notice'; // changed from pnotify 1.x to 4.x
                logOptions.addclass += ' flash-warning';
                break;
            case 'error':
                logOptions.addclass += ' flash-error';
                logOptions.delay = delay * 2;
                // logOptions.hide = false;
                break;
            default:
                type = 'info';
                logOptions.addclass += ' flash-notice';
                break;
        }

        PNotify[type](logOptions);
    }
}

export default NotificationRenderer;

export { INotification };
