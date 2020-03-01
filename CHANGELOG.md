# Change-Log

- ＋ Added
- ✓ Fixed
- Δ Changed
- − Removed

## [6.0.0]

- [Full commit-log](https://github.com/Schlaefer/Saito/compare/<last>...6.0.0)
- [Download release-zip](https://github.com/Schlaefer/Saito/releases/download/<next>/saito-release-master-<next>.zip)

### Changes

- ＋ Requires PHP 7.4+
- ＋ Updates to CakePHP 4
- Uploader
  - ＋ Configurable storage adapter (filesystem, AWS, Azure, (S)FTP, …)
- Internal changes:
  - Δ Use Cake's in-memory cache engine for PHP-tests
  - − Removes `AsserTrait::assertXPath` for `AssertTrait::assertContainsTag`

### Update Notes

#### CakePHP 4

Refer to the CakePHP 4 documentation for all changes. Note that the template file extension changes from `.ctp` to `.php` and that template files move from `View/Templates/` to `templates/` in the folder hierarchy.

## [Next]

- [Full commit-log](https://github.com/Schlaefer/Saito/compare/5.6.0...<next>)
- [Download release-zip](https://github.com/Schlaefer/Saito/releases/download/<next>/saito-release-master-<next>.zip)

### Changes

- ＋ Adds permission `saito.core.user.lastLogin.view` to see a user's last login (defaults to admin)
- ＋ Emit event `saito.core.user.activate.after` after user activation
- ＋ Emit event `saito.core.user.register.after` after user registration
- ＋ Adds plugin "Local" for local customization
- ✓ Improves wrapping of long words and links in posting #365
- ✓ Fixes localization in advanced search #364
- ✓ Missing navigation links in search head
- ✓ Internal error viewing posting where the thread starter was deleted
- Δ Set default period for advanced search to the last 12 months #354
- Δ Switches Bota-theme night/day button icon #366
- Uploader
  - ＋ Default target-size for resizing images is configurable
  - Δ Default target-size for resizing images is reduced from 820 kB to 450 kB
- Internal code changes
  - ＋ Tests PHP 7.4 on travis-ci
  - ＋ Run phpcbf and phpcs with multiple threads
  - ＋ Improve error display before settings are loaded
  - ✓ Fixes phpstan deprecated warnings
  - Δ Improves scanning of JS localizaton strings
  - Δ Updates core JS-, CSS- and PHP-libraries
  - Δ Updates travis-ci environment from trusty to bionic
  - Δ Consolidates PHP event names updates documentation

### Update Notes

Plugins subscribing to events may have to update event-names. See *docs/dev-hooks.md* for available events.

The plugin Local in "plugins/local" allows extending the forum in a CakePHP fashion without running composer.

## [5.6.0] - 2020-01-03

- [Full commit-log](https://github.com/Schlaefer/Saito/compare/5.5.0...5.6.0)
- [Download release-zip](https://github.com/Schlaefer/Saito/releases/download/saito-release-master-5.6.0.zip)

### Changes

- ＋ Adds permission `saito.core.posting.solves.set` for marking a posting as solution/helpful (defaults to thread creator).
- ＋ Improves compatibility with PHP 7.3
- ＋ Improves browser detection for changes in the Bota theme CSS
- ＋ Improves logging of unauthorized access
- ✓ Deleting a bookmark creates an empty area above the bookmarks
- ✓ User roles with ID greater than 3 can't be assigned to category access control
- ✓ Fixes link to default favicon if installed in subdirectory
- Δ Adds "Saito" prefix to CSRF-cookie name
- Δ Moves layout for viewing a posting and answering from center to the left
- Δ Updates Saito default favicon
- − Removes visiblity description for category in category-title hover
- Search:
  - ✓ Internal error on simple search when results are sorted by rank
  - ✓ Internal error if search term contains multiple whitespaces
- Improves dark theme:
  - ✓ Drop down menus aren't styled
  - ✓ Code inserts aren't styled
  - Δ Exchanges dark and light distinction between background and form areas
  - Δ Darkens border and dividiers
- Uploader:
  - ＋ Adds filter options
  - ＋ Performance improvements for users with many (100+) uploads
  - ＋ Adds permission `saito.plugin.uploader.view` for viewing uploads (defaults to upload owner and group `admin`).
  - ＋ Adds permission `saito.plugin.uploader.add` for uploading new files (defaults to profile owner).
  - ＋ Adds permission `saito.plugin.uploader.delete` for deleting uploads (defaults to upload owner and group `admin`).
  - ＋ Adds "audio/ogg" and "audio/opus" to default allowed mime-types
  - ✓ Wrong error message is shown if no file was received on the server
  - Δ Layout improvements
- Internal code changes:
  - ＋ Minor changes for improved theming support
  - Δ Refactors creation, update and validation of postings
  - Δ Updates PHP and Javascript libraries
  - Δ Entries::Table throws RecordNotFoundException instead of returning null
  - Δ Update Apcu version in docker container to 5.1.18
  - Δ Drafts for new threads are stored with a `pid` of `0` instead of `NULL`
  - − Removes SaitoValidationProvider::validateAssoc with CakePHP build-in facility
  - − Removes abandonded Selenium test files

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


## Older Changes

See  [CHANGELOG_OLD.md](docs/CHANGELOG_OLD.md) for older changes.
