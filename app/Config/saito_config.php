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
            'title'       => 'Button 1'
            // image in img/markitup/<button>.png
            'icon'			=> 'button1',
            // code inserted into text
            'code' 				=> ':action:',
            // format replacement as image (optional)
            'type'				=> 'image',
            // replacement in output if type is image
            // image in img/markitup/<replacement>
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