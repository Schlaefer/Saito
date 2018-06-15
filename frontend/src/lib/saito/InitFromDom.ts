import { Collection, Model, View} from 'backbone';

class InitFromDom {
    /**
     * Init all subviews (models and views) from DOM elements
     *
     * @param element
     * @param collection
     * @param view
     */
    public static initCollectionFromDom(
        element: string,
        clt: Collection<Model>,
        view: { new(options: any) } ) {
        const createElement = (collection: Collection<Model>, id: string, el: JQuery) => {
            collection.add({ id });
            const a = new view({
                collection,
                el,
                model: collection.get(id),
            });
        };

        $(element).each((index, el) => {
            createElement(clt, $(el).data('id'), $(el));
        });
    }
}

export { InitFromDom };
