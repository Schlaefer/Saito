
# Customizing #

## Requirements ##

To start Customizing Saito you need to meet the following requirements and setup:

[Specify development requirements]

## Themes ##

The default theme *Bota* is implemented as a [CakePHP 3 theme plugin](https://book.cakephp.org/3.0/en/views/themes.html) and lives in *plugins/Bota*. The UI is implemented as [Bootstrap 4](https://getbootstrap.com/docs/4.3/getting-started/introduction/) theme.

To start your own theme I recommend using SASS and referencing and customizing the default theme.

Duplicate the *Bota* plugin folder and rename it to *YourTheme*.

Activate *YourTheme* by setting it as default theme in *config/saito_config.php*.

Replace everything *plugins/YourTheme/webroot/css/theme.scss* with:

```
@import "../../../../../plugins/Bota/webroot/css/src/theme";
```

This include Bota's *theme.scss*. Compiling it with SASS should give you the same look as the default theme. Now customize the theme:

```
/// Configure Bootstrap and Saito theme-variables before importing the theme.
$body-color: #222;
...

/// Import the default theme.
@import "../../../../../plugins/Bota/webroot/css/src/theme";

/// Additional customizations
body {...}
...
```

Theming resources:

- [Bootstrap theming](https://getbootstrap.com/docs/4.3/getting-started/theming/)
- [Boostrap variables](https://github.com/twbs/bootstrap/blob/v4.3.0/scss/_variables.scss)
- [SASS documentation](https://sass-lang.com/documentation)
- [Simple GUI crossplatform SASS processor](https://scout-app.io/)
