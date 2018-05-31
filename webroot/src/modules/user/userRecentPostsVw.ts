import * as Mn from 'backbone.marionette';
import * as $ from 'jquery';
import * as _ from 'underscore';
import AppView from 'views/app';

export default class extends Mn.View<any> {
    public onRender() {
        const av = new AppView();
        av._initThreadLeafs(this.$('.threadLeaf'));
    }

    private template = () => {
        return _.template($('#tpl-recentposts').html());
    }
}
