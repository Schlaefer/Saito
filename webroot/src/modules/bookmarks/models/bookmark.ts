import { JsonApiModel } from 'lib/backbone/jsonApi';
import * as App from 'models/app.js';

export default class extends JsonApiModel {
    /** Bb urlRoot property */
    urlRoot = () => { return App.settings.get('apiroot') + 'bookmarks/'; };
};
