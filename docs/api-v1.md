# API v1 #

## Objects ##

### Introduction ###

#### Basics ####

All timestamps are ISO 8601 UTC.

All text is raw with unencoded html entities unless mentioned otherwise.

#### Entries and Threads ####

Entries are grouped by `thread_id` into Threads. All entries in the same thread share the same `thread_id`. The `thread_id` is identical to the `id` of the root entry in that thread.

Entries are related to each other via the `parent_id`. The `parent_id` of root entries is `0`.

#### Mark as Read ####

Mark as read handles what a (logged in) user perceives as new entries and is controlled by a timestamp variable `last_refresh`.

Every entry with a `time < last_refresh` is considered old/read and vice versa. So to e.g. mark _everything_ read `last_refresh` should be set to _now_.

### Entry ###

    {
      "id": 423739,
      "parent_id": 0,
      "thread_id": 423739,
      "subject": "my subject",
      "is_nt": true,
      "time": "2013-07-04T19:53:14+00:00",
      "text": "",
      "html": "",
      "user_id": 1,
      "user_name": "Alice",
      "edit_name": "Bob",
      "edit_time": "2013-07-05T19:53:14+00:00"
      "category_id": 1,
      "category_name": "my category"

	  // *** additional data if user is authorized *** //
      "is_locked": false,
    }

`is_nt`
: boolean
: True if `text` is empty.

`is_locked`
: boolean
: Entry is locked and answering is not possible.

`text`
: unencoded, raw bbcode text

`html`
: encoded and parsed bbcode as html

`edit_name`
: name of editor or `null` if unedited

: `edit_time`":
: timestamp of edit or `null` if unedited

### User ###

    {
      "isLoggedIn": true|false, // @todo rename

      // *** additional data if user is authorized *** //
      "id": 1,
      "username": "username",
      "last_refresh": "2013-06-26T15:51:32+00:00",
      "threads_order": "time"
    }

`threads_order`
: values: `time`, `answer`

### Category ###

    {
      "id": 1,
      "order": 1,
      "title": "Category Title 1",
      "description": "Category Description",
      "accession": 0
    }

`order`
: Asc. integer representing sort order.

`accession`
:  `0`: all; `1`: registered users; `2`: mod/admin

### Settings ###

    {
      "edit_period": 20,
      "shoutbox_enabled": true,
      "subject_maxlength": 75
    }

`edit_period`
: period after creating an entry that editing is allowed in minutes

### Server ###

    {
      "time": "2013-07-24T07:44:36+00:00"
    }

`time`
: current server time

## Endpoints ##


### Basics ###

The base url is `api/v1`.

The API requests should be performed with `Accept: application/json` header.

Usually the API also answers to other `Accept` headers by appending `.json` to the request url. E.g. to show the latest threads in the browser use `api/v1/threads.json`.

A successful request will respond with a 2xx HTTP code, on errors the code is 4xx/5xx.

Authentication is handled by sending the standard Saito cookies (session or permanent).

### bootstrap GET ###

Global data about application and user state.

- url: `api/v1/bootstrap`
- request method: `GET`
- authorization: limited
- returns:
	- information about the current user
	- categories accessible to the current user
	- global forum settings

Response:

    {
        "categories": [
          // List of Category objects
        ],
        "settings": {
          // Settings object
        },
        "server": {
          // Server object
        }
        "user": {
          // User object
        }
    }


### threads GET ###

Index of latest threads.

- url: `api/v1/threads`
- request method: `GET`
- authorization: limited (different categories)
- query parameter:
	- `order` (optional); pinned entries are always output first
		- values:
			- `time` (default)
			- `answer`
	- `limit` (optional)
		- int; `10` is default; 0 < limit < 100
	- `offset` (optional)
		- int; default is `0`

Response

    [
        {
          "id": 423739,
          "subject": "my subject",
          "is_nt": true|false,
          "is_pinned": false,
          "time": "2013-07-04T19:53:14+00:00",
          "last_answer": "2013-07-05T10:45:33+00:00",
          "user_id": 1,
          "user_name": "Alice",
          "category_id": 1,
          "category_name": "my category"
        },
        …
    ]

`is_pinned`
: boolean
: is true if a entry is pinned by a moderator to be shown "on top"


### threads/#id GET ###

Complete thread with id `<thread_id>`

- url: `api/v1/threads/<thread_id>`
- authorization: limited (different categories)
- request method: `GET`

Response: List of Entry objects

    [
      // Entry object 1,
      …
    ]


### entries POST ###

Create a new entry.

- url: `api/v1/entries`
- request method: `POST`
- authorization: logged in

Request data for creating a new thread/new root entry:

    {
      "subject": "test",
      "text": "test",
      "category_id": 1
    }

Request data for creating an answer to an existing entry:

    {
      "subject": "test",
      "text": "test",
      "parent_id": 123
    }

`subject`
: optional: if empty parent subject is used

Response: Entry object of the newly created entry.


### entries/#id PUT ###

Updates an existing entry.

- url: `api/v1/entries/<entry_id>`
- request method: `PUT`
- authorization: logged in

Request data:

	{
		"subject": "test",
		"text": "test",
		"category_id": 2
	}

`subject`
: optional for non-root entries

`text`
: optional

`category_id`
: only for root entries

Response: Entry object of the updated entry.


### login POST ###

Marks user as logged in. Removes existing authentication cookie and sets a new
on successful login.

- url: `api/v1/login`
- request method: `POST`
- authorization: none

Request data:

    {
      "username": "your username",
      "password": "your password",
      "remember_me": true
    }

`remember_me`
: boolean
: optional
: stay logged in/set cookie

Response: User object.

### logout POST ###

Marks the user as logout out. Also removes authentication cookies from client.

- url: `api/v1/logout`
- request method: `POST`
- authorization: logged in

Request data:

	{
		"id: 1
	}

`id`
: user id

Response: User object with `"isLoggedIn": false` on success.


### markasread POST ###

Send mark as read event.

- url: `api/v1/marksasread`
- request method: `POST`
- authorization: logged in

Request data:

    {
      "id": 1
      "last_refresh": "2013-07-04T19:53:14+00:00"
    }

`id`
: user id

`last_refresh`
: optional: if empty current server time is used
: if it's older than value on the server the server value will not be changed

Response:

    {
      "id": 1
      "last_refresh": "2013-07-04T19:53:14+00:00"
    }

### shouts GET ###

Get all shouts

- url: `api/v1/shouts`
- request method: `GET`
- authorization: logged in

Response:

    [
      {
        "id": 1,
        "time": "2013-07-04T19:53:14+00:00",
        "text": "raw text",
        "html": "rendered text",
        "user_id": 1,
        "user_name": "Alice"
      },
      …
    ]

### shouts POST ###

Adds a new shout.

- url: `api/v1/shouts`
- request method: `POST`
- authorization: logged in

Request:

	{
		"text": "Shouttext"
	}

Response:

On success responses with all current shouts. Same as "shouts GET".