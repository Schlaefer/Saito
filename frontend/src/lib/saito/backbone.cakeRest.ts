import Backbone, { Model } from 'backbone';
import { defaults } from 'underscore';

interface ICakeRest {
    read: string;
    create: string;
    update: string;
    delete: string;
}

export default abstract class CakeRestModel extends Model {
    public methodToCakePhpUrl!: ICakeRest;

    public webroot!: string;

    public initialize(attributes: any, options: any) {
        this.methodToCakePhpUrl = {
            create: 'add',
            delete: 'delete',
            read: 'view',
            update: 'edit',
        };
    }

    public sync(method: string, model: Model, options: any = {}): JQueryXHR {
        this.urlRoot = this.webroot;
        options = options || {};
        const key: keyof ICakeRest = method.toLocaleLowerCase() as keyof ICakeRest;
        options.url = this.urlRoot + this.methodToCakePhpUrl[key];
        if (!this.isNew()) {
            options.url =
                options.url +
                (options.url.charAt(options.url.length - 1) === '/' ? '' : '/') +
                this.id;
        }

        return Backbone.sync(method, model, options);
    }
}
