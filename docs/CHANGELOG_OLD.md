## [4.10.1] - 2019-06-10

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

## [3.5.1] - 2014-04-12

### What's new
- [fix] link to /users/map in /users/\* area even if maps are not activated #245
- [fix] map location validation is limited to ([0-90],[0-90]) #249

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/3.5.0...3.5.1)

## [3.5.0] - 2014-04-12

### What's new
- [new] Community Map #238
- [task] consolidates paginator navigation
- [task] updates CakePHP to 2.4.7 #241
- [task] updates Marionette, Backbone, Underscore to AMD versions
- [task] updates JS test frameworks, but disables CLI tests (see #242)

### Migration Notes

The new map feature utilizes MapQuest to render maps. You have to enter a MapQuest API-key in the admin area which you can issue for free at http://developer.mapquest.com/.

#### DB Changes

<span class="label label-warning">_Note:_</span> If you use a table prefix you have to prepend it to the table name.

```
ALTER TABLE `users` ADD `user_place_lat` FLOAT  NULL  DEFAULT NULL  AFTER `user_place`;
ALTER TABLE `users` ADD `user_place_lng` FLOAT  NULL  DEFAULT NULL  AFTER `user_place_lat`;
ALTER TABLE `users` ADD `user_place_zoom` TINYINT(4)  NULL  DEFAULT NULL  AFTER `user_place_lng`;

INSERT INTO `settings` (`name`, `value`) VALUES ('map_enabled', '0');
INSERT INTO `settings` (`name`, `value`) VALUES ('map_api_key', '');
```

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/3.4.1...3.5.0)

## [3.4.1] - 2014-04-06

### What's new
- [fix] internal error on /users/index in 3.4.0 #239
- [fix] error pages don't have theme applied #240

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/3.4.0...3.4.1)

## [3.4.0] - 2014-14-05

### What's new
- [new] improves entry semantic page structure
- [new] show entry view counter only for logged in users
- [new] show category title on every `view` and `mix` subentry
- [new] exclude all `/users/…` pages from `robots.txt`
- [new] detects protocol-less absolute urls in [url] BBCode-tag
- [fix] FE notification messages are not localized
- [fix] sitemap entries improved timing
- [fix] sitemap entries `lastmod` ignores edits
- [fix] missing l10n
- [fix] Paz CSS tweaks
- [task] removes unused database fields

Overall refactoring and performance improvements.

### Migration Notes

#### DB Changes

<span class="label label-warning">Note:</span> If you use a table prefix you have to prepend it to the table name.

```
ALTER TABLE `users` DROP `new_posting_notify`;
ALTER TABLE `users` DROP `new_user_notify`;
ALTER TABLE `users` DROP `time_difference`;
ALTER TABLE `users` DROP `user_view`;
ALTER TABLE `users` DROP `pwf_code`;
ALTER TABLE `users` DROP `user_forum_hr_ruler`;
```

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/3.3.0...3.4.0)

## [3.3.0] - 2014-03-30

### What's new
- [new] Show number of helpful answers in user-profile #222
- [new] admin-panel: performance improvements in user list
- [new] Saito installable via composer #232
- [new] Create sitemap.xml for Entries #20 ]
- [new] additional admin statistics #233
- [fix] Admin setting Thread Indent Depth is not applied #234
- [fix] Notification emails are not send #236 (regression from 3.2.0)
- [task] Switch user table from MyISAM to InnoDB #228
- [task] Update CakePHP to 2.4.6 #231
- [task] updates robots.txt #235
- [task] automated dev setup (see `docs/`) #225, #209

### Migration Notes

#### DB Changes

<span class="label label-warning">Note:</span> If you use a table prefix you have to prepend it to the table name.

For MySQL databases the user table default engine is InnoDB now:

```
ALTER TABLE `users` ENGINE = InnoDB;
```

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/3.2.1...3.3.0)

## [3.2.1] - 2014-03-22

### What's new
- [fix] Renaming user doesn't empty (thread) cache #221
- [fix] Don't show prerequisite error on browser prefetch #220

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/3.2.0...3.2.1)

## [3.2.0] - 2014-03-08

### What's new
- Search improvements
  - [new] sort by "rank" or "time" in  simple-search
  - [new] error message in simple-search if entered search-term is shorter than DB-config permits
  - [new] "jump to first page"-link in search-result navigation
  - [fix] Search for username matches substring #154
  - [change] Split simple and advanced search into two separate pages #218
- [new] Gelesene Beiträge tatsächlich als gelesen markieren. #96
- [fix] "subject is to long" error #219
- [change] Deprecate auto sanitizing in AppModel #217

### Migration Notes

#### DB Changes

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

```
CREATE TABLE `user_read` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `user_id` int(11) NOT NULL,
 `entry_id` int(11) NOT NULL,
 `created` datetime DEFAULT NULL,
 `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `entry_id` (`entry_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
```

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/3.1.2...3.2.0)

## [3.1.2] - 2014-03-02

### What's new
- [fix] Fix failing test case #216
- [fix] Paz Theme Tweaks #215
- [fix] smilies admin edit doesn't empty smiley cache #210
- [task] code refactoring for upcoming features

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/3.1.1...3.1.2)

## [3.1.1] - 2014-02-24

### What's new

Notable changes are:
- [fix] Fix new/old heading link color in inline-open and mix-view #214
- [fix] debug output in page footer should be full page width #211
- [fix] content of columns Image / Title missing in admin/smilies #87
- [fix] improves BBCode-tag handling quotes
- [fix] tightens username input validation

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/3.1.0...3.1.1)

## [3.1.0] 2014-02-22

### What's new

Notable changes are:
- [new] new help-system #206
- [new] introduce [e] tag for editing with appropriate icon #205
- [new] show newest registered user in disclaimer footer #183
- [new] increase view counter on all entries in thread when thread is viewed in mix view #66
- [new] Bring back bottom bar at least on entries/index in Paz default theme #201
- [change] Decrease interface elements and heading size in inline-opened posting (Paz) #203

### Migration Notes

Existing `app/Config/database.php` must be amended with a `$saitoHelp` configuration:

```
class DATABASE_CONFIG {

  public $default = array(
    …
  );

  public $saitoHelp = [
    'datasource' => 'SaitoHelp.SaitoHelpSource'
  ];

  …
```

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/3.0.2...3.1.0)

