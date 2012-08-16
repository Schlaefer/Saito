<?php

  App::uses('FormAuthenticateSalted', 'BcryptAuthenticate.Controller/Component/Auth');

  /**
   * @see http://mark-story.com/posts/view/using-bcrypt-for-passwords-in-cakephp
   */
  class BcryptAuthenticate extends FormAuthenticateSalted {

    public static $hashIdentifier = '$2a$';

    /**
     * The cost factor for the hashing.
     *
     * @var integer
     */
    public static $cost = 10;

    public static function checkPassword($password, $hash) {
      $salt = substr($hash, 5 + strlen(self::$cost), 22);
      return self::_crypt($password, $salt) === $hash;
    }

    /**
     * Create a blowfish / bcrypt hash.
     * Individual salts are used to be even more secure.
     *
     * @param string $password Password.
     * @return string Hashed password.
     */
    public static function hash($password) {
      $salt = self::_generateRandomString(22);
      return self::_crypt($password, $salt);
    }

    protected static function _crypt($password, $salt) {
      return crypt($password, '$2a$' . self::$cost . '$' . $salt);
    }

  }
