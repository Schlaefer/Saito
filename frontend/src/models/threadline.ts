import CakeRestModel from 'lib/saito/backbone.cakeRest';
import App from 'models/app';
import _ from 'underscore';

class ThreadLineModel extends CakeRestModel {
    public constructor(options: any = {}) {
        _.defaults(options, {
            html: '',
            isAlwaysShownInline: false,
            isInlineOpened: false,
            isNewToUser: false,
            posting: '',
            shouldScrollOnInlineOpen: true,
        });
        super(options);
    }

    public initialize(attributes: any, options: any) {
        super.initialize(attributes, options);
        this.webroot = App.settings.get('webroot') + 'entries/';
        this.methodToCakePhpUrl.read = 'threadline/';

        this.set('isAlwaysShownInline', App.currentUser.get('user_show_inline') || false);
    }
}

export default ThreadLineModel;
