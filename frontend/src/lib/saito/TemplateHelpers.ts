import * as _ from 'underscore';

class LayoutHelper {
    public static panelHeading(content: object): string {
        const elements = _.pick(content, 'first', 'middle', 'last');
        _.defaults(content, {first: '', middle: '', last: ''});

        let out = '';
        _.each(elements, (element: string, key) => {
            out += '<div class="' + key + '">';
            out += _.escape(element);
            out += '</div>';
        });

        return out;
    }
}

export {
    LayoutHelper,
};
