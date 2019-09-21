/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

/**
 * Global content timer
 */
class ContentTimer {
    private timeoutId: number | undefined;

    public start(): this {
        this.cancel();
        this.timeoutId = window.setTimeout(() => this.show(), 5000);

        return this;
    }

    public cancel() {
        if (typeof this.timeoutId === 'number') {
            window.clearTimeout(this.timeoutId);
            delete this.timeoutId;
        }
    }

    private show() {
        const content = $('#content').css('visibility', 'visible');
        // console.warn('DOM ready timed out: Show content fallback used.');
        delete this.timeoutId;
    }
}

export default ContentTimer;
