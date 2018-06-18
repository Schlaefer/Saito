import * as Bb from 'backbone';
import * as Mn from 'backbone.marionette';
import * as $ from 'jquery';
import { NoContentView as  EmptyView } from 'views/NoContentView';
import ItemView from './bookmarkItemVw';

export default class extends Mn.CollectionView<any, any, any> {
    public childView = ItemView;
    public emptyView = EmptyView;
    public emptyViewOptions = () => {
        return {
            model: new Bb.Model({ content: $.i18n.__('bkm.ncy') }),
        };
    }
}
