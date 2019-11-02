/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import App from 'models/app';
import * as _ from 'underscore';

/**
 * Drag & Drop file upload area
 */
class DragAreaVw extends View<Model> {
    /**
     * Constructor
     * @param options Ma view options
     */
    public constructor(options: any = {}) {
        _.defaults(options, {
            className: 'card-body',
            events: {
                'dragleave @ui.dropLayer': 'handleDragLeave',
                'dragover @ui.dropLayer': 'handleDragOver',
                'drop @ui.dropLayer': 'handleDrop',
            },
            modelEvents: {
                'change:uploadInProgress': 'onChangeUploadInProgress',
            },
            template: _.template(`
                <div class="js-drop imageUploader-add-dropLayer"></div>
                <div class="js-indicator imageUploader-add-dropIndicator">
                    <i class="fa fa-upload"></i>
                </div>
                <h2 class="card-title">
                    <%- $.i18n.__('upl.title') %>
                </h2>
            `),
            ui: {
                dropLayer: '.js-drop',
                heading: 'h2',
                indicator: '.js-indicator',
            },
        });
        super(...arguments);
    }

    /**
     * Ma onRender callback
     */
    public onRender() {
        const layer = this.getUI('dropLayer')[0];
        const supported = ('draggable' in layer) || ('ondragstart' in layer && 'ondrop' in layer);

        if (!supported) {
            this.getUI('dropLayer').remove();
            return;
        }

        this.getUI('heading').html($.i18n.__('upl.new.title'));
    }

    /**
     * Called when file is dropped and initiates upload
     * @param event drop event
     */
    protected handleDrop(event: JQueryEventObject) {
        this.handleDragLeave(event);
        const orgEvent = event.originalEvent as DragEvent;
        if (!orgEvent.dataTransfer) {
            return;
        }

        if (this.model.get('uploadInProgress')) {
            return;
        }

        this.model.set('fileToUpload', orgEvent.dataTransfer.files[0], {validate: true});
        const error = this.model.validationError;
        if (error) {
            App.eventBus.trigger('notification', {message: error, type: 'error'});
        }
    }

    /**
     * Called on drag over the area.
     * @param event event
     */
    protected handleDragOver(event: Event) {
        event.preventDefault();
        if (this.model.get('uploadInProgress')) {
            return;
        }
        this.showOverlay();
    }

    /**
     * Called when drag is leaving the area.
     * @param event event
     */
    protected handleDragLeave(event: Event) {
        event.preventDefault();
        if (this.model.get('uploadInProgress')) {
            return;
        }
        this.hideOverlay();
    }

    /**
     * Called when a upload starts or ends.
     * @param model This view's model.
     * @param value Is a upload in progress or did it stop?
     */
    protected onChangeUploadInProgress(model: Model, value: boolean) {
        if (value) {
            this.showOverlay();
        } else {
            this.hideOverlay();
        }
    }

    /**
     * Show upload overlay
     */
    protected showOverlay() {
        this.getUI('indicator').removeClass('fadeOut').addClass('fadeIn');
    }

    /**
     * Hide upload overlay
     */
    protected hideOverlay() {
        this.getUI('indicator').removeClass('fadeIn').addClass('fadeOut');
    }
}

export default DragAreaVw;
