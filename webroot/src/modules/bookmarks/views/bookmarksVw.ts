import * as $ from 'jquery';
import * as Bb from 'backbone';
import * as Mn from 'backbone.marionette';
import EmptyView from 'views/noContentYetVw';
import ItemView from './bookmarkItemVw';

export default class extends Mn.CollectionView<any, any, any> {
    childView = ItemView;
    emptyViewx = EmptyView;
    emptyViewOptions = () => {
        return {
            model: new Bb.Model({ content: 'bkm.ncy' })
        }
    };
};
