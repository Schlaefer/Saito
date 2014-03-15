# Update existing installation #

1. Download new version from [Saito's homepage][saito-homepage]
2. If you use a theme folder add it to `app/View/Themed`
3. Add your configuration files to `app/Config`:
    - `core.php`
    - `database.php`
    - `saito_config.php`
    - `installed.txt`
4. Update your database as described in the release notes.
5. Move everything in `app/` **except** `app/webroot` onto the server
6. Move `app/webroot` onto the server **except** `app/webroot/useruploads`
7. If CakePHP update is mentioned in the release notes move `lib/` onto the server

[saito-homepage]: http://saito.siezi.com/
