declare module TinyTimer {
    interface TinyTimerCallbackArgs {
        /** Seconds */
        s: number,
        /** Minutes */
        m: number,
        /** Hours */
        h: number,
        /** Days */
        d: number,
        /** Total seconds */
        S: number,
        /** Total minutes */
        M: number,
        /** Total hours */
        H: number,
        /** Total days */
        D: number,
        /** Text representation */
        text: string,
    }

    interface TinyTimerOptions {
        format: string,
        from?: Date | string,
        onEnd?: (args: TinyTimerCallbackArgs) => {},
        onTick?: (args: TinyTimerCallbackArgs) => {},
        to: Date | string,
    }
}

declare namespace JQuery {
    interface jqXHR {
        // Official but missing in official jQuery definitions
        crossDomain: boolean;
    }
}

interface JQueryStatic {
    i18n: any;
    isReady: boolean;
}

interface JQuery {
    scrollIntoView(method: string): any;
    textrange(method: string | object, arg1?: number | string, arg2?: number | string, arg3?: number | string): { position: number, start: number, end: number, length: number, text: string }
    tinyTimer(options: TinyTimer.TinyTimerOptions): void;
}

declare namespace Marionette {
    interface Application {
        onStart(app: any, options: any): void;
    }
}

declare module '*.html' {
    const content: string;
    export default content;
}

interface PNotify {

}

declare module 'humanize' {
    class humanize {
        public date(size: string): string
        public filesize(size: string): string
    }

    const h: humanize;

    export default h;
}

declare module 'backbone.localstorage' {
    class LocalStorage {
        public constructor(key: string);
    }

    export { LocalStorage };
}

// moment ts file is fucked up: https://github.com/moment/moment/issues/3763
declare module 'moment' {
    interface MomentStatic {
        (): any
        (date: number): any
        (date: string): any
        (date: string, time: string): any
        (date: Date): any
        (date: string, formats: string[]): any
        (date: number[]): any

        unix(timestamp: number): any
    }

    var moment: MomentStatic;

    export default moment;
}

/**
 * Helper declaration for .js files and TS strict
 */
declare module 'views/app';

/**
 * Browser-vendor specific properties on the global document object
 */
interface Document {
    msHidden: any;
    webkitHidden: any;
}

interface Window {
    /**
     * Redirects the browser to a new URL.
     *
     * @param url URL to redirect to.
     */
    redirect: (url: string) => void;
}
