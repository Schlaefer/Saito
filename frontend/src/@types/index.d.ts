interface JQueryStatic {
    i18n: any;
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
