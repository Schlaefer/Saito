# Email Configuration #

## Admin Adress ##

The first user account created when installing the forum is the admin account. You probably want to provide an email address for this account in the user settings.

## Basic Configuration ##

### Forum Address ###

You have to set an email address in the admin preferences.

This is used as:

* *receiver* for the global contact form `/users/contact/0`
* *from* when an email is not send by a specific user (e.g. registration mail, reply notifications)
* *sender* in *all* emails send by the forum

## Advanced Configuration ##

It's possible to define an [advanced configuration](cakephp-email-config) in `app/Config/email.php`.

To do so create a `$saito` configuration.

If you define a `from` field:

    class EmailConfig {

	  public $saito = array(
		'transport' => 'Mail',
		'from' => 'contact@example.com',
	  )

this `from` is used as *sender* by the forum.

[cakephp-email-config]: http://book.cakephp.org/2.0/en/core-utility-libraries/email.html#configuration