/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

interface IBbcodeTag {
    tag: string;
    attributes?: string;
    content?: string;
}

interface IBbcodeTagConfig {
    prefix: string;
    suffix: string;
}

/**
 * BBCode block tag [tag attributes]content[/tag].
 */
class BbcodeTag implements IStringable {
    private params: IBbcodeTag;

    private config: IBbcodeTagConfig;

    private defaultConfig: IBbcodeTagConfig =  {
        prefix: '',
        suffix: ' ',
    };

    /**
     * Constructor
     *
     * @param params tag parameter
     */
    public constructor(params: IBbcodeTag, config: Partial<IBbcodeTagConfig> = {}) {
        this.params = params;
        this.config = Object.assign({}, this.defaultConfig, config);
    }

    /**
     * Get tag of [tag attributes]content[/tag]
     */
    public getTag(): string {
        return this.params.tag;
    }

    /**
     * Get attributes of [tag attributes]content[/tag]
     */
    public getAttributes(): string|null {
        return this.params.attributes || null;
    }

    /**
     * Get content of [tag attributes]content[/tag]
     */
    public getContent(): string|null {
        return this.params.content || null;
    }

    /**
     * Get the whole tag as string
     */
    public toString(): string {
        let out = '[' + this.params.tag;
        if (this.getAttributes()) {
            out +=  ' ' + this.getAttributes();
        }
        out += ']';
        if (this.getContent()) {
            out += this.getContent();
        }
        out += '[/' + this.getTag() + ']';

        // Insert whitespace esp. after to not trigger iOS 12/13 autocorrect.
        // @see https://github.com/Schlaefer/Saito/issues/360
        out = this.config.prefix + out + this.config.suffix;

        return out;
    }
}

export default BbcodeTag;
