import { JsonApiModel } from 'lib/backbone/jsonApi';
import EventBus from 'app/vent';

export default class extends JsonApiModel {
    /** Bb urlRoot property */
    urlRoot = () => {
        // use event-bus not app/model to prevent circular initialization:
        // App->CurrentUser->Bookmarks->App where App is not available yet
        return EventBus.vent.request('apiroot') + 'bookmarks/';
    };
};
