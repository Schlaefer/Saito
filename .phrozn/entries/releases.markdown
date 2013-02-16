# Release Notes

<i class='icon-info-sign icon-schmuck'></i>

<span class="label label-info">Info</span> [Download older versions](https://github.com/Schlaefer/Saito/tags)

## 2013-02.02 ##

- [new] #129 [Slidetab should only be sortable by dragging the slidebar tab][129] (allows selecting text in sidebar)
- [fix] #127 [Stop autoreload if text is entered in shoutbox texfield][127]
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
    
    They frantically passing the picture leaving it on the right.


## Once Upon a Time in the East

- 2010-07-08 – going public with 1.0b1
- 2010-06-21 – eating dogfoot
- 2010-06-17 – git init .

## The Forgotten Founder

- 2010 – RoR was finally abandoned, but valuable lessons were learned from Batu 
- 2008 – "Batu" the Rails version was written
