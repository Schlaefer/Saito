# PHP #

## Event.Saito.Controller.initialize ##

Trigger: on CakePHP's 'Controller.initialize' event

Data:

- subject: Controller object

## Event.Saito.Model.initialize ##

Trigger: after a Model is constructed

Data:

- Model

## Event.Saito.User.afterIgnore ##

Trigger: after a user is ignored

Data:

- blockedUserId
- userId
- Model - UserIgnore 

## Event.Saito.View.beforeRender ##

Trigger: on CakePHP's 'View.beforeRender' event

Data:

- subject: View object

## Model.Saito.Posting.delete ##

Trigger: after a posting was deleted

Data:

- subject: Posting
- table: Table

## Request.Saito.View.Admin.plugins ##

Trigger: plugins for admin area

Returns: array
- 'title' title
- 'url' URL to plugin page, should be under '/admin/plugin'

## Request.Saito.View.MainMenu.navItem ##

Trigger: additional main navigation items

Data:
- 'View'

Returns: array
- 'title': URL-title special chars encoded
- 'url'

## Request.Saito.View.Posting.badges ##

Trigger: badge for posting

Data:

- posting - posting data as array

Returns: badge

Be careful, this callback is performance sensitive when rendering thread-trees or long threads in mix-view.

## Request.Saito.View.Posting.footerActions ##

Add HTML content to the footer actions when viewing a posting.

Data:

- posting - posting data as array
- View

Returns: items to be inserted in 

Be careful, this callback may be performance sensitive when rendering long threads in mix-view.

## Request.Saito.View.ThreadLine.beforeRender ##

Trigger: before threadline is rendered

Data:

- node - jBBCode node
- View

Returns: array with optional keys

- css
- style

Be **very** careful, this callback is performance sensitive when rendering thread-trees.

## Request.Saito.View.User.beforeFullProfile ##

Trigger: before user profile is rendered (users/view)

Data:

- user - user data as array
- View

Returns: Additional user profile data  as array with mandatory keys:

- 'title' 
- 'content'

## Request.Saito.View.User.edit ##

Inserts content into user edit page.

Data:

- user
- View

Returns: HTML

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


<!-- Not official/deprecated


## Mobile ##

The following callbacks are available and may be set in `custom_html_header.ctp`:

    <script>
      window.Saito.callbacks = {
        afterEntriesIndex: function() {
          …
        },
        afterEntriesMix: function() {
          …
        },
        afterAppmenu: function() {
          …
        }
      }
    </script>

-->