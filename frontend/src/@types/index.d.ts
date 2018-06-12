interface JQueryStatic {
    i18n: any;
}

declare module '*.html' {
    const content: string;
    export default content;
}

interface PNotify {

}

declare module 'embed-js' {
    class EmbedJS {
        constructor(options: Object);
        render(): object;
    }

    export default EmbedJS;
}

declare module 'embed-plugin-noembed' {
    const noembed: any;
    export default noembed;
}

declare module 'embed-plugin-instagram' {
    const instagram: any;
    export default instagram;
}

declare module 'embed-plugin-url' {
    const url: any;
    export default url;
}
