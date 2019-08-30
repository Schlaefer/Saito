/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import { debounce, defaults, template, throttle } from 'underscore';
import AnswerModel from './models/AnswerModel';

interface ILongTimers { early: number; long: number; }

/**
 * Main timer for sending drafts to the server.
 */
class LongTimer {
    protected fct: () => void;

    protected lastRun: number;

    protected running: boolean;

    protected timerId: number;

    protected timers: ILongTimers;

    public constructor(fct: () => void, timers: ILongTimers) {
        this.fct = fct;
        this.timers = timers;
    }

    /**
     * Starts the timer.
     */
    public start() {
        if (this.running) {
            return;
        }
        this.running = true;
        this.timerId = window.setTimeout(() => { this.run(); }, this.timers.long);
    }

    /**
     * Ends the timer early.
     */
    public early() {
        if ((this.lastRun !== null) && ((Date.now() - this.lastRun) < this.timers.early)) {
            return;
        }

        this.run();
        this.stop();
    }

    /**
     * Stops the timer.
     */
    public stop() {
        if (!this.running) {
            return;
        }

        this.running = false;
        window.clearTimeout(this.timerId);
    }

    /**
     * Runs the the callback if the timer rings.
     */
    private run() {
        this.fct();
        this.lastRun = Date.now();
    }
}

/**
 * Model for drafts
 */
class DraftModel extends AnswerModel {
    /**
     * Ma initializer
     *
     * @param options options
     */
    public initialize(options) {
        this.saitoUrl = 'drafts/';
    }
}

type DraftTimers = { debounce: number } & ILongTimers;

/**
 * View for drafts
 */
export default class DraftView extends View<Model> {
    /** Holds the main timer */
    public longTimer: LongTimer;
    /** Short timer to debounce the main timer */
    public shortTimer: () => void;
    /**
     * Enables or disables the the sending of drafts.
     *
     * Default: true.
     */
    private enabled: boolean;

    public constructor(options: object = {}) {
        const deflt: { timers: DraftTimers } & any = {
            attributes: { 'data-shpid': 9 },
            className: 'draft-status shp',
            model: new DraftModel(),
            modelEvents: {
                change: 'handleModelChange',
            },
            tagName: 'span',
            template: template(`
                <i class="fa fa-fw fa-floppy-o js-saved"
                   title="<%- $.i18n.__('answer.draft.saved.t') %>"
                   aria-hidden="true">
                </i>
                <i class="fa fa-fw fa-pencil js-unsaved"
                   title="<%- $.i18n.__('answer.draft.unsaved.t') %>"
                   style="display: none;"
                   aria-hidden="true">
                </i>
            `),
            timers: {
                debounce: 4000,
                early: 5000,
                long: 30000,
            },
            ui: {
                saved: '.js-saved',
                unsaved: '.js-unsaved',
            },
        };
        defaults(options, deflt);

        super(options);

        this.enabled = true;
    }

    public onRender() {
        this.showSaved();

        const timers: DraftTimers = this.getOption('timers');
        this.longTimer = new LongTimer(() => { this.send(); }, timers);
        this.shortTimer = debounce(() => { this.longTimer.early(); }, timers.debounce);
    }

    /**
     * Enables the sending of drafts.
     */
    public enable(): void {
        this.enabled = true;
    }

    /**
     * Disables the sending of drafts.
     */
    public disable(): void {
        this.enabled = false;
    }

    private handleModelChange() {
        this.showUnsaved();
        this.longTimer.start();
        this.shortTimer();
    }

    private send() {
        if (!this.enabled) {
            return;
        }
        if (!this.model.get('id')) {
            // Do only when creating a new draft, not on an existing one.
            if (!this.model.get('subject') && !this.model.get('text')) {
                // Don't send empty data.
                return;
            }
        }
        this.model.save(null, {
            success: (model, response, options) => { this.showSaved(); },
        });
    }

    private showUnsaved() {
        this.getUI('saved').hide();
        this.getUI('unsaved').show();
    }

    private showSaved() {
        this.getUI('unsaved').hide();
        this.getUI('saved').show();
    }
}

export { DraftModel, DraftView };
