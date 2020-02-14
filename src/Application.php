<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App;

use App\Auth\AuthenticationServiceFactory;
use App\Middleware\SaitoBootstrapMiddleware;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Authentication\UrlChecker\DefaultUrlChecker;
use Cake\Core\Configure;
use Cake\Core\Exception\MissingPluginException;
use Cake\Core\Plugin;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Event\EventManagerInterface;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\Middleware\EncryptedCookieMiddleware;
use Cake\Http\Middleware\SecurityHeadersMiddleware;
use Cake\Http\ServerRequest;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Saito\App\Registry;
use Saito\RememberTrait;
use Stopwatch\Lib\Stopwatch;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    use RememberTrait;

    /**
     * {@inheritDoc}
     */
    public function __construct($configDir, ?EventManagerInterface $eventManager = null)
    {
        Stopwatch::init();
        Stopwatch::enable();
        Stopwatch::start('Application::__construct');
        parent::__construct($configDir, $eventManager);
        Stopwatch::stop('Application::__construct');
    }

    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        Stopwatch::start('Application::bootstrap');

        parent::bootstrap();

        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        }
        /*
         * Only try to load DebugKit in development mode
         * Debug Kit should not be installed on a production system
         */
        if (Configure::read('debug')) {
            // $this->addPlugin('DebugKit');
        }
        // Load more plugins here

        Registry::initialize();

        $this->addPlugin('Authentication');
        $this->addPlugin(\Admin\Plugin::class, ['routes' => true]);
        $this->addPlugin(\Api\Plugin::class, ['bootstrap' => true, 'routes' => true]);
        $this->addPlugin(\Bookmarks\Plugin::class, ['routes' => true]);
        $this->addPlugin(\BbcodeParser\Plugin::class);
        $this->addPlugin(\Feeds\Plugin::class, ['routes' => true]);
        $this->addPlugin(\Installer\Plugin::class);
        $this->addPlugin(\SaitoHelp\Plugin::class, ['routes' => true]);
        $this->addPlugin(\SaitoSearch\Plugin::class, ['routes' => true]);
        $this->addPlugin(\Sitemap\Plugin::class, ['bootstrap' => true, 'routes' => true]);
        $this->addPlugin(\ImageUploader\Plugin::class, ['routes' => true]);

        $this->addPlugin(\Cron\Plugin::class);
        $this->addPlugin(\Commonmark\Plugin::class);
        $this->addPlugin(\Detectors\Plugin::class);
        $this->addPlugin(\MailObfuscator\Plugin::class);
        $this->addPlugin(\SpectrumColorpicker\Plugin::class);
        $this->addPlugin(\Stopwatch\Plugin::class);

        // TODO
        // $this->addPlugin('Proffer');

        $this->loadDefaultThemePlugin();

        Stopwatch::stop('Application::bootstrap');
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware($middlewareQueue): \Cake\Http\MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error')))

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance. For that when
            // creating the middleware instance specify the cache config name by
            // using it's second constructor argument:
            // `new RoutingMiddleware($this, '_cake_routes_')`
            ->add(new RoutingMiddleware($this, '_cake_routes_'))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/4/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())

            ->insertAfter(RoutingMiddleware::class, new SaitoBootstrapMiddleware())

            ->add(new EncryptedCookieMiddleware(
                // Names of cookies to protect
                [Configure::read('Security.cookieAuthName')],
                Configure::read('Security.cookieSalt')
            ))

            // CakePHP authentication provider
            ->insertAfter(
                EncryptedCookieMiddleware::class,
                new AuthenticationMiddleware($this)
            );

        /// CSRF
        $csrf = new CsrfProtectionMiddleware([
            'expiry' => time() + 10800,
            'cookieName' => PHP_SAPI !== 'cli'
                // Nice looking Saito-CSRF cookie name
                ? Configure::read('Session.cookie') . '-CSRF'
                // The security mock in testing doesn't allow seeting
                // a different cookie-name.
                : 'csrfToken',
        ]);
        $csrf->whitelistCallback(function (ServerRequest $request) {
            return $this->isApiRoute($request) || $request->getParam('plugin') === 'Installer';
        });
        $middlewareQueue->insertAfter(EncryptedCookieMiddleware::class, $csrf);

        /// Security Header X-Frame
        $security = (new SecurityHeadersMiddleware())
            ->setXFrameOptions(strtolower(Configure::read('Saito.X-Frame-Options')));
        $middlewareQueue->add($security);

        return $middlewareQueue;
    }

    /**
     * Get authentication service.
     *
     * Part of AuthenticationServiceProviderInterface.
     *
     * {@inheritDoc}
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        if ($this->isApiRoute($request)) {
            return AuthenticationServiceFactory::buildJwt();
        }

        return AuthenticationServiceFactory::buildApp();
    }

    /**
     * Load the plugin for Saito's default theme
     *
     * @return void
     */
    private function loadDefaultThemePlugin()
    {
        $defaultTheme = Configure::read('Saito.themes.default');
        if (empty($defaultTheme)) {
            throw new \RuntimeException(
                'Could not resolve default theme for plugin loading.',
                1556562215
            );
        }
        if (Plugin::isLoaded($defaultTheme) !== true) {
            $this->addPlugin($defaultTheme);
        }
    }

    /**
     * Bootrapping for CLI application.
     *
     * That is when running commands.
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        try {
            $this->addPlugin('Bake');
        } catch (MissingPluginException $e) {
            // Do not halt if the plugin is missing
        }

        $this->addPlugin('Migrations');

        // Load more plugins here
    }

    /**
     * Check if current request is on an API route
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Current request
     * @return bool
     */
    protected function isApiRoute(ServerRequestInterface $request): bool
    {
        return $this->remember('isApiRoute', function () use ($request) {
            return (new DefaultUrlChecker())
                ->check($request, ['#api/v2#'], ['useRegex' => true]);
        });
    }
}
