import EventBus from 'app/vent';
import * as Bb from 'backbone';

abstract class JsonApiModel extends Bb.Model {
    /** Saito URL resource identifier */
    protected abstract saitoUrl: string;

    /** Bb URL property */
    public urlRoot = () => {
        // use event-bus not app/model to prevent circular initialization:
        // App->CurrentUser->Bookmarks->App where App is not available yet
        return EventBus.vent.request('apiroot') + this.saitoUrl;
    }

    /** Bb respone parser */
    public parse(response: any, options?: any) {
        let data = response;

        // empty response from server (204)
        if (!data) {
            return data;
        }

        // single item is requested from server
        if ('data' in data) {
            data = response.data;
        }

        // item data from attributes
        if ('attributes' in data) {
            data = data.attributes;
        }

        return data;
    }
}

abstract class JsonApiCollection extends Bb.Collection<JsonApiModel> {
    /** Saito URL resource identifier */
    protected abstract saitoUrl: string;

    /** Bb URL property */
    public url = () => EventBus.vent.request('apiroot') + this.saitoUrl;

    /** Bb response parser */
    public parse(response: any, options?: any) {
        return response.data;
    }
}

export {
    JsonApiCollection,
    JsonApiModel,
};
