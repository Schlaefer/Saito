import EventBus from 'app/vent';
import { JsonApiModel } from 'lib/backbone/jsonApi';

export default class extends JsonApiModel {
    public urlRoot = () => {
        return EventBus.vent.request('webroot') + 'preview/preview';
    }
}
