import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import App from 'models/app';
import * as _ from 'underscore';
import { SpinnerView } from 'views/SpinnerView';
import Template from '../templates/uploaderAddTpl.html';

class UploaderAddVw extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            className: 'imageUploader-add',
            events: {
                'change @ui.inputFile': 'uploadManual',
                'dragleave @ui.dropLayer': 'handleDragLeave',
                'dragover @ui.dropLayer': 'handleDragOver',
                'drop @ui.dropLayer': 'handleDrop',
            },
            regions: {
                spinner: '.js-imageUploadSpinner',
            },
            template: Template,
            ui: {
                dropLayer: '.js-drop',
                heading: 'h2',
                indicator: '.js-indicator',
                inputFile: '#Upload0File',
            },
        });
        super(...arguments);
    }

    public onRender() {
        this.initDropUploader();
    }

    private uploadManual(event: Event) {
        event.preventDefault();

        const formData = new FormData();
        const input: any = this.getUI('inputFile')[0];
        formData.append(
            input.name,
            input.files[0],
        );

        this.send(formData);
    }

    /**
     * Sends form-data via ajax
     */
    private send(formData: FormData) {
        this.showChildView('spinner', new SpinnerView());

        const xhr = new XMLHttpRequest();
        xhr.open('POST', App.settings.get('apiroot') + 'uploads');
        xhr.setRequestHeader('Accept', 'application/json, text/javascript');
        xhr.setRequestHeader('Authorization', 'bearer ' + App.settings.get('jwt'));

        const onError = (msg?: string) => {
            App.eventBus.trigger('notification', {
                message: msg || $.i18n.__('upl.failure'),
                type: 'error',
            });
        };

        xhr.onloadend = (request) => {
            this.detachChildView('spinner');
            // clears out form field
            this.render();

            if (('' + xhr.status)[0] !== '2') {
                let msg;
                try {
                    msg = JSON.parse(xhr.responseText).errors[0].title;
                } catch (e) {
                    onError();
                }
                onError(msg);
                return;
            }

            this.collection.add(JSON.parse(xhr.responseText).data.attributes);
        };
        xhr.onerror = () => {
            onError();
        };

        xhr.send(formData);
    }

    private initDropUploader() {
        const layer = this.getUI('dropLayer')[0];
        const supported = ('draggable' in layer) || ('ondragstart' in layer && 'ondrop' in layer);

        if (!supported) {
            this.getUI('dropLayer').remove();
            return;
        }

        this.getUI('heading').html($.i18n.__('upl.new.title'));
    }

    private handleDrop(event: JQueryEventObject) {
        this.handleDragLeave(event);
        const orgEvent = event.originalEvent as DragEvent;
        if (!orgEvent.dataTransfer) {
            return;
        }

        const files = orgEvent.dataTransfer.files;
        const formData = new FormData();
        formData.append('upload[0][file]', files[0]);

        this.send(formData);
    }

    private handleDragOver(event: Event) {
        event.preventDefault();
        this.getUI('indicator').removeClass('fadeOut').addClass('fadeIn');
    }

    private handleDragLeave(event: Event) {
        event.preventDefault();
        this.getUI('indicator').removeClass('fadeIn').addClass('fadeOut');
    }
}

export default UploaderAddVw;
