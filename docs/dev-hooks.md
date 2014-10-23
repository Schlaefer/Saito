# PHP #

## Event.Saito.Controller.initialize ##

When: on CakePHP's 'Controller.initialize' event

Data:

- subject: Controller object

## Event.Saito.Model.afterConstruct ##

When: after a Model class is constructed

Data:

- Model

## Event.Saito.User.afterIgnore ##

When: after a user is ignored

Data:

- blockedUserId
- userId
- Model - UserIgnore 

## Request.Saito.View.Posting.badges ##

When: badge for posting

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

When: before threadline is rendered

Data:

- node - jBBCode node
- View

Returns: array with optional keys

- css
- style

Be **very** careful, this callback is performance sensitive when rendering thread-trees.

## Request.Saito.View.User.beforeFullProfile ##

When: before user profile is rendered (users/view)

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

When: after a single posting view is rendered and initialized



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