## [3.0.2] - 2014-02-17

### What's new

Notable changes are:
- [new] BBCode tag documentation in [docs/user-bbcodes.md](https://github.com/Schlaefer/Saito/blob/master/docs/user-bbcodes.md)
- [fix] Resolve page heading RSS feeds static page #190
- [fix] Fix i10n admin panel → create new user #196
- [fix] finish i10n admin interface #184
- [fix] Localize Merge Thread Form #60
- [fix] Registration time for admin in new installation is not set #195
- [fix] Set database default connection for installation to utf8 #200
- [fix] Fix failing travis test cases #199
- [change] swaps Home/Saito Home links in admin menu #40
- [change] renames common.js, main.js to common.min.js and main.min.js

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/3.0.1...3.0.2)

## [3.0.1] - 2014-02-11

### What's new

Bugfix release for 3.0.0. Inline-open "all" performance is greatly improved.

Notable changes are:
- [new] resolution independent and i10nable NSFW badge #188
- [fix] fix loading indicator lockup on inline-open #192
- [fix] BBCode detaginizer must preprocess Geshi content #193
- [fix] Center uploaded images #189
- [fix] finish i10n entries/add #185
- [fix] CSS for prerequisite warnings is missing #197
- [change] consolidate Paz theme JS #198
- [change] Update to CakePHP 2.4.5 #180

