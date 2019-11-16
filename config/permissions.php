<?php declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

use Saito\User\Permission\ResourceAC;
use Saito\User\Permission\Resource;
use Saito\User\Permission\Resources;
use Saito\User\Permission\Roles;

/**
 * Create roles and assign which permissions from other roles are allowed too
 *
 * Add translations in nondynamic.po as 'permission.role.<ID-number>'
 */
$config['Saito']['Permission']['Roles'] = (new Roles)
    // Non logged-in visitors
    ->add('anon', 0)
    // Registered and logged-in users
    ->add('user', 1, ['anon'])
    // Moderators
    ->add('mod', 2, ['user', 'anon'])
    // Administrators
    ->add('admin', 3, ['mod', 'user', 'anon'])
    // Owners
    ->add('owner', 4, ['admin', 'mod', 'user', 'anon']);

/**
 * Permissions
 *
 * everbody > owner > role
 */
$config['Saito']['Permission']['Resources'] = (new Resources())
    /**
     * Allow roles access to resource based on roles
     */
    // Access to the administration backend
    ->add((new Resource('saito.core.admin.backend'))
        ->allow((new ResourceAC())->asRole('admin')))
    // Pin or lock a posting
    ->add((new Resource('saito.core.posting.pinAndLock'))
        ->allow((new ResourceAC())->asRole('mod')))
    // Delete a posting
    ->add((new Resource('saito.core.posting.delete'))
        ->allow((new ResourceAC())->asRole('mod')))
    // Allow user to edit their own postings
    ->add((new Resource('saito.core.posting.edit'))
        ->allow((new ResourceAC())->onOwn()))
    // "Moderator" mode. Restricted to other user and accessible categories
    ->add((new Resource('saito.core.posting.edit.restricted'))
        ->allow((new ResourceAC())->asRole('mod')))
    // Allows unrestricted editing of postings
    ->add((new Resource('saito.core.posting.edit.unrestricted'))
        ->allow((new ResourceAC())->asRole('admin')))
    // Show user's IP address if available
    ->add((new Resource('saito.core.posting.ip.view'))
        ->allow((new ResourceAC())->asRole('mod')))
    // Merge postings
    ->add((new Resource('saito.core.posting.merge'))
        ->allow((new ResourceAC())->asRole('mod')))
    // Show a user's activation status
    ->add((new Resource('saito.core.user.activate.view'))
        ->allow((new ResourceAC())->asRole('admin')))
    // Contact a user no matter their contact settings
    ->add((new Resource('saito.core.user.contact'))
        ->allow((new ResourceAC())->asRole('admin')))
    // Delete user
    ->add((new Resource('saito.core.user.delete'))
        ->allow((new ResourceAC())->asRole('mod')->onRole('user'))
        ->allow((new ResourceAC())->asRole('admin')->onRoles('mod', 'user'))
        ->allow((new ResourceAC())->asRole('owner')))
    // Edit a user's profile page
    ->add((new Resource('saito.core.user.edit'))
        ->allow((new ResourceAC())->onOwn())
        ->allow((new ResourceAC())->asRole('admin')))
    // Change a user's email address
    ->add((new Resource('saito.core.user.email.set'))
        ->allow((new ResourceAC())->asRole('admin')))
    // Allows locking-out of users
    ->add((new Resource('saito.core.user.lock.set'))
        ->allow((new ResourceAC())->asRole('mod')->onRole('user'))
        ->allow((new ResourceAC())->asRole('admin')->onRoles('mod', 'user'))
        ->allow((new ResourceAC())->asRole('owner')))
    // Show a user's blocking status
    ->add((new Resource('saito.core.user.lock.view'))
        ->allow((new ResourceAC())->asRole('user')))
    // Change a user's name
    ->add((new Resource('saito.core.user.name.set'))
        ->allow((new ResourceAC())->asRole('admin')))
    // Change a user's password
    ->add((new Resource('saito.core.user.password.set'))
        ->allow((new ResourceAC())->asRole('admin')->onRoles('mod', 'user'))
        ->allow((new ResourceAC())->asRole('owner')))
    // Change a user's role. Allowed ranks: all the current user has but not
    // their own rank.
    ->add((new Resource('saito.core.user.role.set.restricted'))
        ->allow((new ResourceAC())->asRole('admin')->onRoles('mod', 'user')))
    // Change a user's role. Allowed ranks: all the current user has including
    // their own rank.
    ->add((new Resource('saito.core.user.role.set.unrestricted'))
        ->allow((new ResourceAC())->asRole('owner')))
    // Deleting bookmarks
    ->add((new Resource('saito.plugin.bookmarks.delete'))
        ->allow((new ResourceAC())->onOwn()))
    // Use the register form
    ->add((new Resource('saito.core.user.register'))
        ->allow((new ResourceAC())->asEverybody()))
    ;

return $config;
