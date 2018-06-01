import { JsonApiCollection, JsonApiModel } from 'lib/backbone/jsonApi';

export default class extends JsonApiCollection {
    /** Bb collection model */
    public model = JsonApiModel;

    protected saitoUrl = 'uploads/';

    /** Bb comparator */
    public comparator = (model) => {
        // sort by latest firest (negate ID for DESC)
        return -1 * model.get('id');
    }
}
