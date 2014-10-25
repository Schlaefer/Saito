# "Not Safe For Work"-Bagde #

## Install ##


Add new database-fields:

```mysql
ALTER TABLE `entries` ADD `nsfw` TINYINT(1)  NULL  DEFAULT NULL;
```

Empty the cache in the admin panel to register the DB-changes.

Add to `saito_config.php`:

```php
CakePlugin::load('NsfwBadge', ['bootstrap' => true]);
```

## Uninstall ##

Remove database-fields:

```mysql
ALTER TABLE `entries` DROP `nsfw`;
```

Empty the cache in the admin panel to register the DB-changes.