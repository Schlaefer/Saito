# Email Configuration #

## Basic Configuration ##

### Main Address ###

You have to set a main email address in the admin preferences.

### Optional Addresses ###

The main address can be overwritten by the:

- contact address: *recipient* address for the global contact form
- register address: *from* address for the register confirmation mail
- system address: *from* and *sender* for system generated messages (e.g. notifications)

## Advanced Configuration ##

It's possible to define an [advanced configuration][cakephp-email-config]. To do so create a `$saito` configuration.

If you define a `from` field:

    class EmailConfig {

	  public $saito = array(
		'transport' => 'Mail',
		'from' => 'contact@example.com',
	  )

this `from` is used as *sender* by the forum.

[cakephp-email-config]: http://book.cakephp.org/2.0/en/core-utility-libraries/email.html#configuration