import { Collection, Model } from 'backbone';
import { CollectionView } from 'backbone.marionette';
import * as _ from 'underscore';
import { AbstractMenuButtonView } from './MenuButton/AbstractMenuButtonView';
import { MenuButtonEncloseView } from './MenuButton/MenuButtonEncloseView';
import { MenuButtonLinkView } from './MenuButton/MenuButtonLinkView';
import { MenuButtonMediaView } from './MenuButton/MenuButtonMediaView';
import { MenuButtonSeparator } from './MenuButton/MenuButtonSeparator';
import { MenuButtonSmiliesView } from './MenuButton/MenuButtonSmiliesView';
import { MenuButtonUploadView } from './MenuButton/MenuButtonUploadView';

enum MenuButtonType {
    enclose = 'enclose',
    link = 'saito-link',
    media = 'saito-media',
    separator = 'separator',
    smilies = 'saito-smilies',
    upload = 'saito-upload',
}

class MenuButtonBarView extends CollectionView<Model, AbstractMenuButtonView, Collection<Model>> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            childView: (model: Model) => {
                const type = model.get('type');
                switch (type) {
                    case MenuButtonType.enclose:
                        return MenuButtonEncloseView;
                    case MenuButtonType.separator:
                        return MenuButtonSeparator;
                    case MenuButtonType.link:
                        return MenuButtonLinkView;
                    case MenuButtonType.media:
                        return MenuButtonMediaView;
                    case MenuButtonType.upload:
                        return MenuButtonUploadView;
                    case MenuButtonType.smilies:
                        return MenuButtonSmiliesView;
                    default:
                        throw new Error('Editor button type "' + type + '" not recognized.');
                }
            },
            className: 'markupButtons',
            tagName: 'ul',
        });
        super(options);
    }
}

export { MenuButtonBarView };
