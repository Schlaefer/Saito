<?php

  App::uses('FormAuthenticateSalted', 'Lib/Controller/Component/Auth');

  /**
   * mylittleforum 2.x auth with salted sha1 passwords
   */
  class Mlf2Authenticate extends FormAuthenticateSalted {

    public static function hash($password) {
      // compare to includes/functions.inc.php generate_pw_hash() mlf 2.3
      $salt = self::_generateRandomString(10);
      $salted_hash = sha1($password.$salt);
      $hash_with_salt = $salted_hash.$salt;
      return $hash_with_salt;
    }

    public static function checkPassword($password, $hash) {
      $out = FALSE;
      // compare to includes/functions.inc.php is_pw_correct() mlf 2.3
      $salted_hash = substr($hash, 0, 40);
      $salt = substr($hash, 40, 10);
      if ( sha1($password . $salt) == $salted_hash ) :
        $out = TRUE;
      endif;
      return $out;
    }


  }

?>