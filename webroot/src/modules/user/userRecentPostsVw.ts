import * as _ from 'underscore';
import * as $ from 'jquery';
import * as Mn from 'backbone.marionette';
import AppView from 'views/app';

export default class extends Mn.View<any> {
    private template = () => {
        return _.template($('#tpl-recentposts').html());
    };
    public onRender() {
        const av = new AppView();
        av._initThreadLeafs(this.$('.threadLeaf'));
    }
};
