<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Test;

use Saito\App\Registry;
use Saito\Test\SaitoTestCase;
use Saito\User\CurrentUser\CurrentUserFactory;
use Saito\User\Permission\Identifier\Owner;
use Saito\User\Permission\Identifier\Role;
use Saito\User\Permission\PermissionConfig;
use Saito\User\Permission\Permissions;
use Saito\User\Permission\Roles;

class PermissionTest extends SaitoTestCase
{
    public $fixtures = [
        'app.Category',
    ];

    public function testBootstrapCategories()
    {
        $p = Registry::get('Permissions');

        $cu = CurrentUserFactory::createDummy(['user_type' => 'mod']);
        $this->assertTrue($p->check($cu, 'saito.core.category.4.thread'));
        $this->assertTrue($p->check($cu, 'saito.core.category.4.answer'));
        $this->assertTrue($p->check($cu, 'saito.core.category.4.read'));

        $cu = CurrentUserFactory::createDummy(['user_type' => 'user']);
        $this->assertFalse($p->check($cu, 'saito.core.category.4.thread'));
        $this->assertTrue($p->check($cu, 'saito.core.category.4.answer'));
        $this->assertTrue($p->check($cu, 'saito.core.category.4.read'));
    }

    public function testCheckForce()
    {
        $Cats = $this->getTableLocator()->get('Categories');
        $config = (new PermissionConfig())
            ->allowAll('foo')
            // Only the last set allowAll should apply.
            ->allowAll('bar')
            ->allowAll('bar', false)
            ->allowRole('baz', 'anon')
            ->allowRole('zip', 'anon')
            ->allowAll('zip', false);

        $p = new Permissions((new Roles())->add('anon', 0), $config, $Cats);
        $cu = CurrentUserFactory::createDummy(['user_type' => 'anon']);

        $this->assertTrue($p->check($cu, 'foo'));
        $this->assertFalse($p->check($cu, 'bar'));
        $this->assertTrue($p->check($cu, 'baz'));
        $this->assertFalse($p->check($cu, 'zip'));
    }

    public function testCheckRole()
    {
        $Cats = $this->getTableLocator()->get('Categories');
        $roles = (new Roles())
            ->add('anon', 0)
            ->add('user', 1, ['anon']);

        $config = (new PermissionConfig())
            ->allowRole('foo', 'user')
            ->allowRole('bar', ['user', 'anon'])
            ->allowRole('baz', 'user', 'anon')
            ->allowRole('zip', 'user', ['user', 'anon']);

        $p = new Permissions($roles, $config, $Cats);
        $anon = CurrentUserFactory::createDummy(['user_type' => 'anon']);
        $user = CurrentUserFactory::createDummy(['user_type' => 'user']);

        $this->assertFalse($p->check($anon, 'foo'));
        $this->assertTrue($p->check($user, 'foo'));

        $this->assertTrue($p->check($anon, 'bar'));
        $this->assertTrue($p->check($user, 'bar'));

        $this->assertFalse($p->check($anon, 'baz', new Role('anon')));
        $this->assertFalse($p->check($anon, 'baz', new Role('user')));
        $this->assertTrue($p->check($user, 'baz', new Role('anon')));
        $this->assertFalse($p->check($user, 'baz', new Role('user')));

        $this->assertFalse($p->check($anon, 'zip', new Role('anon')));
        $this->assertFalse($p->check($anon, 'zip', new Role('user')));
        $this->assertTrue($p->check($user, 'zip', new Role('anon')));
        $this->assertTrue($p->check($user, 'zip', new Role('user')));
    }

    public function testOwner()
    {
        $Cats = $this->getTableLocator()->get('Categories');
        $roles = (new Roles())
            ->add('user', 0)
            ->add('admin', 1, ['user']);

        $config = (new PermissionConfig())
            ->allowRole('foo', 'admin')
            ->allowRole('bar', 'admin')
            ->allowOwner('bar')
            ->allowOwner('baz');

        $p = new Permissions($roles, $config, $Cats);
        $userAllowed = CurrentUserFactory::createDummy(['id' => 1, 'user_type' => 'user']);
        $userNotAllowed = CurrentUserFactory::createDummy(['id' => 2, 'user_type' => 'user']);
        $admin = CurrentUserFactory::createDummy(['id' => 3, 'user_type' => 'admin']);

        $this->assertFalse($p->check($userAllowed, 'foo'));
        $this->assertTrue($p->check($admin, 'foo'));

        $this->assertTrue($p->check($userAllowed, 'bar', new Owner(1)));
        $this->assertFalse($p->check($userNotAllowed, 'bar', new Owner(1)));
        $this->assertTrue($p->check($admin, 'bar', new Role('user')));

        $this->assertTrue($p->check($userAllowed, 'baz', new Owner(1)));
        $this->assertFalse($p->check($userNotAllowed, 'baz', new Owner(1)));
        $this->assertFalse($p->check($admin, 'baz'));
        $this->assertTrue($p->check($admin, 'baz', new Owner(3)));
    }

    public function testGetRoles()
    {
        $p = Registry::get('Permissions');

        $this->assertInstanceOf(Roles::class, $p->getRoles());
    }
}
