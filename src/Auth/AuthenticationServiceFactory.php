<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Auth;

use App\Auth\LegacyPasswordHasherSaltless;
use App\Auth\Mlf2PasswordHasher;
use Authentication\AuthenticationService;
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * Builds AuthenticationService consumed by Authentication middleware
 */
class AuthenticationServiceFactory
{
    /**
     * Build authentication service for JWT based API
     *
     * @return AuthenticationService
     */
    public static function buildJwt(): AuthenticationService
    {
        $service = new AuthenticationService();

        $service->loadIdentifier('Authentication.JwtSubject');
        $service->loadAuthenticator('Authentication.Jwt', [
            'returnPayload' => false,
            'secretKey' => Configure::read('Security.cookieSalt'),
        ]);

        return $service;
    }

    /**
     * Build authentication service with Session, Cookie and Form
     *
     * @return AuthenticationService
     */
    public static function buildApp(): AuthenticationService
    {
        $service = new AuthenticationService();

        $service->setConfig('queryParam', 'redirect');
        $service->setConfig('unauthenticatedRedirect', '/login');

        $service->loadIdentifier('Authentication.Password', [
            'passwordHasher' => [
                'className' => 'Authentication.Fallback',
                'hashers' => [
                    // Saito passwords (Cake default)
                    ['className' => 'Authentication.Default'],
                    // Mylittleforum 2 legacy passwords
                    ['className' => Mlf2PasswordHasher::class],
                    // Mylittleforum 1 legacy passwords
                    ['className' => LegacyPasswordHasherSaltless::class, 'hashType' => 'md5'],
                ]
            ]
        ]);

        // Authenticators are checked in order of registration.
        // Leave Session first.
        $service->loadAuthenticator(
            'Authentication.Session',
            [
                // Always check against DB. User-state (type, locked) might have
                // changed and must be reflected immediately.
                'identify' => true,
            ]
        );
        $service->loadAuthenticator(
            'Authentication.Cookie',
            [
                'cookie' => [
                    'expire' => new \DateTimeImmutable('+10 days'),
                    'httpOnly' => true,
                    'name' => Configure::read('Security.cookieAuthName'),
                    'path' => Router::url('/', false),
                ]
            ]
        );
        $service->loadAuthenticator('Authentication.Form', ['loginUrl' => '/login']);

        return $service;
    }
}
