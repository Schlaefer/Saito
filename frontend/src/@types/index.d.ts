interface JQueryStatic {
    i18n: any;
}

interface JQuery {
    scrollIntoView(method: string): any;
    textrange(method: string|object, arg1?: number|string, arg2?: number|string, arg3?: number|string): any;
    tinyTimer(options: object): void;
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
 * Browser-vendor specific properties on the global document object
 */
interface Document {
    msHidden: any;
    webkitHidden: any;
}
