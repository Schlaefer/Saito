# Change-Log

- ＋ New
- ✓ Fixed
- Δ Changed
- − Removed

## [Next]

- [Full commit-log](https://github.com/Schlaefer/Saito/compare/5.5.0...<next>)
- [Download release-zip](https://github.com/Schlaefer/Saito/releases/download/<next>/saito-release-master-<next>.zip)

### Changes

- ＋ Adds permissions `saito.core.posting.solves.set` for marking a posting as solution/helpful.
- ＋ Improves compatibility with PHP 7.3
- ＋ Improves browser detection for changes in the Bota theme CSS
- ＋ Improves logging of unauthorized access
- ✓ Deleting a bookmark creates an empty area above the bookmarks
- Δ Adds "Saito" prefix to CSRF-cookie name
- − Removes visiblity description for category in category-title hover
- Internal code changes:
  - Δ Refactors creation, update and validation of postings
  - Δ Updates PHP and Javascript libraries
  - Δ Entries::Table throws RecordNotFoundException instead of returning null
  - Δ Update Apcu version in docker container to 5.1.18
  - − Remove SaitoValidationProvider::validateAssoc with CakePHP build-in facility

### Update Notes

## [5.5.0] - 2019-11-16

- [Full commit-log](https://github.com/Schlaefer/Saito/compare/5.4.1...5.5.0)
- [Download release-zip](https://github.com/Schlaefer/Saito/releases/download/5.5.0/saito-release-master-5.5.0.zip)


### Changes

- ＋ Adds `CHANGELOG.md` to keep track of changes
- ＋ Rewritten and expanded permission system:
  - ＋ New, more fine grained permissions
  - ＋ Permissions are configurable
  - ＋ New role "Owner"
- Uploader:
  - ＋ Shows progress-bar when uploading a file
  - ＋ Shows speed, time remaining and file size when uploading a file
  - ＋ Adds button for canceling the current file-upload
  - ＋ Cancel a running upload if the upload-dialog is closed
  - ＋ Checks that file with same name isn't uploaded before upload starts
  - ＋ Improved responsive layout
- ✓ Fixes user's can't log-out if forum is installed in a subdirectory
- ✓ Fixes login redirect issues if forum is installed in a subdirecotry
- Δ Improves performance of background task runner
- Internal code changes:
  - Δ Increases phpstan static code analysis from level 3 to 4
  - Δ Changes passing of current-user throughout the app
  - Δ Updates aura/di from 2.x to 4.x

### Update Notes

#### Extended Permission System

Saito 5.0.0 introduced a new permission system which was rewritten and considerably extended in this release.

##### Configuration

The configuration is exposed at `config/permissions.php` now.

Want to allow moderators to contact a user no matter the user's contact-settings? You can do that. Want to disable new registrations? You can do that. Want to allow users to change their email-address? You can do that. And a lot more.

Permissions are intended to offer flexibility by tweaking the exiting forum behavior to your needs. While possible it is not recommended to start a brand new permission-configuration from scratch.

If you make changes in `config/permissions.php` don't forget to carry them over if you update to new releases in the future.

##### The Owner Account

This update introduces a new user-role *Owner*. The following changes apply to the default configuration:

- On new installations the first account created is an Owner instead of an Administrator
- The Owner lives "above" the Administrator inheriting all their rights
- The "lower" roles are not allowed to change the role, block or delete an Owner
- Only an Owner can promote (or demote) a user to Administrator or Owner

The update is not going to change accounts on existing installations and, because this is the whole point, it isn't possible to promote an account to Owner from an Administrator account. To promote an user on an existing installation execute manually in the database:

```SQL
UPDATE users SET user_type='owner' WHERE username='TheUserName';
```

##### "Lock User" Setting

The setting for enabling user-locking is removed from the admin-backend and controlled by permissions now. The default behavior is unchanged: moderators may lock, locking status is visible to every user.

## [5.4.1] - 2019-10-20

### Noteworthy Changes

- ✓ Changing a user name isn't reflected in search results or "edited by" information
- ✓ Improves reliability of executing background maintenance tasks
- ✓ Fixes internal error caused by read-postings garbage collection for registered users
- Δ Improved performance of read-postings garbage collection for registered users

### Update Notes

Don't miss to add:

- [the new "long" cache configuration](https://github.com/Schlaefer/Saito/blob/7d085ea43598cd3220438d7ca6a5169cae2eaf6c/config/app.php#L170) in `config/app.php`
- [the new "logInfo" configuration](https://github.com/Schlaefer/Saito/blob/7d085ea43598cd3220438d7ca6a5169cae2eaf6c/config/saito_config.php#L95) in `config/saito_config.php`

[Full change-log](https://github.com/Schlaefer/Saito/compare/5.4.0...5.4.1)

## [5.4.0] - 2019-10-12

### Noteworthy Changes

- ＋ Inserts an additional whitespace after closing BBCode tag #360
- ＋ Improves mime-type detection in Uploader
  - ＋ Workaround for [issue with .mp3 files on Chromium-derivates](https://bugs.chromium.org/p/chromium/issues/detail?id=227004)
  - ＋ Workaround for .mp4 videos identifying as `application/octet-stream`
- ✓ Fixes issues where errors-messages were displayed without theme
- ✓ Fixes issues where an API-error didn't result in a proper error-response
- Code improvement:
  - ＋ Increases TypeScript check to "strict" #355
  - Δ Migrates more Javascript code to TypeScript fixing some minor bugs on the way.
  - Δ Migrates user-authentication from [AuthComponent](https://book.cakephp.org/3.0/en/controllers/components/authentication.html) (deprecated in CakePHP 4) to [newer and future-proof Authenticaton-plugin](https://github.com/cakephp/authentication) #361

### Update Notes

[Full change-log](https://github.com/Schlaefer/Saito/compare/5.3.3...5.4.0)

## [5.3.3] - 2019-09-21

### Noteworthy Changes

- ✓ Fixes issues that prevent editing a posting as moderator

### Update Notes

[Full change-log](https://github.com/Schlaefer/Saito/compare/5.3.2...5.3.3)

## [5.3.2] - 2019-09-06

### Noteworthy Changes

- Δ Smiley menu is placed below menu buttons in posting form #349

### Update Notes

[Full change-log](https://github.com/Schlaefer/Saito/compare/5.3.1...5.3.2)

## [5.3.1] - 2019-09-01

### Noteworthy Changes

- ✓ Category order of select input in posting form is wrong #345
- ✓ Force browser to load an updated language .json file #346
- ✓ 5.3 updater fails on pre 5.2 installations if uploads without title exist #347
- ✓ Editing a posting doesn't trigger an autoresize on the textarea #348

### Update Notes

[Full change-log](https://github.com/Schlaefer/Saito/compare/5.3.0...5.3.1)

## [5.3.0] - 2019-08-30

### Noteworthy Changes

#### From the Changelog

- ＋ Send posting before moving on from posting form #338
- ＋ Save drafts while composing a new posting
- ＋ Browser warns the user before navigating away from a posting form with input
- ＋ Favicon-indicator shows number of unread postings on background tabs with autoreload #95
- ＋ New setting `answeringAutoSelectCategory` to control category-selection in posting-form
- − Removes support for embedding new Flash videos (`<object>...`) #326
- ✓ Uploading PNG images allows double-uploads #343
- ✓ Fixes several bugs causing Internal Error issues
- ✓ Don't autolink file:// URIs #341
- ✓ Internal posting-hashtag in parenthesis isn't linked #337
- Δ Changes the default DB engine for the `entries` table from MyISAM to InnoDB #322
- Δ Keeping track of online users is more accurate while requiring less resources
- Δ Font files for default theme are served locally instead from Google (everything is served locally now)
- Δ Disables Security component on login #339
- Δ PHP code maintenance
  - Δ Improves code quality so it passes phpstan static code analysis on level 3 (was 1)
  - Δ Declares all `src/` and `plugins/` PHP files as strict
  - Δ Refactors handling of current user's state
- Δ Core library updates (CakePHP 3.8, TypeScript 3)

#### Never Lose A Posting Again

5.3.0 refactors and improves a lot of code including keeping track of the current user and posting a new entry. Both touches important functionality and our oldest code paths (reaching back even before the `git init` of this repository). They accumulated a lot of cruft over the years.

This was also the occasion to introduce exciting new features:

In the past sending the posting-form was mainly a simple HTTP POST request. If something went wrong the content was gone. The browser's back button wasn't much of a help. From now on a posting is sent in the background before leaving the posting-form. If there's a server error or a connection problem the user is notified and won't lose the posting staring at a blank page.

While composing a new posting the content is continuously saved as a draft in the background. On the chance that something is going wrong while composing a posting the draft is restored when the user opens the posting form again.

### Update Notes

#### New Setting `answeringAutoSelectCategory`

There's a new setting `answeringAutoSelectCategory` in `config/saito_config.php`.  It allows to select a default category for new postings.

If `true` the first available category (by category-order and accessibility according to user rights) is preselected as default category in the posting form. If `false` the user is forced to select a category.

Default: `false` (same behavior as in previous versions).

#### Changing Entries Table from MyISAM to InnoDB #332

This update changes the last and biggest table - containing all postings - from MyISAM to the modern InnoDB database-engine. According to my benchmarks this switch shouldn't impose a major performance impact anymore.

The updater is going to convert the table automatically, but be aware that your PHP-script runtime is limited on a shared-hoster. The conversion may take several minutes depending on the number of postings and exceed that period. So you might end up sitting in front of a blank page wondering what happened. If your forum contains more than 100.000 postings I recommend converting the table manually before starting the updater. Execute e.g. in phpMyAdmin:

```sql
ALTER TABLE entries ENGINE=InnoDB;
```

As always: Backup your database before performing an update.

[Full change-log](https://github.com/Schlaefer/Saito/compare/5.2.1...5.3.0)

## [5.2.1] - 2019-07-01

### Noteworthy Changes

- ✓ Deleting an user doesn't properly clean-up the user's postings and leaves the entries table in a dirty state

### Update Notes

*An update to 5.2.1 is highly recommended.* - Don't delete an user on version 5.0.0+ before updating to 5.2.1. A manual DB fix is possible and not very complicated, open an issue if you ran into this issue and require assistance.

For a quick in-place upgrade just update `src/Model/Table/EntriesTable.php`  and ` src/Lib/version.php`.

[Full change-log](https://github.com/Schlaefer/Saito/compare/5.2.0...5.2.1)

## [5.2.0] - 2019-07-13

### Noteworthy Changes

5.2 is a feature update with a considerably enhanced uploader and quality of life improvements for user management.

- ＋ Image uploader is extended to a general purpose uploader #325
- ＋ Privileged users may see the user-account activation status in user-profile and user-list
- ＋ Privileged users may contact normal users even if the user has messaging disabled #336
- ＋ Privileged users may directly set a user's password #108
- ＋ Domain info after link takes Public Suffix List into account
- ✓ RSS feed item doesn't show username
- ✓ RSS feed item doesn't show correct date
- ✓ Bootstrap toasts are themed bright in night-theme
- ✓ Bootstrap toasts are placed beneath modal dialogs
- ✓ Domain info after link breaks on URLs with special chars
- ✓ i18n for deleting categories including German l10n
- Δ Increases font size of the default theme
- Δ Updates marionette.js to version 4

### Update Notes

#### Uploader

Upload-settings in the admin panel have been removed. Write down your settings (max number of uploads per user) before updating. The Uploader is configured in [`config/saito_config.php`](https://github.com/Schlaefer/Saito/blob/5.2.0/config/saito_config.php#L95) now. Individual file-types and file-size per type are configurable. The default settings allow uploading of common Internet media formats (images, audio , video and text-files).

#### Access-control

- ＋ `saito.core.user.activate` - See activation status (default-groups: admin)
- ＋ `saito.core.user.password.set` - Change user password (default-groups: admin)
- Δ  `saito.core.user.view.contact` becomes `saito.core.user.contact` - Allows viewing contact data and messaging via contact-form (default-groups: admin)

I forgot to mention the access-control permissions in the 5.0.0 release notes, didn't I? As I said: version 5 was a big update and most of it happened many moons ago. You'll find the meat [here](https://github.com/Schlaefer/Saito/blob/5.2.0/src/Lib/Saito/User/Permission.php). While the forum is still shipping and tested with the default administrator, moderator, user, and anonymous groups, it is possible to configure those groups - or create your own if you feel adventurous.


[Full change-log](https://github.com/Schlaefer/Saito/compare/5.1.0...5.2.0)

## [5.1.0] - 2019-06-13

### Noteworthy Changes

5.1.0 is a bugfix and maintenance release.

- ＋ bumps minimum required PHP Version from 7.1 to 7.2+
- ✓ Creating bookmarks not working on new 5.0.0 installation #334
- Δ Updating removes database compatibility to Saito 4.10 #323 #324
- Δ Default database charset and collation for new installation changes from utf8 to utf8mb4 #333
- Δ Rewritten installer #335
- Δ Refactored user-blocking internals
- Δ Updates libraries (esp. CakePHP 3.7 and Bootstrap 4.3)

### Update Notes

Utf8mb4 is required for full Emoji-support. Existing installation have to update the table and columns to utf8mb4 manually. If you're fine without Emoji support just set `encoding => 'utf8'` as connection parameter for the dabase in `config/app.php` and everything is going to work as before.

See [full change-log](https://github.com/Schlaefer/Saito/compare/5.0.0...5.1.0) or the [milestone](https://github.com/Schlaefer/Saito/issues?utf8=%E2%9C%93&q=milestone%3A5.1.0+)

## [5.0.0] - 2019-06-10

### What's new

Hello!

Saito 5 is big rewrite of major parts of the forum. 90% of the work took place in late 2015, but I burned out at the end, so it didn't make it out of the door. The remaining 9.9% were done in the first half of 2018. 2019 sees the release – finally.

On the backend the update from CakePHP 2.x to CakePHP 3.x is the most noteworthy, which was a considerable effort.

The frontend-stack moved from bower, RequireJS, Marionette 1.x and Javascript over to yarn, webpack, Marionette 3.x with parts starting to migrate to Typescript. The UI is based on Bootstrap now, which should offer a more accessible theming-environment.

Overall there's a stronger separation between frontend and backend. The major theme is that the PHP-backend is going to provide a new JSON based API with JWT authentication which is accessed by a independent frontend JS application. The rewritten image-uploader and bookmarks features being the first incarnations of this transition.

Future-proving the code-base was the main goal, but there are also feature changes.

Users are able to set an avatar image now. The layout is better optimised for mobile devices. Category-access-rights are more fine grained. The image-uploader is rewritten and improved (auto-rotate images by EXIF-metadata, remove medata, compress images, thumbnails on index page). The posting form is a custom implementation, which allows more flexibility (sub-paragraph citations, better dialogs for inserting content and esp. for smilies). Embedding of rich 3rd-party content doesn't rely on an external provider anymore.

On the other hand less popular features didn't made the transition: Shoutbox, community-map, separate mobile-version, email-notifications on answers, admin-stats, …

### Update

#### Migrating from 4.x

Saito 5.0.0 requires PHP 7.1 but is able to run on the same DB as Saito 4.10 (meaning that the DB updates for Saito 5 don't break 4.10). This allows you to move from PHP 5 to 7 with 4.10 and gently switch to Saito 5.

Saito 5 includes an automated database updater, so no more manually updating the DB with raw SQL commands. *Yeah!* **But** ... I can only hope that you applied the manual steps of the past by the letter. I also assume that your database structure is in the same state as a vanilla 4.10 installation. The automated updater may fail if it isn't ...

***Please do a database-backup before updating!*** – *This is not a drill!*

[The database connection](https://book.cakephp.org/3.0/en/orm/database-basics.html#configuration)  is set in `config/app.php`. Enter your existing security salt there too.

There's no support for table prefixes anymore. If prefixes were used in the past rename the tables to an unprefixed version.

#### Theming

The new default theme "Bota" replaces "Paz". It is implemented as a [CakePHP 3 theme plugin](https://book.cakephp.org/3.0/en/views/themes.html) and lives in `plugins/Bota`. The UI is implemented as [Bootstrap 4](https://getbootstrap.com/docs/4.1/getting-started/introduction/) theme.

To start your own theme I recommend using SASS and referencing and customizing the default theme.

```
// e.g. in "plugins/YourTheme/webroot/css/src/theme.scss"
// set YourTheme in config/saito_config.php

//// Change Bootstrap variables

$body-color: #222;
...

//// Include the main theme which will pick up the Bootstrap variable values

@import "../../../../../plugins/Bota/webroot/css/src/theme";

//// Additional customizations tweaking the default theme

@import "_your_customizations.scss";

body {
  // more customizations
}
```

Otherwise you have to bring your own Bootstrap-theme and layout additional forum properties from scratch.

## [4.1.10] - 2019-06-10

### What's new

- ✓ Fixes incorrect table setup by the installer

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.10.0...4.10.1)

## [4.10.0] - 2019-05-09

### What's new
- ＋ PHP 7.1 compatibility
- ＋ Prepares update to Saito 5
- ＋ adds new API-endpoint `users/online`
- ✓ fixes smaller issues on MariaDB
- ✓ [mobile] fixes smilies und BBCode in Shoutbox
- ✓ [mobile] fixes issues on login and logout
- ✓ [mobile] fixes bugs when editing a posting starting a thread
- ✓ [mobile] fixes issues when trying to view non-existing threads
- Δ if embed.ly is disabled existing `[embed]`-tags will present a HTML-link

```sql
ALTER TABLE `user_blocks`CHANGE `by` `blocked_by_user_id` int(11) unsigned NULL DEFAULT NULL;
RENAME TABLE `user_read` TO `user_reads`;

ALTER TABLE `settings` ADD `id` INT(11)  NOT NULL  AUTO_INCREMENT  PRIMARY KEY FIRST;
INSERT INTO `settings` (`name`, `value`) VALUES ('db_version', '4.10.0');

ALTER TABLE `users` CHANGE `user_signatures_images_hide` `user_signatures_images_hide` TINYINT(1)  NOT NULL  DEFAULT '0';
```

It is possible your settings table already has an "id"-column. In that case make sure it's auto-increment and add a "db_version" key with value "4.10.0" manually.

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.8.0...4.10.0)

## [4.9.0]

Unreleased and rolled into 4.10.0.

## [4.8.0] - 2015-11-18

- \+ show remaining chars for subject #312
- \+ set default format for youtube video fallback from 4:3 to 16:9 #316
- \+ add [quote] BBCode tag #317
- \+ show PHP-info in admin panel
- ✓ [mobile] improved reliability when starting the mobile app
- ✓ [mobile] app data isn't updated on Internet Explorer
- ✓ improve [float] BBCode-tag
- ✓ improve embed.ly embedding
- Δ relax CSRF protection when creating new postings
- Δ update CakePHP from 2.6.7 to 2.6.12

## [4.7.5] - 2015-11-15

### What's new
- ✓ caches were not cleared out on certain operations
- ✓ hide other users signature images not working #315
- ✓ accession check on categories not always applied
- ✓ improved localization
- Δ update CakePHP from 2.6.3 to 2.6.7

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.7.4...4.7.5)

## [4.7.4] - 2015-03-21

### What's new
- ✓ don't include complete web pages with embedly #314
- ✓ posting in mobile app not working #313
- Δ update to CakePHP 2.6.3

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.7.3...4.7.4)

## [4.7.3] - 2015-02-28

### What's new
- ＋ log user-agent in `saito-<*>.log` files
- ✓ maps where not working because of API change on mapquest.com
- ✓ user is not shown in userlist-slidetab #307
- ✓ don't show ?mar in URL for non-aMAR users
- ✓ improves german localisation
- Δ updates CakePHP from 2.6.0 to 2.6.2
- Δ minor refactoring

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.7.2...4.7.3)

## [4.7.2] - 2015-01-10

### What's new
- ✓ HTML-entities created by BBCode-parser followed by a parenthesis trigger wink smiley #311

Minor code refactoring.

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.7.1...4.7.2)

## [4.7.1] - 2015-01-04

### What's new
- ✓ cite button in answering form doesn't insert text #308 (was bug in flattr-plugin)
- Δ Update CakePHP to 2.6.0 #309
- Δ Update jQuery to 2.1.3 #310

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.7.0...4.7.1)

## [4.7.0] - 2014-12-13

### What's new
- ＋ Set sort order for non-logged-in users to last-answer #304
- ＋ add drop shadow to simley-popup in entries/add #303
- ✓ fix bullet CSS in bookmark index #298
- ✓ fix badges (via plugin) margin #301
- ✓ fix default citation mark in bbcode doc #302
- ✓ fix timing in test case #305
- Δ rename table column Smilies.order to Smilies.sort #300
- Δ rename table column Entry.category to Entry.categories_id #299

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.6.0...4.7.0)

### Migration Notes

<span class="label label-warning">_Note:_</span> If you use a table prefix you have to prepend it to the table name.

``` mysql
ALTER TABLE `entries` CHANGE `category` `category_id` INT(11)  NOT NULL  DEFAULT '0';
ALTER TABLE `smilies` CHANGE `order` `sort` INT(4)  NOT NULL  DEFAULT '0';
```

## [4.5.0] - 2014-11-08

### What's new
- ✓ fixes an issue when composer wasn't able to find the pear CakePHP package
- ✓ fixes path issue when installing on MS Windows
- ✓ fixes PostgreSQL support
- Δ refactors BBCode-renderer into a plugin (included and activated by default)
  - ✓ fixes @Username is not linked before linebreak
  - \- removes [u] underline BBCode tag
  - \- removes `.c-bbcode-<#>` CSS-classes
- Δ CSS class `.staticPage` was renamed to `.richtext`
- Δ composer root is now in `app/`
- \- removes plugins Flattr, NsfwBadge and Userranks (see Migration Notes)

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.4.0...4.5.0)

### Migration Notes

#### Set Parser

Set the parser in your `saito_config.php`. Default is:

``` php
Configure::write('Saito.Settings.ParserPlugin', 'Bbcode');
```

which points to `app/Plugin/<Bbcode>Parser`.

#### Plugin Source

The removed plugins have their own repositories now:
- https://github.com/Schlaefer/saito-flattr (composer: schlaefer/saito-flattr)
- https://github.com/Schlaefer/saito-nsfwbadge (composer: schlaefer/saito-nsfwbadge)
- https://github.com/Schlaefer/saito-userranks (composer: schlaefer/saito-userranks)

Download them manually and put them into `app/Plugin` or install them via composer.

## [4.4.0] - 2014-10-26

### What's new
- ＋ adds hooks for extending the core (see `docs/dev-hooks.md`)
- ✓ quote symbol set in admin-settings is ignored
- Δ refactors user-ranks
  - \- removes user-ranks from core (still available as example plugin, see `app/Plugins/Userranks`)
- Δ refactors flattr support
  - \- removes flattr from core (still available as plugin, see `app/Plugins/Flattr`)
  - ✓ no flattr button on user-profile
- Δ refactors "Not Safe For Work"-badge
  - \- removes NSFW-badge from core (still available as plugin, see `app/Plugins/NsfwBadge`)
- Δ refactors user-blocking
  - ＋ automatically unblock blocked users after a specified time
  - ＋ moderators and admins see blocking history in user-profile
  - ＋ admins see global blocking history in admin-area
- Δ refactors smiley handling
  - ＋ introduces new HDPI-ready smiley icons in default theme
  - ＋ allows localization of smiley-titles
  - Δ changes default smiley-set
  - Δ allows usage of pixel or font based smilies
- Δ changes quote symbol for new installations from `»` to `>`

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.3.5...4.4.0)

### Migration Notes

#### DB Changes

<span class="label label-warning">_Note:_</span> If you use a table prefix you have to prepend it to the table name.

``` sql
CREATE TABLE `user_blocks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `by` int(11) unsigned DEFAULT NULL,
  `ends` datetime DEFAULT NULL,
  `ended` datetime DEFAULT NULL,
  `hash` char(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ends` (`ends`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
```

#### Remove Userranks

If you don't activate Userranks again remove its the DB entries:

``` mysql
DELETE FROM `settings` WHERE `name` IN ('userranks_show');
DELETE FROM `settings` WHERE `name` IN ('userranks_ranks');
```

#### Remove Flattr

Remove the old flattr config, its now set in in the Flattr plugin `config.php`:

``` mysql
DELETE FROM `settings` WHERE `name` IN ('flattr_category','flattr_enabled','flattr_language');
```

If you don't activate Flattr again you should remove its existing DB entries:

``` mysql
ALTER TABLE `users` DROP `flattr_allow_posting`;
ALTER TABLE `users` DROP `flattr_allow_user`;
ALTER TABLE `users` DROP `flattr_uid`;

ALTER TABLE `entries` DROP `flattr`;
```

#### Remove "Not Safe For Work"-badge

If you don't activate the "Not Safe For Work"-badge again you should remove its existing DB entries:

``` mysql
ALTER TABLE `entries` DROP `nsfw`;
```

#### New Smiley-Set

The easiest way to get the new smiley set is to drop the existing smiley-configuration database tables and recreated them (empty the cache in the admin-area afterwards):

``` mysql
DROP TABLE IF EXISTS `smilies`;

CREATE TABLE `smilies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order` int(4) NOT NULL DEFAULT '0',
  `icon` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `image` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `smilies` (`id`, `order`, `icon`, `image`, `title`)
VALUES
    (1, 1, 'happy', NULL, 'smilies.t.smile'),
    (2, 2, 'grin', '', 'smilies.t.grin'),
    (3, 3, 'wink', '', 'smilies.t.wink'),
    (4, 4, 'saint', '', 'smilies.t.saint'),
    (5, 5, 'squint', '', 'smilies.t.sleep'),
    (6, 6, 'sunglasses', '', 'smilies.t.cool'),
    (7, 7, 'heart-empty-1', '', 'smilies.t.kiss'),
    (8, 8, 'thumbsup', '', 'smilies.t.thumbsup'),
    (9, 9, 'coffee', NULL, 'smilies.t.coffee'),
    (10, 10, 'tongue', '', 'smilies.t.tongue'),
    (11, 11, 'devil', NULL, 'smilies.t.evil'),
    (12, 12, 'sleep', '', 'smilies.t.blush'),
    (13, 13, 'surprised', NULL, 'smilies.t.gasp'),
    (14, 14, 'displeased', '', 'smilies.t.embarrassed'),
    (15, 15, 'unhappy', '', 'smilies.t.unhappy'),
    (16, 16, 'cry', '', 'smilies.t.cry'),
    (17, 17, 'angry', '', 'smilies.t.angry');


DROP TABLE IF EXISTS `smiley_codes`;

CREATE TABLE `smiley_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `smiley_id` int(11) NOT NULL DEFAULT '0',
  `code` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `smiley_codes` (`id`, `smiley_id`, `code`)
VALUES
    (1, 1, ':-)'),
    (2, 1, ':)'),
    (3, 2, ':-D'),
    (4, 2, ':D'),
    (5, 3, ';-)'),
    (6, 3, ';)'),
    (7, 4, 'O:]'),
    (8, 5, '(-.-)zzZ'),
    (9, 6, 'B-)'),
    (10, 7, ':-*'),
    (11, 8, ':grinw:'),
    (12, 9, '[_]P'),
    (13, 9, ':coffee:'),
    (14, 10, ':P'),
    (15, 10, ':-P'),
    (16, 11, ':evil:'),
    (17, 12, ':blush:'),
    (18, 13, ':-O'),
    (19, 14, ':emba:'),
    (20, 14, ':oops:'),
    (21, 15, ':-('),
    (22, 15, ':('),
    (23, 16, ':cry:'),
    (24, 16, ':\'('),
    (25, 17, ':angry:'),
    (26, 17, ':shout:');
```

Otherwise you have to make the changes in the admin area.

If you want to stick with the old icons: don't change anything and copy over the smilies theme folder from the previous version.

## [4.3.5] - 2014-10-21

### What's new
- ✓ fixes broken entries/edit form on validation error

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.3.4...4.3.5)

## [4.3.4] - 2014-10-10

### What's new
- ✓ fixes slidetab reordering is not stored on the server
- ✓ fixes some caches are not persistently cleared out
- ✓ fixes a performance regression caused by erroneously cleared caches when adding/editing a posting
- Δ only show small notice if search words are too short

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.3.3...4.3.4)

## [4.3.3] - 2014-10-09

### What's new
- ✓ fixes showing wrong category in posting tree

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.3.2...4.3.3)

## [4.3.2] - 2014-10-09

### What's new
- ＋autofocus first text field in search
- ✓ fixes no recent postings on profile page of ignored users
- ✓ fixes ignored postings are shown in mix view
- ✓ fixes auto-link in [url] BBCode-tag
- ✓ fixes no admin edit of user profile page because of similar name already exists
- Δ shows ignored postings as invisible but clickable placeholders
- Δ update to CakePHP 2.5.5, jQuery 2.1.1 and latest require.js
- Δ code refactoring

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.3.1...4.3.2)

## [4.3.1] - 2014-09-28

### What's new
- ✓ fixes issues when posting an answer with Safari Mobile

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.3.0...4.3.1)

## [4.3.0] - 2014-09-27

### What's new
- ＋ make ignored postings more flexible by using a CSS `.ignored` class #287
- ＋ improves detection for password autofill
- ＋ prevents iframe embedding by setting `X-Frame-Options` header
- ＋ help pages open in new window
- ✓ improves blackholed behavior and documentation #286
- Δ move "Advanced Search"/"Simple Search" navigation to navbar #288
- Δ refactors thread-tree and mix-tree rendering

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.2.1...4.3.0)

## [4.2.1] - 2014-09-14

### What's new
- ＋ show postings in profile of ignored user #280
- ✓ default search order is not applied in users/index #282
- ✓ "Neu Antwort" in german l10n email notification #279
- ✓ deleting category in admin backend fails #285
- ✓ creating new category in admin panel doesn't empty category cache #284
- Δ update to CakePHP 2.5.4+ #283

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.2.0...4.2.1)

## [4.2.0] - 2014-09-01

### What's new
- ＋ ignore users #276
- ＋ performance improvements in mix view
- ✓ i10n in contacts/<*> headers #277
- ✓ adds missing back-links in contact form
- ✓ cache prefix not set for default cache #278
- Δ switch thread cache from whole threads to thread-lines #275

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.1.0...4.2.0)

### Migration Notes

#### DB Changes

<span class="label label-warning">_Note:_</span> If you use a table prefix you have to prepend it to the table name.

``` mysql
CREATE TABLE `user_ignores` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `blocked_user_id` int(11) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `blocked_user_id` (`blocked_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `users` ADD `ignore_count` int(10) unsigned NOT NULL DEFAULT '0';
```

## [4.1.0] - 2014-08-08

### What's new

This release improves user experience for non-logged-in users by providing a MAR. This may increase server load.
- ＋ Mark As Read for anonymous users #274
- ＋ link to help-page source in help-page footer
- ＋ requests (view, mix) of non-public posting asks for login to access that posting instead of redirecting to homepage
- ＋ on registration a new username must at least two characters off to any existing username to be available
- ✓ fixes dummy_data shell
- Δ refactors contact code
  - Δ URL to contact admin changes from `/users/contact/0` to `/contacts/owner/`
  - Δ URL to contact users changes from `/users/contact/<id>` to `/contacts/user/<id>`

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.0.5...4.1.0)

## [4.0.5] - 2014-07-27

### What's new
- ✓ fixes [e] BBCode-tag bleeds into following content #270
- ✓ ongoing CSRF blackholing in 4.0.4 #269
- Δ optimizes composer autoload performance in release build #272
- Δ updates jBBCode to 1.3 #271
- Δ updates CakePHP to 2.5.3
- Δ code refactoring

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.0.4...4.0.5)

## [4.0.4] - 2014-07-05

### What's new
- ＋ switches BBCode parser to jBBCode
  - Δ changes languages selection in [code]-tag from `[code <lang>]` to `[code=<lang>]` (no backwards compatibility/breaks existing BBCode)
- ＋ less strict security settings to prevent overly eager CSRF-blackholing
- ＋ adds vine to to allowed video domains
- ＋ makes simple search available for non-logged in users
- ＋ performance improvements
- ✓ fixes new threads don't show up in recent entries s(l)idebar
- ✓ fixes orphaned entries in `user_read` table
- ✓ fixes no eng. l10n for markitup link-popup
- ✓ breaks long words in slidetab to next line
- Δ updates CakePHP to 2.5.2

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.0.3...4.0.4)

## [4.0.3] - 2014-06-17

### What's new
- ✓ improves word-length-detection in simple-search

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.0.2...4.0.3)

## [4.0.2] - 2014-06-10

### What's new
- ✓ blank page when changing password #266
- ✓ tab behavior in register and login form broken #267
- ✓ log blackholed requestes #268

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.0.1...4.0.2)

## [4.0.1] - 2014-06-08

### What's new
- ＋ clear localStorage on logout #262
- ✓ includes jasmine js test in cli test runner #242
- ✓ internal error if categories are activated on user profile for the first time #263
- ✓ layout Category popup gobbeld #265
- ✓ improves Category popup positioning
- ✓ Sending Category form is blackholed #264

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.0.0...4.0.1)

## [4.0.0] - 2014-05-29

### What's new

All changes of 4.0.0-RC - 4.0.0-RC3 and
- ✓ Inline Answering not working with SecurityComponent enabled #261
- ✓ blob → mediumblob conversion for ecaches table was not applied in installer
- Δ changes default cookie encryption to AES
- Δ deactivates form autofill in login and registration form

[Full change-log](https://github.com/Schlaefer/Saito/compare/4.0.0-RC3...4.0.0)

## [4.0.0-RC3] - 2014-05-18

### What's new
- ✓ fixes logout not working
- ✓ fixes non collapsing back links in responsive design
- Δ Updates CakePHP to 2.5.1

[See full change-log.](https://github.com/Schlaefer/Saito/compare/4.0.0-RC2...4.0.0-RC3)

## [4.0.0-RC2] - 2014-05-16

### What's new
- ✓ thread cache isn't checked appropriately and reads/saves wrong output
- ✓ skip not implemented and failing pgsql simple search test case
- Δ code refactoring

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/4.0.0-RC...4.0.0-RC2)

## [4.0.0-RC1] - 2014-04-29

### What's new
- ＋ Don't update view counter on search engine robots #243
- ＋ extended crawler/robots detection
- ＋ improves autolinking of URLs next to punctuation marks
- ＋ shows used cache engines in system info admin panel
- ＋ add doc link and "where to edit" info to /users/map #247
- ＋ sort threads by last answer is default now
- ＋ /users/index is always sorted alphabetically after primary sort parameter
- ＋ PostgreSQL support #259 (except for simple search)
- ✓ show date on older shoutbox entries #251
- ✓ absolute date in mobile view is gobbled #170
- ✓ limit map boundaries and minimum zoom-level
- ✓ [bbcode] urls are not parsed in lists #256
- ✓ theme error on /users/edit on validation error #244
- ✓ deleting last bookmark should show "no bookmarks" message #75
- ✓ installation creates `BLOB` instead of `MEDIUMBLOB` field in `ecaches` table
- ✓ global help button is not activated on answering form
- ✓ i18n decimal divider in generation time
- ✓ fixes no pointer cursor on .btn-strip hover
- ✓ Double entries in UserOnline slidebar #157
- ✓ accession 1 entries-url should not be in sitemap
- Δ rewritten user login #254
  - ＋ shows info if user account is not activated yet
  - ＋ autofill username on failing login in login-form
  - ＋ autofocus/select username in login-form
  - ＋ log failing logins
- Δ rewritten user registration #253
  - ＋ shows info if sending of confirmation email failed
  - ＋ log if sending of confirmation email failed
  - ＋ shows info if confirmation link failed
  - ＋ shows info if account was already activated
  - ＋ adds navigation back-links to registration views
  - Δ l10n changes
  - ！confirmation-URL in activation-email changed
- Δ rewritten user change password #255
  - ＋ log change attempts for non-existing users
  - ＋ log change attempts by non-authorized users
- Δ refactors bookmark edit
  - ＋ log edit attempts by non-authorized users
- Δ refactors contact messaging
  - ＋ advanced email address configuration #223
  - ＋ show disclaimer on global contact form
  - ＋ adds navigation back-link
  - ＋ logs if sending of contact email failed
- ＋ CakePHP `dummy_data` shell to generate artificial content for development
- Δ changes disclaimer l10n strings
- Δ Update to CakePHP 2.5.0 #246
- Δ replaces underscore.js with lo-dash
- Δ activate CakePHP's SecurityComponent by default
- Δ renames log file `auth.log` to `saito-auth.log`
- Δ add staticPage layout for all `pages` esp. TOS #257
- Δ consolidates database field types
- Δ consolidates database index names
- Δ removes unused database fields

Other bugfixes and improvements. This updates includes important security enhancements.

### Migration Notes

#### DB Changes

<span class="label label-warning">_Note:_</span> If you use a table prefix you have to prepend it to the table name.

<span class="label label-warning">_Note:_</span> Depending on DB-size these may run some time. Make a DB backup and apply separately.

``` mysql
DROP TABLE IF EXISTS `useronline`;

CREATE TABLE `useronline` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_id` int(11) DEFAULT NULL,
  `logged_in` tinyint(1) NOT NULL,
  `time` int(14) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `useronline_uuid` (`uuid`),
  KEY `useronline_userId` (`user_id`),
  KEY `useronline_loggedIn` (`logged_in`)
) ENGINE=MEMORY AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `bookmarks` DROP INDEX `entry_id-user_id`;
ALTER TABLE `bookmarks` ADD INDEX `bookmarks_entryId_userId` (`entry_id`, `user_id`);

ALTER TABLE `bookmarks` DROP INDEX `user_id`;
ALTER TABLE `bookmarks` ADD INDEX `bookmarks_userId` (`user_id`);

ALTER TABLE `entries` DROP INDEX `user_id`;
ALTER TABLE `entries` ADD INDEX `entries_userId` (`user_id`);

ALTER TABLE `entries` DROP INDEX `user_id-time`;
ALTER TABLE `entries` ADD INDEX `entries_userId_time` (`time`, `user_id`);

ALTER TABLE `entries` CHANGE `last_answer` `last_answer` TIMESTAMP  NULL  DEFAULT NULL;
UPDATE `entries` SET last_answer=NULL WHERE last_answer='0000-00-00 00:00:00';

ALTER TABLE `entries` CHANGE `edited` `edited` TIMESTAMP  NULL  DEFAULT NULL;
UPDATE `entries` SET edited=NULL WHERE edited='0000-00-00 00:00:00';

ALTER TABLE `users` CHANGE `last_login` `last_login` TIMESTAMP  NULL  DEFAULT NULL;
UPDATE `users` SET last_login=NULL WHERE last_login='0000-00-00 00:00:00';

ALTER TABLE `users` CHANGE `registered` `registered` TIMESTAMP  NULL DEFAULT NULL;

ALTER TABLE `users` CHANGE `last_refresh` `last_refresh` TIMESTAMP  NULL DEFAULT NULL;
UPDATE `users` SET last_refresh=NULL WHERE last_refresh='0000-00-00 00:00:00';

ALTER TABLE `users` CHANGE `last_refresh_tmp` `last_refresh_tmp` TIMESTAMP  NULL DEFAULT NULL;
UPDATE `users` SET last_refresh_tmp=NULL WHERE last_refresh_tmp='0000-00-00 00:00:00';

ALTER TABLE `users` CHANGE `personal_messages` `personal_messages` TINYINT(1)  NOT NULL  DEFAULT '1';
ALTER TABLE `users` CHANGE `user_lock` `user_lock` TINYINT(1)  NOT NULL  DEFAULT '0';
ALTER TABLE `users` CHANGE `user_signatures_hide` `user_signatures_hide` TINYINT(1)  NOT NULL  DEFAULT '0';
ALTER TABLE `users` CHANGE `user_automaticaly_mark_as_read` `user_automaticaly_mark_as_read` TINYINT(1)  NOT NULL  DEFAULT '1';
ALTER TABLE `users` CHANGE `user_sort_last_answer` `user_sort_last_answer` TINYINT(1)  NOT NULL  DEFAULT '1';
ALTER TABLE `users` CHANGE `show_recententries` `show_recententries` TINYINT(1)  NOT NULL  DEFAULT '0';
ALTER TABLE `users` CHANGE `user_category_custom` `user_category_custom` VARCHAR(512)  CHARACTER SET utf8  COLLATE utf8_unicode_ci  NULL  DEFAULT NULL;

ALTER TABLE `users` CHANGE `activate_code` `activate_code` INT(7)  NOT NULL  DEFAULT '0';

ALTER TABLE `entries` DROP `uniqid`;
ALTER TABLE `users` DROP `last_logout`;
ALTER TABLE `users` DROP `hide_email`;
ALTER TABLE `users` DROP `user_show_own_signature`;

INSERT INTO `settings` (`name`, `value`) VALUES ('email_contact', NULL);
INSERT INTO `settings` (`name`, `value`) VALUES ('email_register', NULL);
INSERT INTO `settings` (`name`, `value`) VALUES ('email_system', NULL);
```

_If_ you're using MySQL and the field `value` in the `ecaches` table is of type `BLOB` change it to `MEDIUMBLOB`:

``` mysql
ALTER TABLE `ecaches` CHANGE `value` `value` MEDIUMBLOB  NOT NULL;
```

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/3.5.1...4.0.0-RC)



## Older Changes

See  [CHANGELOG_OLD.md](docs/CHANGELOG_OLD.md) for older changes.
