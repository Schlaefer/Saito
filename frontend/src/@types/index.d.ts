interface JQueryStatic {
    i18n: any;
}

interface JQuery {
    insertAtCaret(text: string): JQuery;
    scrollIntoView(method: string): any;
    tinyTimer(options: object): void;
}

declare module '*.html' {
    const content: string;
    export default content;
}

interface PNotify {

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
