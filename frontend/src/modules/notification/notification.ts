import EventBus from 'app/vent';
import { Collection, Model } from 'backbone';
import * as Mn from 'backbone.marionette';
import * as $ from 'jquery';
import * as _ from 'underscore';

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

class NotificationView extends Mn.View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            attributes: {
                'aria-atomic': 'true',
                'aria-live': 'assertive',
                'role': 'alert',
            },
            className: 'toast',
            events: {
                'hidden.bs.toast': 'onClosed',
            },
            template: _.template(`
<div class="toast-header">
    <svg class="bd-placeholder-img
        <%= addclass %>"
        width="20" height="20"
        xmlns="http://www.w3.org/2000/svg"
        preserveAspectRatio="xMidYMid slice"
        focusable="false"
        role="img">
    </svg>
    <strong class="mr-auto"><%- title %></strong>
    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="toast-body">
    <%- text %>
</div>
`),
            ui: {
                toast: '.toast',
            },

        });
        super(options);
    }

    public onRender() {
        this.$el
            .toast({autohide: this.model.get('autohide'), delay: this.model.get('delay')})
            .toast('show');
    }

    public onClosed() {
        this.model.collection.remove(this.model);
    }
}

class NotificationsCollectionView extends Mn.CollectionView<Model, Mn.View<Model>, Collection<Model>> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            childView: NotificationView,
        });
        super(...arguments);
    }
}

class NotificationsView extends Mn.View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            template: _.template(`<div class="notifications";"></div>`),
            ui: {
                inner: '.notifications',
            },
        });
        super(...arguments);
    }

    public initialize() {
        this.collection = new Collection();
        this.listenTo(EventBus.vent, 'notification', this.showMessages);
    }

    public onRender() {
        new NotificationsCollectionView({
            collection: this.collection,
            el: this.getUI('inner'),
        }).render();
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
    private showMessages(message: INotification | INotification[]) {
        if (Array.isArray(message)) {
            _.each(message, function(msg) {
                this.showMessages(msg);
            }, this);

            return;
        }
        this.showMessage(message);
    }

    /**
     * Renders a single message
     *
     * @param msg single message
     * @private
     */
    private showMessage(message: INotification) {
             const logOptions = {
                addclass: 'bg-info',
                autohide: true,
                delay: 5000,
                text: $.i18n.__(message.message.trim()),
                title: message.title || '',
            };
             const type: string = message.type;

             switch (type) {
                case 'success':
                    logOptions.addclass = 'bg-success';
                    break;
                case 'warning':
                    logOptions.addclass = 'bg-warning';
                    logOptions.autohide = false;
                    break;
                case 'error':
                    logOptions.addclass = 'bg-danger';
                    logOptions.autohide = false;
                    break;
                default:
            }

             this.collection.add(new Model(logOptions));
        }
    }

export default NotificationsView;
