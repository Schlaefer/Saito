<?php

  /**
   * Saito Enduser Configuration
   */
  
  /**
   * Changing default language
   *
   * Use ISO 639-2 Code http://www.loc.gov/standards/iso639-2/php/code_list.php
   * So german would be: deu
   */
  Configure::write('Config.language', 'eng');

  /**
   * Set the theme
   */
  Configure::write('Saito.theme', 'Default');

  /**
   * Add additional buttons to editor
   */
  /*
  Configure::write(
      'Saito.markItUp.additionalButtons',
      array(
        'Button1' => array(
            // image in img/markitup/<button>.png
            'button'			=> 'button1',
            // code inserted into text
            'code' 				=> ':action:',
            // format replacement as image
            'type'				=> 'image',
            // replacement in output
            'replacement' => 'resultofbutton1.png'
          ),
  * 			// …
        )
      );
  *
  */

  /**
   * Users to notify via email if a new users registers  
   */
  /*
  Configure::write('Saito.Notification.userActivatedAdminNoticeToUserWithID',
    // array with user IDs: array(1, 5) notfies user 1 (usually the admin) and user 5
    array(1)
   );
   */

?>