interface JQueryStatic {
    i18n: any;
}

interface JQuery {
    insertAtCaret(text: string): JQuery;
    scrollIntoView(method: string): void;
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
