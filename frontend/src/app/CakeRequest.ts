/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

interface ICakeRequest {
    action?: string;
    controller?: string;
    csrf?: {
        header: string,
        token: string,
    };
    isMobile?: boolean;
}

class CakeRequest {
    private request!: ICakeRequest;

    /**
     * Setter
     *
     * @param request Cake Request
     */
    public set(request: ICakeRequest) {
        this.request = request;
    }

    /**
     * Get the current CakePHP route action
     */
    public getAction(): string | undefined {
        return this.request ? this.request.action : undefined;
    }

    /**
     * Get the current CakePHP route controller
     */
    public getController(): string | undefined {
        return this.request ? this.request.controller : undefined;
    }

    public getCsrf(): { header: string, token: string } | undefined {
        return this.request ? this.request.csrf : undefined;
    }

    public isMobile(): boolean | undefined {
        return this.request ? this.request.isMobile : undefined;
    }
}

export default CakeRequest;
