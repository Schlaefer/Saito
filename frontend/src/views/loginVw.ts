import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as _ from 'underscore';

class LoginVw extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            template: _.template($('#tpl-modalLoginDialog').html()),
        });
        super(options);
    }
}

export default LoginVw;
