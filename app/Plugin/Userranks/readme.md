# Userranks Plugin #

## What ##

Shows user rank based on number of postings in user-profile.

## Install ##

- activate plugin by including it in your `app/Config/saito_config.php`:

```php
CakePlugin::load('Userranks', ['bootstrap' => true]);
```

- set your ranks in the plugin's `Config/config.php`

## Test ##

```php
app/Console/cake test Userranks Lib/Userranks
```