import { Model } from 'backbone';

declare module 'backbone' {
    // tslint:disable-next-line:interface-name
    interface Model {
        toggle(key: string): void;
    }
}

/**
 * Bool toggle attribute of model
 *
 * @param attribute
 */
Model.prototype.toggle = function(attribute) {
    this.set(attribute, !Boolean(this.get(attribute)));
};
