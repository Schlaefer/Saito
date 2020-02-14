# PHP #

## saito.core.posting.delete.after ##

Trigger: after a posting was deleted

Data:

- subject: Posting
- table: Table

## saito.core.posting.view.badges.request ##

Trigger: badge for posting

Data:

- posting - posting data as array

Returns: badge

Be careful, this callback is performance sensitive when rendering thread-trees or long threads in mix-view.

## saito.core.posting.view.footerActions.request ##

Add HTML content to the footer actions when viewing a posting.

Data:

- posting - posting data as array
- View

Returns: items to be inserted in

Be careful, this callback may be performance sensitive when rendering long threads in mix-view.

## saito.core.threadline.render.before ##

Trigger: before threadline is rendered

Data:

- posting
- view

Returns: array with optional keys

- css
- style

Be **very** careful, this callback is performance sensitive when rendering thread-trees.

## saito.core.user.activate.after ##

Trigger: after a new user activated an account

Data:

- subject: User
- table: Table

## saito.core.user.ignore.after ##

Trigger: after a user is ignored

Data:

- blockedUserId
- userId
- Model - UserIgnore


## saito.core.user.register.after ##

Trigger: after a new user registered

Data:

- subject: User
- table: Table

## saito.core.user.edit.render.request ##

Inserts content into user edit page.

Data:

- user
- View

Returns: HTML

## saito.core.user.profile.render.request ##

Trigger: before user profile is rendered (users/view)

Data:

- user - user data as array
- View

Returns: Additional user profile data  as array with mandatory keys:

- 'title'
- 'content'

## saito.plugin.admin.plugins.request ##

Trigger: Request for additional plugins to add to admin area.

Returns: array
- 'title' title
- 'url' URL to plugin page, should be under '/admin/plugin'


# JS #


## External Injection Hooks ##

```
SaitoApp.callbacks.beforeAppInit.push(function() {
	…
});

SaitoApp.callbacks.afterAppInit.push(function() {
	…
});

SaitoApp.callbacks.afterViewInit.push(function() {
	…
});
```

## Internal Events ##

### Vent.Posting.View.afterRender ###

Trigger: after a single posting view is rendered and initialized
