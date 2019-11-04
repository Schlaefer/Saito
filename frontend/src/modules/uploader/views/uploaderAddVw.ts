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
import Template from '../templates/uploaderAddTpl.html';
import DragAreaVw from './uploaderAdd/uploaderAddDragAreaVw';
import UploaderAddMdl from './uploaderAdd/uploaderAddMdl';
import ProgressBarVw from './uploaderAdd/uploaderAddProgressVw';
import StatsVw from './uploaderAdd/uploaderAddStatsVw';

class UploaderAddVw extends View<Model> {
    private xhr: XMLHttpRequest|undefined;

    /**
     * Constructor
     *
     * @param options Ma view options
     */
    public constructor(options: any = {}) {
        _.defaults(options, {
            className: 'imageUploader-add',
            events: {
                'change @ui.inputFile': 'onUploadBtn',
                'click @ui.abortBtn': 'onAbortBtn',
            },
            model: new UploaderAddMdl(null, {collection: options.collection}),
            modelEvents: {
                'change:fileToUpload': 'send',
            },
            regions: {
                dragAreaRg: '.js-dragAreaRg',
                progressBarRg: '.js-progressBarRg',
                statsRg: '.js-statsRg',
            },
            template: Template,
            ui: {
                abortBtn: '.js-abortBtn',
                bar: '.progress-bar',
                inputFile: '#Upload0File',
                sendBtn: '.js-sendBtn',
            },
        });
        super(...arguments);
    }

    /**
     * Ma onRender callback
     */
    public onRender() {
        this.showChildView('progressBarRg', new ProgressBarVw({model: this.model}));
        this.showChildView('statsRg', new StatsVw({model: this.model}));
        this.showChildView('dragAreaRg', new DragAreaVw({model: this.model}));
    }

    /**
     * Ma onBeforeDestroy callback
     */
    public onBeforeDestroy()
    {
        if (this.model.get('uploadInProgress')) {
            this.onAbortBtn();
        }
    }

    /**
     * Called after the user picked a file to upload.
     *
     * @param event
     */
    protected onUploadBtn(event: Event) {
        event.preventDefault();
        const input: any = this.getUI('inputFile')[0];
        this.model.set('fileToUpload', input.files[0], {validate: true});
        const error = this.model.validationError;
        if (error) {
            App.eventBus.trigger('notification', {message: error, type: 'error'});
        }
    }

    /**
     * Called when the file in this view's model is updated to send the file
     *
     * @param model This view's model
     * @param file The file to upload
     */
    protected send(model: Model, file: File) {
        if (file === null) {
            return;
        }

        this.xhr = new XMLHttpRequest();
        const xhr = this.xhr;
        xhr.open('POST', App.settings.get('apiroot') + 'uploads');
        xhr.setRequestHeader('Accept', 'application/json, text/javascript');
        xhr.setRequestHeader('Authorization', 'bearer ' + App.settings.get('jwt'));

        xhr.onloadstart = () => this.onUploadStart();
        xhr.onabort = () => this.onUploadAbort();
        xhr.upload.onprogress = (event) => this.onUploadProgress(event);
        xhr.onerror = () => this.onUploadError();

        xhr.onloadend = () => {
            this.xhr = undefined;
            this.model.set('progress', 0);
            this.model.set('uploadInProgress', false);
            this.model.set('fileToUpload', null);
            // clears out form field
            this.render();

            if (this.model.get('uploadWasAbortedByUser') === true) {
                /// User aborted upload.
                this.model.set('uploadWasAbortedByUser', false);
                return;
            }

            if (('' + xhr.status)[0] === '2') {
                /// Upload was successful.
                this.collection.add(JSON.parse(xhr.responseText).data.attributes);
                return;
            }

            /// Upload failed
            let msg;
            try {
                const errors = JSON.parse(xhr.responseText).errors;
                if (errors) {
                    msg = errors[0].title;
                }
            } finally {
                this.onUploadError(msg);
            }
        };

        const formData = new FormData();
        formData.append('upload[0][file]', file);

        xhr.send(formData);
    }

    /**
     * Called on upload error to handle error presentation.
     *
     * @param msg Error message
     */
    protected onUploadError(msg?: string) {
        App.eventBus.trigger('notification', {
            message: msg || $.i18n.__('upl.failure'),
            type: 'error',
        });
    }

    /**
     * Called on upload progress to update upload stats.
     *
     * @param event progress event
     */
    protected onUploadProgress(event: ProgressEvent) {
        let complete;
        if (event.lengthComputable) {
            /// Progress-bar length reflects actual upload
            this.model.set('loaded', event.loaded);

            complete = Math.floor(event.loaded / event.total * 100);
        } else {
            /// Progress/bar length is faked
            complete = this.model.get('progress');
            if (complete < 95) {
                complete += 2;
            }
        }
        this.model.set('progress', complete);
    }

    /**
     * Called when the upload starts
     */
    protected onUploadStart() {
        this.model.set('start', (new Date()).getTime() / 1000);
        this.model.set('uploadInProgress', true);

        this.getUI('inputFile').attr('disabled', 'disabled');
        this.getUI('sendBtn').hide();
        this.getUI('abortBtn').show();
    }

    /**
     * Called when upload is aborted
     */
    protected onUploadAbort() {
        this.getUI('inputFile').removeAttr('disabled');
        this.getUI('abortBtn').hide();
        this.getUI('sendBtn').show();
    }

    /**
     * Called when the upload cancel button is pressed. Aborts the upload.
     */
    protected onAbortBtn() {
        if (this.xhr === undefined) {
            return;
        }
        this.model.set('uploadWasAbortedByUser', true);
        this.xhr.abort();
    }
}

export default UploaderAddVw;
