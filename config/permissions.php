<?php declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

use Saito\User\Permission\PermissionConfig;
use Saito\User\Permission\Roles;

/**
 * Create roles and assign which permissions from other roles are allowed too
 *
 * Add translations in nondynamic.po as 'permission.role.<ID-number>'
 */
$config['Saito']['Roles'] = (new Roles)
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
 * allowAll > allowUser > allowRole
 */
$config['Saito']['Permissions'] = (new PermissionConfig)
    /**
     * Allow roles access to resource based on roles
     */
    // Access to the administration backend
    ->allowRole('saito.core.admin.backend', 'admin')
    // Pin or lock a posting
    ->allowRole('saito.core.posting.pinAndLock', 'mod')
    // Delete a posting
    ->allowRole('saito.core.posting.delete', 'mod')
    // "Moderator" mode. Restricted to other user and accessible categories
    ->allowRole('saito.core.posting.edit.restricted', 'mod')
    // Allows unrestricted editing of postings
    ->allowRole('saito.core.posting.edit.unrestricted', 'admin')
    // Show user's IP address if available
    ->allowRole('saito.core.posting.ip.view', 'mod')
    // Merge postings
    ->allowRole('saito.core.posting.merge', 'mod')
    // Show a user's activation status
    ->allowRole('saito.core.user.activate.view', 'admin')
    // Contact a user no matter their contact settings
    ->allowRole('saito.core.user.contact', 'admin')
    // Delete user
    ->allowRole('saito.core.user.delete', 'mod', 'user')
    ->allowRole('saito.core.user.delete', 'admin', ['mod', 'user'])
    ->allowRole('saito.core.user.delete', 'owner')
    // Edit a user's profile page
    ->allowRole('saito.core.user.edit', 'admin')
    // Change a user's email address
    ->allowRole('saito.core.user.email.set', 'admin')
    // Allows locking-out of users
    ->allowRole('saito.core.user.lock.set', 'mod', 'user')
    ->allowRole('saito.core.user.lock.set', 'admin', ['mod', 'user'])
    ->allowRole('saito.core.user.lock.set', 'owner')
    // Show a user's blocking status
    ->allowRole('saito.core.user.lock.view', 'user')
    // Change a user's name
    ->allowRole('saito.core.user.name.set', 'admin')
    // Change a user's password
    ->allowRole('saito.core.user.password.set', 'admin', ['mod', 'user'])
    ->allowRole('saito.core.user.password.set', 'owner')
    // Change a user's role. Allowed ranks: all the current user has but not
    // their own rank.
    ->allowRole('saito.core.user.role.set.restricted', 'admin', ['mod', 'user'])
    // Change a user's role. Allowed ranks: all the current user has including
    // their own rank.
    ->allowRole('saito.core.user.role.set.unrestricted', 'owner')

    /**
     * Allow access if the resource "belongs" to a user
     */
    // Allow user to edit their own postings
    ->allowOwner('saito.core.posting.edit')
    // Allow user to edit their own profile
    ->allowOwner('saito.core.user.edit')
    // Allow user to delete their own bookmarks
    ->allowOwner('saito.plugin.bookmarks.delete')

    /**
     * Allow access without limitations
     */
    // Register new users
    ->allowAll('saito.core.user.register');

return $config;
