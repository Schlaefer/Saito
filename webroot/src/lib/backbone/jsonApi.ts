import * as Bb from 'backbone';

class JsonApiModel extends Bb.Model {
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
    };
};

class JsonApiCollection extends Bb.Collection<JsonApiModel> {
    /** Bb response parser */
    public parse(response: any, options?: any) {
        return response.data;
    };
};

export {
    JsonApiCollection,
    JsonApiModel,
};
