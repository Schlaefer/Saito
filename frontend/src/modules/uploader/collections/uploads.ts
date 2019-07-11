import { JsonApiCollection, JsonApiModel } from 'lib/backbone/jsonApi';

class UploadsModel extends JsonApiModel {
    protected saitoUrl = 'uploads/';
}

export default class extends JsonApiCollection {
    /** Bb collection model */
    public model = UploadsModel;

    protected saitoUrl = 'uploads/';

    /** Bb comparator */
    public comparator = (model) => {
        // sort by latest first (negate ID for DESC)
        return -1 * model.get('id');
    }
}