### Links
- [Full Changelog](https://github.com/Schlaefer/Saito/compare/3.0.0...3.0.1)

## [3.0.0] 2014-02-07

### What's new
- [new] new Default Theme Paz
- [new] changes named parameter `stopwatch:true` to GET request parameter `?stopwatch=true`
- [new] changes named parameter `lang:<lang>` to GET request parameter `?lang=<lang>`
- [new] completes english i10n in users/edit
- [new] dynamically resizes textarea in posting form
- [new] adds [float] BBCode tag
- [fix] comment text cut off in bookmarks/index
- [fix] fixes special char encoding when editing an existing bookmark comment
- [fix] fixes "open new" button not working/shown
- [fix] fixes internal hash links not working in BBCode-lists
- [change] signature is not longer shown below n/t- or in inline-opened postings
- [change] displays shorter time format for older entries
- [change] minor layout refinements in search-, login- and register-form
- [change] minor layout adjustments to users/view
- [change] refines answering locked button design
- [change] updates core libraries (jQuery UI 1.10.4)
- [remove] Bootstrap is no longer available in user front-end
- [remove] removes buggy 'thread with new entry' bullet indicator
- [remove] deactivates help menu

## [2.0.1] 2014-01-27

### What's new
- [fix] fixes failing test cases

## [2.0.0] 2014-01-17

### What's new

[Complete changelist](https://github.com/Schlaefer/Saito/compare/2013-11.06...2.0.0). Noteworthy changes:
- [new] #174 Add user-preference theme chooser (for configuring see `app/Config/saito_config.php`)
- [new]  #173 Remove user font-size customization
- [new] retina smiley button image
- [new] retina button for image upload delete
- [new] icon for HTML5 notification is located in `webroot/img/html5-notification-icon.png` now
- [new] Shoutbox performance improvements
- [new] `title_for_page` and `forum_name` variables available in view files
- [new] search result navigation buttons are moved into navigation bar
- [new] changes version string to semantic versioning
- [new] updates core libraries (#177 CakePHP 2.4.4, #178 jQuery 2.1, #179 Marionette 1.5+, require.js 1.9.10)
- [new] updates layout in users/index
- [fix] Shoutbox input field is two lines high in Firefox
- [fix] #172 No code toggle icon for geshi
- [fix] #171 Email notifications are not filtered by recipient === author
- [task] #175 Change GET theme selector from named param to standard query-string

Code refactoring esp. CSS class name cleanup in preparation of new default theme.

### Migration Notes

#### Theme

Theme is recompiled. Note esp. the new `<div class="body">` wrapper in `default.ctp`.

#### DB Changes

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

```
ALTER TABLE `users` DROP `user_font_size`;
ALTER TABLE `users` ADD `user_theme` VARCHAR(255)  NULL  DEFAULT NULL;
```

## 2013-11.06 ##

### What's new

- [new] show warning if JavaScript is not available
- [new] show warning if localStorage is not available
- [fix] don't timeout DOM if localStorage is not available
- [fix] layout tweaks
- [task] updates CakePHP to 2.4.3
- [task] makes auth cookie http only

### Migration notes

Theme is recompiled and uses compass 0.13.alpha.10+ instead of 0.12.

## 2013-11.05 ##

### What's new ###

- [new] show latest log entries in admin area
- [fix] help popup not showing
- [task] refactors status and language asset requests
- [task] layout tweaks

### Migration notes ###

Theme is recompiled.

## 2013-11.04 ##

### What's new ###

- [new] marks new shoutbox entries on per browser basis
- [fix] sets user online if the page is open (temp. workaround for regression)
- [fix] adds time until Stopwatch startup to Stopwatch::getWallTime
- [fix] excludes search button from sequential tab focus
- [task] CSS tweak

Code refactoring.

## 2013-11.03 ##

### What's new ###

- [new] #165 Add edit-time onto edit button
- [new] Stopwatch outputs warm-up times in debug output
- [new] improves responsive layout
- [new] mobile customization documentation in `docs/config-customizing.md`
- [fix] mobile: fixes mobile view only using default theme assets
- [task] refactors edit time from .ctp-template into .js-view

### Migration Notes ###

#### Theme ####

Theme is recompiled.

#### default.ctp ####

If you use a custom `default.ctp` file replace:

    <div id="claim"></div>
            </div> <!-- .left -->
            <div class="l-top-menu top-menu">
                <?php echo $this->element('layout/header_login'); ?>
            </div>
        </div> <!-- #top -->

with

			<div id="claim"></div>
		</div> <!-- .left -->
	</div> <!-- #top -->
    <div class="l-top-menu-wrapper">
      <div class="l-top-menu top-menu">
        <?php echo $this->element('layout/header_login'); ?>
      </div>
    </div>

## 2013-11.02 ##

### What's new ###

- [new] #166 Invalidate mobile cache manifest on `Empty Caches` button

## 2013-11.01 ##

### What's new ###

- [new] reduces minimum page width to 768px
- [new] PHP 5.5 support
- [fix] [mobile] long links overflow content area
- [task] refactors Bookmark and Solves buttons in posting form
- [task] #163 - implements Server Side Events for status messages (disabled by default)
- [task] updates to font-awesome 4, marionette 1.2.2, backbone 1.1

Code formatting and refactoring.

## 2013-10.03 ##

### What's new ###

- [new] #9 thread-starter can mark helpful answers
- [new] Shoutbox notifications if window is in background
- [new] Shoutbox JSON-API
- [fix] #162 Special chars in email notifications are html encoded
- [task] rewritten Shoutbox consuming the API
- [task] adds several new grunt commands: `grunt test`, `grunt compass:watch`, `grunt compass:compile`
- [task] adds phpcs, jasmine, jshint tests to `grunt test`
- [task] updates CakePHP to 2.4.2
- [task] code cleanup and refactoring

Notifications use the image `[Theme/]webroot/img/apple-touch-icon-precomposed.png` as icon.

### Regressions ###

- no shoutbox in mobile view

### DB Changes ###

 <span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

    ALTER TABLE  `entries` ADD  `solves` INT( 11 ) NOT NULL DEFAULT  '0';

## 2013-10.02 ##

### What's new ###

- [fix] #158 Checkboxes for email notifications not working on new entries.
- [fix] missing remaining time on submit button when editing
- [task] move frontend components to bower
- [task] use grunt for js dev-setup and release generation

## 2013-10.01 ##

### What's new ###

- [new] mobile view "[Togusa](http://en.wikipedia.org/wiki/Togusa)"
- [fix] iframe overflow on viewing entries

## 2013-09.07 ##

### What's new ###

- [new] [API] `shoutbox_enabled` attribute in bootstrap

## 2013-09.06 ##

### What's new ###

- [fix] fixes mandatory category select in search form
- [fix] [API] always logout on login attempt

Code refactoring.

## 2013-09.05 ##

### What's new ###

- [fix] fixes invalid html
- [fix] [hr]/[---] don't need a close tag anymore
- [fix] media overflow in inline opening

## 2013-09.04 ##

### What's new ###

- [fix] fixes some issues in entries/edit and entries/add

## 2013-09.03 ##

### What's new ###

- [fix] resovles thread tree sort ambiguity if entries have same time
- [fix] no upload form in entries/add
- [fix] performance improvement in entries/view

## 2013-09.02 ##

### What's new ###

- [fix] improves "double click to send twice"-protection in entry form
- [fix] show ValidityState messages in entry form

Code refactoring.

## 2013-09.01 ##

### What's new ###

- [new] JSON API for basic forum functionality (login, add, edit, logout)
- [new] [s] as shorthand for [strike] bbcode-tag
- [new] Smiley model is cached for better performance
- [new] Updates CakePHP to 2.4.1
- [fix] fixes missing whitespaces in [spoiler] text

Code refactoring and performance improvements.

There's a new JSON API which is optional at the moment. See `docs/api-v1.md` for more information.

### DB Changes

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

    INSERT INTO `settings` (`name`, `value`) VALUES ('api_enabled', '1');
    INSERT INTO `settings` (`name`, `value`) VALUES ('api_crossdomain', '');

## 2013-08.03 ##

### What's new ###

- [new] new [spoiler] bbcode tag with button
- [new] removed [u] underline bbcode button
- [new] unified bbcode buttons for image and media
- [fix] #150 Upload not working in iCab Mobile
- [task] updates CakePHP to 2.4.0

Code refactoring.

### Migration notes ###

Don't forget to update your `lib/Cake` folder.

## 2013-08.02 ##

### What's new ###

- [fix] makes youtu.be video insert protocol relative
- [task] includes require.js in main-prod.js
- [task] updates jQuery from 2.0.2 to 2.0.3
- [task] updates CakePHP to 2.4.0-RC2
- [task] updates font-awesome to 2.3.1
- [task] updates require.js to 2.1.8
- [task] updates r.js to 2.1.8
- [task] updates text.js to 2.0.10

### Migration notes ###

This version uses a Release Candidate version of CakePHP. Stay at 2013-06.05 for a stable release.

Don't forget to update your `lib/Cake` folder.

## 2013-08.01 ##

### What's new ###

- [new] improved navigation in admin settings
- [fix] user ranks in admin settings are not shown
- [fix] changing category title in admin settings doesn't empty thread cache
- [fix] can't save entry if subject is exactly max length (regression from 2013-07.01)
- [fix] shoutbox is rendered twice on page load
- [task] updates to CakePHP2.4-RC1

Significant code refactoring and minor performance improvements.

### Migration notes ###

This version uses a Release Candidate version of CakePHP. Stay at 2013-06.05 for a stable release.

Don't forget to update your `lib/Cake` folder.

## 2013-07.01b ##

### What’s New ###

- [fix] answering in admin category fails

### Migration notes ###

This version uses a beta version of CakePHP. Stay at 2013-06.05 for a stable release.

## 2013-07.01a ##

### What’s New ###

- updates to latest CakePHP 2.4 dev version
- temporary fix for <https://cakephp.lighthouseapp.com/projects/42648/tickets/3938-this-redirectthis-auth-redirecturl-broken>

### Migration notes ###

This version uses a beta version of CakePHP. Stay at 2013-06.05 for a stable release.

Don't forget to update your `lib/Cake` folder.

## 2013-07.01 ##

### What’s New ###

- [fix] #148 encoding in [code] block not working
- [task] update to CakePHP 2.4beta

Code refactoring. Performance and security improvements.

### Migration notes ###

This version uses a beta version of CakePHP.

Don't forget to update your `lib/Cake` folder.

## 2013-06.05 ##

### What’s New ###

- [new] performance improvements (settings are now cached)
- [fix] email addresses in admin user index are link instead of mailto

## 2013-06.04 ##

### What’s New ###

- [new] performance improvements on entries/mix

## 2013-06.03 ##

### What’s New ###

- [new] email addresses are obfuscated in entry output
- [new] minor performance improvements in entries/mix
- [new] all plugins in app/Plugins are now loaded automatically
- [fix] #147 [fixes #147 Middle click not working in Firefox
][gh147]
- [fix] fixes html5 validation related errors on entries/add in firefox
- [fix] corner radius on mod button not effected by theme settings
- [task] update to CakePHP 2.3.6
- [task] refactored auto-mark-as-read
- [task] consolidated twitter-bootstrap include

[gh147]: https://github.com/Schlaefer/Saito/issues/147

### Migration Notes ###

#### Misc ####

Theme is recompiled.

#### default.ctp ####

If you use a custom `default.ctp` file replace:

	    <?php
            echo $this->Html->link(
                $this->Html->image(
                    'forum_logo.png', array( 'alt' => 'Logo', 'height' => 70 )
                ),
                '/',
                array( 'id' => 'btn_header_logo', 'escape' => false ));
          ?>

with

	<?php
		echo $this->Html->link(
			$this->Html->image(
				'forum_logo.png',
				['alt' => 'Logo', 'height' => 70]
			),
			'/' . (isset($markAsRead) ? '?mar' : ''),
			$options = [
				'id'      => 'btn_header_logo',
				'escape'  => false,
			]
		);
	?>


## 2013-06.02 ##

### What's new ###

- [fix] #145 [recent entries/postings slidetabs don't update immediately if APC is used for caching][gh145]
- [fix] #146 [alignment in recent entries/postings is off][gh146]

[gh146]: https://github.com/Schlaefer/Saito/issues/146
[gh145]: https://github.com/Schlaefer/Saito/issues/145

### Migration notes ###

Theme is recompiled.

## 2013-06.01 ##

### What's new ###

- [new] performance improvements esp. rendering flat (and long) thread trees
- [new] new bbcode icon for source code
- [new] new layout for posting entries
- [new] when answering show parent entry's subject as placeholder in empty subject
- [fix] #144 [Deleting a user doesn't empty entry cache][gh144]
- [fix] #143 [users/view/ should have more descriptive title tag][gh143]
- [task] replaced blueprint with susy CSS framework
- [task] updated jQuery to 2.0.2
- [task] updated fontawesome to 3.1
- [task] code refactoring

[gh144]: https://github.com/Schlaefer/Saito/issues/144
[gh143]: https://github.com/Schlaefer/Saito/issues/143

### Migration notes ###

Theme is recompiled.

## 2013-05.04 ##

### What's new ###

- [new] link to user entry search at bottom of recent changes in /users/view
- [new] minor performance improvements using hashlinks
- [fix] #NumberChar tags are hashlinked
- minor code refactoring

## 2013-05.03 ##

### What's new ###

- [fix] #141 [Can't delete user: confirmation dialog doesn't stay on screen][gh141]
- [fix] #142 [Same origin js problem with embed.ly and twitter tweets][gh142]
- [task] minor code refactoring

[gh141]: https://github.com/Schlaefer/Saito/issues/141
[gh142]: https://github.com/Schlaefer/Saito/issues/142

## 2013-05.02 ##

### What's new ###

- [fix] #140 [page content times out (content not shown, js not initalized)][gh140]
- [task] update to CakePHP 2.3.5
- [task] minor code refactoring

[gh140]: https://github.com/Schlaefer/Saito/issues/140

## 2013-05.01 ##

### What's new ###

- [fix] fixed some performance regressions introduced in 2013-04.x

## 2013-04.08 ##

### What's new ###

- [task] update to CakePHP 2.3.4
- [task] minor code refactoring

## 2013-04.07 ##

### What's new ###

- [fix] don't scroll on mass inline openings (all, new)

## 2013-04.06 ##

### What's new ###

- [new] Shoutbox shows content on page load
- [new] rendered Shoutbox html is cached for better performance
- [new] improved click responsiveness on iOS
- [new] Retina magnifier icon in search field
- [fix] #139 [Reverse Hash Link Not Working][gh139]
- [fix] no scroll-into-view on inline opening (regression from 2013–04.01)
- [task] updated bootstrap to 2.3.1
- [task] updates jQuery to 2.0.0
- [task] CSS and HTML cleanup

[gh139]: https://github.com/Schlaefer/Saito/issues/139

### Migration Notes ###

jQuery 2.0.0 drops support for IE 8 and below.

## 2013-04.05 ##

### What's new ###

- [new] redesigned users/index
- [fix] 1970 timestamp if shoutbox has no shouts
- [fix] removed console.log() call from preview
- [task] CSS and HTML cleanup
- [task] switch to sass-twitter-bootstrap

### Migration notes ###

Recompile theme if necessary.

Instead of depending on the `bootstrap-sass` gem to be installed for compass compiling `sass-twitter-bootstrap` is included now in `app/Vendor`.


## 2013-04.04 ##

### What's new ###

- [fix] no reverse hashing of entries/view links
- [task] update from CakePHP 2.3 to 2.3.2

### Migration notes ###

Don't forget to update your `lib/Cake` folder.

## 2013-04.03 ##

### What's new ###

- [fix] multimedia button not working

## 2013-04.02 ##

### What's new ###

- [new] insert images via multimedia button
- [new] automatically convert fubar dropbox links to dl.dropbox.com (See: <https://www.dropbox.com/help/201/>) if multimedia button is used
- [new] performance improvements
- [fix] no notifications in admin area
- [task] code cleanup

### Migration notes ###

Theme is recompiled.

## 2013-04.01 ##

### What's new ###

This is a release with major code refactoring.

- [new] uploader with drag & drop panel
- [new] short tag #<entry_id> links to entry
- [new] short tag @<name|id> links to user profile
- [new] show peak memory in stopwatch debug output
- [new] new url for named profiles: users/name/<username>
- [new] sets meta description in entries/view of n/t postings to subject of that posting
- [new] required min PHP version is now 5.4
- [fix] #102 [No text format within a list][gh102]
- [fix] #19 [raw urls in code werden zu url geparst][gh19]
- [fix] improved layout and scrolling behavior in uploader on tablets
- [fix] slidetabs don't remember state if installed in server root
- [fix] no pinch & zoom on iPad
- [fix] show source code button broken in inline open
- [fix] shift+tab in entries/add textarea now working
- [fix] hard browser reload don't remember page position
- [fix] shoutbox doesn't load when first opened
- [fix] relative local links in bbcode don't work if server port is not `80`
- [task] new notification and messaging system
- [task] changed doc format from xhtml to html5
- [task] refactored all remaining js classes and files into backbone
- [task] updated backbone.js to 1.0
- [task] i18n for js frontend
- [task] more test cases
- [task] reactivated Selenium test cases which are now using the CakePHP data fixtures
- [task] cleaned up html tree structure and reduced number of html tags
- [task] added `youtube-nocookie` domain to trusted video domains (installer)

[gh19]: https://github.com/Schlaefer/Saito/issues/9
[gh102]: https://github.com/Schlaefer/Saito/issues/102

### Theme ###

#### default.ctp ####

If you use a custom `default.ctp` layout remove the following lines from it:

		<?php
		$flashMessage = $this->Session->flash();
		$emailMessage = $this->Session->flash('email');
		if ($flashMessage || $emailMessage) :
		?>
			<div id="l-flash-container">
				<?php echo $flashMessage; ?>
				<?php echo $emailMessage; ?>
			</div>
		<?php endif; ?>

Replace the line:

    if (!SaitoApp.request.isPreview) { $('#content').hide(); }

with:

    if (!SaitoApp.request.isPreview) { $('#content').css('visibility', 'hidden'); }



#### styles.scss ####

There is a new `css/src/base/_uploads.scss` file. It has to be included in in `<Theme>/webroot/css/src/styles.scss`:

    @import "base/_uploads";

## 2013-02.03 ##

- [fix] errors using Camino

## 2013-02.02 ##

- [new] #129 [Slidetab should only be sortable by dragging the slidebar tab][gh129] (allows selecting text in sidebar)
- [fix] #127 [Stop autoreload if text is entered in shoutbox texfield][gh127]
- [fix] subject is required in advanced search form
- [fix] info counter on user slidetab tab needs page reload to show/hide
- [task] refactored slidetab js code into backbone
- [task] refactored help dialog js code into backbone

[gh127]: https://github.com/Schlaefer/Saito/issues/127
[gh129]: https://github.com/Schlaefer/Saito/issues/129

## 2013-02.01 ##

- [new] Shoutbox
- [new] display PHP peek memory usage in debug output
- [fix] #123 Alt-Tags broken in Edit-Window
- [fix] #130 show raw [code] option broken
- [fix] #131 [embed] is not excluded from bbcode parsing if multimedia is set to false
- [fix] #132 Usercounter on slidetab misalligned
- [fix] js-tests can be run in production mode
- [fix] mark as read button is get link and pollutes browser history
- [fix] wobbeling word baseline in [code] blocks
- [task] #124 Update to CakePHP 2.3
- [task] #125 Update to jQuery 1.9.1
- [task] updated backbone.js (underscore, localStorage)
- [task] updated require.js (domReady, text)
- [task] unified layout center button in header and footer
- [task] refactored bookmark page js from page template into backbone
- [task] refactored scroll to top footer button js into backbone
- [task] unified layout center button in header and footer
- [task] migrated js test (yes, we have some) from qunit to jasmine
- [task] updated markItUp to patched version for jQuery 1.9+ that doesn't need jQuery.migrate
- [task] removed jQuery.migrate
- [task] basic email config info in docs/config-email.md


[Milestone issues.](https://github.com/Schlaefer/Saito/issues?milestone=10&state=closed)


### DB Changes

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

    INSERT INTO `settings` (`name`, `value`) VALUES ('shoutbox_enabled', '1');
    INSERT INTO `settings` (`name`, `value`) VALUES ('shoutbox_max_shouts', '10');

    CREATE TABLE `shouts` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `created` datetime DEFAULT NULL,
      `modified` datetime DEFAULT NULL,
      `text` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
      `user_id` int(11) NOT NULL,
      `time` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MEMORY AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

    ALTER TABLE  `users` ADD  `show_shoutbox` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `show_recententries`


## 2013-01.05 ##

- [fix] iOS issues with buttons in posting form

[Milestone issues.](https://github.com/Schlaefer/Saito/issues?milestone=9&state=closed)

## 2013-01.04 ##

- [new] Sort admin usertable by last registrations by default
- [fix] Up arrow in footer misaligned

[Milestone issues.](https://github.com/Schlaefer/Saito/issues?milestone=8&state=closed)

## 2013-01.03 ##

- [fix] surpress jQuery.migrate warnings in production mode
- [fix] thread pre icons as utf8 instead of fontawesome

## 2013-01.02 ##

- [fix] iOS scrolling performance regression from 2013-01.01
- [fix] thread close icon position iOS

## 2013-01.01 ##

- [new] Updated core libraries (CakePHP 2.3 RC2, jQuery 1.9, jQuery UI 1.9, markItUp 1.1.13, fontawesome 3.0)
- [new] SMTP option for sending emails #107
- [new] thread-icons in (theme) CSS instead of hardcoded in PHP
- [new] layout tweaks

[Complete list.](https://github.com/Schlaefer/Saito/issues?milestone=7&state=closed)

## 2012-12.03 ##

- [fix] media embedding broken

## 2012-12.02 ##

- [new] user option to collapse threads by default
- [fix] empty preview in Safari top sites

### DB Changes

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

    ALTER TABLE  `users` ADD  `user_show_thread_collapsed` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `inline_view_on_click`


## 2012-12.01 ##

### What's new ###

- [new] layout tweaks
- [new] updated core from CakePHP 2.3 beta to CakePHP 2.3 RC1
- [fix] timeout for content ready
- [fix] subject field not focused

### Update Note

The CakePHP core is updated to a release candidate version. If you don't trust it, leave this release out. If you update don't forget the `lib/Cake` folder.

## 2012-11.05 ##

### What's new ###

- [new] Updated core to CakePHP 2.3 beta
- [new] usercounter on closed usersidebar
- [new] performance improvements esp. in mix-view
- [new] simple statistics panel in admin area
- [new] layout tweaks
- [fix] wait until JS is fully initialized before showing page content


### Update Note

The CakePHP core is updated to a beta version. If you don't trust it, leave this release out. If you update don't forget the `lib/Cake` folder.

Recompile your theme if necessary.

Please note the change in default.ctp from:

    <div id="content">
      <?php echo $this->fetch('content'); ?>
    </div>

to:

    <div id="content">
      <script type="text/javascript">$('#content').hide();</script>
      <?php echo $this->fetch('content'); ?>
    </div>


## 2012-11.04 ##

### What's new ###

- [new] robuster BBCode on https installations
- [new] updated jQuery 1.8.1 to 1.8.3
- [new] admin and debug-tools use bundled js libraries instead of CDN
- [new] view user pofile by /users/view/&lt;username&gt;
- [fix] refined layout contact form
- [fix] mark confirm new password field as mandatory

## 2012-11.03 ##

### What's new ###

- [new] option to send message copy to sender in contact form
- [new] anonymous user has to provide an email address in contact form
- [new] moderators can remove an arbitrary entry and its subentries (a.k.a. delete subthread)
- [task] code cleanup & refactoring; passing test cases for php 5.4

## 2012-11.02 ##

### What's new ###

- tweaked simple search

## 2012-11.01 ##

### What's new ###

- set Sender field in email messages
- workaround for WebKit bug 101443

### Update Note

Recompile your theme if necessary.

## 2012-10.03 ##

### What's new ###

- [fix] can't access admin area

## 2012-10.02 ##

### What's new ###

Code refactoring.

## 2012-10.01 ##

### What's new ###

- [fix] escape special chars after displaying an inline answer
- [fix] changing category on root entry changes category on all entries in thread
- [fix] when merging threads change category of appended entries to target category
- [fix] Ignore Safari preview request in auto-mark-as-read

## 2012-09.07 ##

### What's new ###

- [new] #100 [delete new registered but not activated users automatically after 24 hrs][gh100]
- [fix] #51 [Collapsed thread in entries/index also collapsed in entries/view][gh51]
- [fix] incorporate server timezone in admin user index
- [fix] widen search field in users/ pages

[gh51]: https://github.com/Schlaefer/Saito/issues/51
[gh100]: https://github.com/Schlaefer/Saito/issues/100

## 2012-09.06 ##

### What's new ###

- [fix] Missing users in users/index
- [fix] Warning messages on entries/mix when thread doesn't exist

## 2012-09.05 ##

### What's new ###

- [new] #98 [Improve detailed search by adding category filter][gh98]
- [new] #99 [Nachbearbeitungszeitpunkt um Datum erweitern][gh99]
- [fix] scrolling tweaks
- [fix] layout tweaks
- [fix] SEO tweaks

[gh98]: https://github.com/Schlaefer/Saito/issues/98
[gh99]: https://github.com/Schlaefer/Saito/issues/99

## 2012-09.04 ##

### What's new ###

- [new] make sure newest CSS and JS is used by browser (no more cache emptying after update)
- [fix] inline-opening with option "always open inline" fails after inline-answer (also #94 [Error message "Posting not found"][gh94])
- [fix] throw error when trying to view non-existing thread in entries/mix

[gh94]: https://github.com/Schlaefer/Saito/issues/94

### Notes

In your `app/Config/core.php` change

	  Configure::write('Asset.timestamp', true);

to

	  Configure::write('Asset.timestamp', 'force');


## 2012-09.03

### What's new

- [fix] autoreload not working if forum is installed in webroot
- [fix] some minor notices blowing up the debug.log

## 2012-09.02

### What's new

- [new] robots.txt in webroot (thanks to kt007)
- [new] don't count (popular) search engine crawlers as guests
- [new] disables autoreload if an inline answering form was opened
- [new] set html title tag in entries/mix to subject of root posting
- [fix] search performance regression introduced in b28e8de71dbd6f8f45909caa374dfa5c7aa74c3e
- [fix] cleaned up headers and breadcrump navigation in admin interface
- [fix] tweaked inline-opening handling
- [fix] german l10n typos (thanks to Schnaks)
- [fix] automatically mark as read more robust on new sessions
- [fix] new entries are marked read on autoreload
- [task] updated jQuery to 1.8.1
- [task] javascript refactoring

## 2012-09.01

### What's new

- [new] "Empty Caches" button in admin panel
- [new] performance improvements
- [fix] layout tweaks in /users/edit/#
- [fix] refresh time stepper allows values below zero
- [fix] #89 [New entry instead of reply with deactivated JS][gh89]
- [fix] no search results for username if Entry.name is empty
- [fix] open new entries button is shown for not logged-in users
- [fix] Missing localization for entries in mod menu
- [task] Javascript refactoring

[gh89]: https://github.com/Schlaefer/Saito/issues/89

## 2012-08.07

### What's new

- [new] reduced recent user postings in s(l)idetab from 10 to 5
- [new] /users/contact/0 contacts email adress specified in admin forum settings
- [fix] use forum_disabled.ctp from current Theme folder
- [fix] #18 [remove macnemo favicon][gh18]
- [fix] #84 [Uncached threads always show the showNewThreads-Button][gh84]
- [task] #11 [forum_disabled.ctp entnemofizieren][gh11]
- [task] #83 [rename 'Alles' to 'Alle Kategorien' for category chooser
][gh83]
- [task] javascript refactoring

[gh11]: https://github.com/Schlaefer/Saito/issues/11
[gh18]: https://github.com/Schlaefer/Saito/issues/18
[gh83]: https://github.com/Schlaefer/Saito/issues/83
[gh84]: https://github.com/Schlaefer/Saito/issues/84

### Theme Changes

Contact adress in disclaimer.ctp is now `/users/contact/0` (was `/users/contact/1`).

## 2012-08.06

### What's new

- [new] change language with `lang:<lang>` url parameter on the fly
- [fix] #82 [Pin and Lock menu don't send ajax call when openend inline
][gh82]
- [fix] #81 [Performing Un-/pin and Un-/lock in mod menu removes icon][gh81]
- [fix] no editing and user's homeplace information in entries/mix
- [fix] no pin icon in entries/[view|mix]
- [task] implemented s(l)idetabs using view blocks
- [task] Entry code refactoring
- [task] Auth code cleanup

[gh81]: https://github.com/Schlaefer/Saito/issues/81
[gh82]: https://github.com/Schlaefer/Saito/issues/82


### Theme Changes

All CSS `slidebar*` classes were consolidated and renamed to `slidetab*`.


## 2012-08.05

### What's new

- [new] significant performance improvements
- [new] plot of stopwatch diff times in debug mode
- [new] #73 [append disclaimer to all page controller pages][gh73]
- [new] improved tab behavior on users/login
- [fix] #77 ["Edit Bookmark" eindeutschen][gh77]

[gh73]: https://github.com/Schlaefer/Saito/issues/73
[gh77]: https://github.com/Schlaefer/Saito/issues/77

## 2012-08.04

### What's new

- [new] improved caching behavior
- [new] update documentation
- [fix] #72 [Update to jQuery 1.8][gh72]
- [fix] l10n

[gh72]: https://github.com/Schlaefer/Saito/issues/72

## 2012-08.03

### What's new

- [new] bookmarks
- [new] tweaked caching for better performance
- [new] layout tweaks

### DB Changes

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

    CREATE TABLE `bookmarks` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `user_id` int(11) unsigned NOT NULL,
      `entry_id` int(11) unsigned NOT NULL,
      `comment` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
      `created` datetime NOT NULL,
      `modified` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `entry_id-user_id` (`entry_id`,`user_id`),
      KEY `user_id` (`user_id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `ecaches` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `created` datetime NOT NULL,
      `modified` datetime NOT NULL,
      `key` varchar(128) NOT NULL,
      `value` mediumblob NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `key` (`key`)
    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

### Theme Changes

Update in your default.ctp:

    <div id='modalLoginDialog' style='height: 0px; overflow: hidden;'>
      <?php echo $this->element('users/login_form'); ?>
    </div>

to

    <?php echo $this->element('users/login_modal'); ?>



## 2012-08.02

### What's new

- [fix] #57 [Bottom of drop down menues is hidden in the inline view of the index][gh57]
- [fix] cascading mod-button in entries/mix/#

[gh57]: https://github.com/Schlaefer/Saito/issues/57

## 2012-08.01

### What's new

- [new] Hide signature separator if signature is empty
- [new] Relative time values in recent entries sidetab
- [new] Layout tweaks
- [fix] hide mod menu in entry/view if menu is empty
- [fix] #64 [Mod menu in users/view/# empty if no mod option][gh64]
- [fix] anonymous user counter shows negative value (-1)
- [fix] Localizations

[gh64]: https://github.com/Schlaefer/Saito/issues/64

## 2012-07.05

### What's new

- [new] if subject is empty when answering use parent's subject

## 2012-07.04

### What's new

- [new] #67 [Countdown timer in editing form][gh67]
- [fix] #68 [fix admin/users/index sorting for registration date][gh68]

[gh67]: https://github.com/Schlaefer/Saito/issues/67
[gh68]: https://github.com/Schlaefer/Saito/issues/68


## 2012-07.03

### What's new

- [new] subject field in answer form is empty by default
- [new] user tab in admin panel
- [fix] add user in admin panel
- [fix] #65 [Space in thread line before posting time][gh65]
- [fix] cleaned up rss/json feed data
- [fix] #63 [Show the last 20 instead of 10 entries in users/view/#][gh63]

[gh63]: https://github.com/Schlaefer/Saito/issues/63
[gh65]: https://github.com/Schlaefer/Saito/issues/65


## 2012-07.02

### What's new

- [new] Category chooser on front page
  - Admin option to activate for all users
  - Admin option to allow users to activate in their user pref
- [new] Term of Service confirmation checkbox on user registration
  - Admin option to enable it
  - Admin option to provide a custom ToS-url
- [new] #62 Support for embedding .opus files

### DB Changes

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

    ALTER TABLE `users` CHANGE `activate_code` `activate_code` INT(7)  UNSIGNED  NOT NULL;

    ALTER TABLE `users` DROP `user_categories`;
    ALTER TABLE  `users` ADD  `user_category_override` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `flattr_allow_posting` , ADD  `user_category_active` INT( 11 ) NOT NULL DEFAULT '0' AFTER `user_category_override` , ADD  `user_category_custom` VARCHAR( 512 ) NOT NULL AFTER  `user_category_active`;
    INSERT INTO `settings` (`name`, `value`) VALUES ('category_chooser_global', '0');
    INSERT INTO `settings` (`name`, `value`) VALUES ('category_chooser_user_override', '1');

    INSERT INTO `settings` (`name`, `value`) VALUES ('tos_enabled', '0');
    INSERT INTO `settings` (`name`, `value`) VALUES ('tos_url', '');


## 2012-07.01

### What's new

- [new] Email notification about new answers to posting or thread
- [new] S(l)idetab recent entries. Shows the 10 last new entries.
- [new] refined users/edit layout (thanks to kt007)
- [new] Mods can merge threads (append thread to an entry in another thread)
- [new] admin forum setting to enable stopwatch output in production mode with url parameter `/stopwatch:true/`
- [new] refactored cache: performance improvements on entries/index/#

### DB Changes

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

    ALTER TABLE `users` DROP `show_about`;
    ALTER TABLE `users` DROP `show_donate`;

    ALTER TABLE  `users` ADD  `show_recententries` TINYINT( 1 ) UNSIGNED NOT NULL AFTER  `show_recentposts`;

    INSERT INTO `settings` (`name`, `value`) VALUES ('stopwatch_get', '0');

    CREATE TABLE `esevents` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `subject` int(11) unsigned NOT NULL,
      `event` int(11) unsigned NOT NULL,
      `created` datetime DEFAULT NULL,
      `modified` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `subject_event` (`subject`,`event`)
    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `esnotifications` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `user_id` int(11) unsigned NOT NULL,
      `esevent_id` int(11) unsigned NOT NULL,
      `esreceiver_id` int(11) unsigned NOT NULL,
      `deactivate` int(8) unsigned NOT NULL,
      `created` datetime DEFAULT NULL,
      `modified` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `userid_esreceiverid` (`user_id`,`esreceiver_id`),
      KEY `eseventid_esreceiverid_userid` (`esevent_id`,`esreceiver_id`,`user_id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


## 2012-07-08

### What's new

- [new] update to CakePHP 2.2
- [new] using Rijndael for cookie encryption
- [new] performance improvements on entries/index
- [fix] #56 Editing posting doesn't empty its tree cache.
- [fix] route /login
- [fix] german localization title tag edit buttons

### Update Note

Don't forget to update your `lib/Cake` folder.

Because of the new cookie encryption format permanently logged-in users have to login again to renew their cookie.

## 2012-06-30

### What's new

- [new] significant performance improvement (less server load) on entries/index
- [fix] Security issue when performing searches
- [fix] can't paginate on entries/index
- [fix] layout: no padding on inline-opened entries

### DB Changes

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

    ALTER TABLE `users` ADD UNIQUE INDEX (`username`);
    ALTER TABLE `categories` ADD `thread_count` INT( 11 ) NOT NULL


## 2012-06-27

- [new] /login shortcut for login-form at /users/login
- [fix] no title-tag on (Category) in /entries/view/#
- [fix] several display glitches on help popups
- [fix] #54 Posting preview contains (Categorie) in headline
- [fix] Minor layout glitches.™

## 2012-06-26


### What's new

- [new] embed.ly support
- [new] /entries/source/#id outputs raw bbcode
- [new] horizontal ruler tag [hr][/hr] with custom shortcut [---]
- [fix] no frontpage caching for logged-out users
- [fix] improved positioning of smiley popup in entries edit form
- [fix] layout tweaks

### DB Changes:

<span class="label label-warning">Note:</span> Don't forget to add your table prefix if necessary.

    INSERT INTO `settings` (`name`, `value`) VALUES ('embedly_enabled', '0');
    INSERT INTO `settings` (`name`, `value`) VALUES ('embedly_key', NULL);

### Theme Changes

Please note that Layouts/default.ctp now includes all JS and CakePHP boilerplate via layout/html_footer.ctp to simplify future updates.

## 2012-06-24

- [new] Admin option to enable moderators to block users
- [new] Admin can delete users
- [new] Admin option to store (anonymized) IPs
- [new] Admin sees user's email adress in users/view/#
- [new] More resolution independent icons
- [new] Password are stored using bcrypt (automatic migration for existing user on next login)
- [new] Support for authentication with mylittleforum 2 passwords
- [new] Notify admin when new users registers (see saito_config file) [testing notification system]
- [fix] #55 German Language files entnemofizieren
- [fix] wrong link on button in entries/view to entries/mix
- [fix] one very long word in subject breaks layout (esp. iPhone)
- [fix] empty parentheses in user/view when user ranks are deactivated
- [fix] Last entries in users/view doesn't respect user's access rights
- [fix] Search doesn't respect user's access rights
- [fix] heavily refactored styles
- [fix] Expanded german and english localization

DB Changes:

    INSERT INTO `settings` (`name`, `value`) VALUES ('block_user_ui', 1);
    INSERT INTO `settings` (`name`, `value`) VALUES ('store_ip', '0');
    INSERT INTO `settings` (`name`, `value`) VALUES ('store_ip_anonymized', '1');

    ALTER TABLE `entries` ADD `ip` VARCHAR(39)  NULL  DEFAULT NULL  AFTER `nsfw`;

## 2012-05-16

- [new] #53 Use local font files instead of Google Fonts
- [new] [upload] tag accepts `widht` and `height` attribute
- [new] changed html title-tag format from `forumtitle – pagetitle` to `pagetitle – forumtitle`
- [new] ca. server-time spend generating the site displayed in front-page footer
- [new] layout tweaks
- [fix] no Open Sans font on older OS X/Safari versions
- [fix] theoretical issue where users could change each others passwords
- [fix] flattr button now loads its resources via https if the forum itself is running with https (fixes browser error message "insecure content")
- [fix] unofficial support for font-size in user-preferences
- [fix] #52 Wrong comma and username format when viewing posting and not logged-in

## 2012-05-11

- [new] more layout tweaks and css refactoring
- [fix] #45 Replace ? Help-Icon with text.
- [fix] #46 Replace Plus Sign in front of New Entry link with borderless one
- [fix] #49 userranks_show with bogus default value after installation
- [fix] #7 Tooltip für Kategoriensichtbarkeit
- [fix] #47 No drop shadow on video embedding popup

## 2012-05-06

- [new] popup help system
- [new] several layout tweaks
- [fix] missing page-number in title on entries/index
- [fix] vertical back button in mix-view doesn't jump to thread in entries/index
- [task] reimplemented header navigation with cake2.1 view blocks

## 2012-05-04

- [new] more layout tweaks and css refactoring
- [new] more english localizations
- [new] stricter inline-answering: now on front page and in mix view only
- [fix] CakePHP MySQL fulltext index patch for Cake 2.1.2
- [fix] #43 Unterstrichen [u] funktioniert nicht
- [fix] #42 Kein Inhalt im title-Tag nach Cake 2.1 Update
- [fix] RSS feed (Cake 2 regression)

## 2012-05-02

- [new] update to CakePHP 2.1.2
- [new] many more layout tweaks
- [new] more english localization
- [new] more resolution independent icons
- [new] admin can change his own password
- [fix] contact admin broken if user is not logged-in
- [fix] shift-tab from entry textarea to subject field broken


## 2012-04-24

- Dedicated [Saito homepage](http://saito.siezi.com/)
- [new] Updated Default layout with iPad and iPhone optimizations made to macnemo theme in v2012-04-13
- [new] *Many more* layout tweaks
- [new] New close thread button (client side only)
- [new] Resolution independend icons in navigation bar
- [new] English localization (still incomplete)
- [new] resizable search field in header
- [fix] layout search field with shadow 1px off
- [fix] localized german month names in search form
- [fix] fully localized footer (disclaimer)
- [fix] On iOS Cursors doesn't jump out off subject field anymore

## 2012-04-13

- Update from Cake 1.3 to 2.0
- Layoutoptimierungen für iPad und iPhone
- Cyrus' iPad Zoom Bug ist (hoffentlich) erschlagen
- Smiliebuttons fügen ein zusätzliches Leerzeichen ein, damit viele nacheinander zusammenklicken kann
- Mods können eigene, angepinnte Beiträge nachbearbeiten
- Und der Admin hat jetzt eine Zeitzonen-Einstellungen in seinem Panel

## Then …

    [Scene]

    A beach in the south sea. A straw hat on the left.

    Sully throws the hat-door open! Sully runs out the door, Mike is following.

    They frantically passing the picture leaving it to the right.


## Once upon a Time in the East

- 2010-07-08 – going public with 1.0b1
- 2010-06-21 – eating dogfoot
- 2010-06-17 – `git init .` for Saito

## The Forgotten Founder

- 2010 – RoR was finally abandoned, but valuable lessons were learned from Batu
- 2008 – Batu the Rails version was written
