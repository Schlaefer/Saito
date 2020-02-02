<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller;

use App\Controller\Component\ThemesComponent;
use App\Controller\Component\TitleComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Saito\User\CurrentUser\CurrentUserFactory;

/**
 * Custom Error Controller to render errors in default theme for production.
 *
 * @property ThemesComponent $Themes
 * @property TitleComponent $Title
 */
class ErrorController extends Controller
{
    /**
     * {@inheritDoc}
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Themes', Configure::read('Saito.themes'));
        // Populate forum title for display in layout
        $this->loadComponent('Title');
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender(\Cake\Event\EventInterface $event)
    {
        parent::beforeRender($event);

        if (!Configure::read('debug')) {
            // Pickup custom errorX00.ctp layout files.
            $this->viewBuilder()->setTemplatePath('Error');

            // Set stripped down CurrentUser so calls to it in (default) layout
            // .ctp(s) don't fail.
            $CurrentUser = CurrentUserFactory::createDummy();
            $this->set('CurrentUser', $CurrentUser);

            $this->Themes->setDefault();
        }
    }
}
