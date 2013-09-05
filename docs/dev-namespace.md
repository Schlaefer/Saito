Cache
=====

Kind 	| Namespace	| Key 			| Subkey			    | Type 	| Comment
-----	| ---------	| ----			| -------			    | ----	| -------
Cache | Saito 		| Settings 	| 						    | 	| Siehe Configuration
Cache | Saito 		| Cache 		| registerGc 	    | timestamp 	| timestamp of last registerGc
Cache | Saito 		| Cache 		| catForAccession	| array 	    | cache for categories for accession
Cache | Saito 		| Cache 		| appSettings	    | array 	    | cache for app settings
Cache 	        | Saito 	| Smilies 	| smilies_all 	| array 	| Smilies from `smilies` table
Configuration 	| Saito 	| Cache 	| Thread 	| bool 	| if true use thread cache
Configuration 	| Saito 	| useSaltForUserPasswords	| 	| bool 	| unsalted md5 mode for user passwords
Configuration 	| Saito		| markItUp	| nextCssId	| int 	| next CSS-ID for button in the markItUp-CSS
Configuration		| Saito		| markItUp 	| additionalButtons	| array 	| Additional buttons shown in the markItUpEditor
Configuration 	| Saito 	| Settings 	| 	| array 	| Array with App Settings
Configuration 	| Saito 	| Slidetabs 	| all 	| array 	| names of all installed slidetabs
Configuration 	| Saito 	| Smilies 	| smilies_all 	| array 	| Smilies from `smilies` table
Configuration 	| Saito 	| Smlies 	| smilies_all_html	| array 	| Html-formatierte Smilies
Configuration 	| Saito 	| theme 	| 	| string	| theme name; default ist "default"
Configuration 	| Saito 	| v 	| 	| string	| internal revision number
Session 	| User 	| last_refresh_tmp 	| 	| integer	| Speichert letzten Session Login für Mark as Read


Settings
========


Field Name 	| Default Value 	| Type 	| Comment
---------- 	| -------------	| ----- 	| -------
autolink 						| 1 	  | bool 	  | Try to autolink URLs in bbcode
bbcode_img 					| 1 	  | bool 	  | Multimedia in BBCode anzeigen
block_user_ui       | 0     | bool    | Allow Moderators to block users
edit_delay 					| 3 	  | int 	  | time in min. for edit without notice
edit_period 				| 20 	  | int 	  | time in min. for edit with notice
embedly_enabled     | 0     | bool    | Enable embedly support
embedly_key         |       | string  |
flattr_category 		| text  | string	| category tag used by flattr for entries. see flattr.com for available categories
flattr_enabled 			| 0 	  | bool 	  | enables flattr usage for users
flattr_language 		| de_DE | string	| language tag used by flattr for entries. see flattr.com for codes
forum_disabled 			| 0 	  | bool 	  | |
forum_disabled_text | We'll back soon.	| string	|
forum_email 				| 	| string	| forum email address (admin contact)
installed 					| FALSE	| bool 		| true if installer has run and forum is installed; set in bootstrap.php
quote_symbol 	| » 	| string	|
signature_separator 	| --- 	| string	|
smilies 	| 1 	| bool 	| Use Smilies
store_ip            | 0   | bool  | Store user IPs
store_ip_anonymized | 1   | bool  | Store IPs anonymized
subject_maxlength 	| 75 	| int 	|
text_word_maxlength | 120 	| int 	|
timezone						| UTC		| string | default time zone for showing time value
thread_depth_indent 	| 25 	| int 	| max indent level in index view
topics_per_page	| 20 	| int 	| # of topic on index page
upload_max_img_size 	| 300 	| int 	| Max. upload size in kB
upload_max_number_of_uploads 	| 10 	| int 	| Max uploads per User. 0 menas no limit
user_lock 	| 0 	| bool 	| user is not allowed to login
userranks_ranks	| 100=Rookie&#124;101=Veteran	| string	|
userranks_show 	| 1 	| bool 	| use user-ranks
video_domains_allowed 	| youtube&#124;vimeo 	| string	| separated list with allowed flash domains. '*' allows all.

**Note: escaped pipe &#124 in markdown table is actually a real pipe char.**







