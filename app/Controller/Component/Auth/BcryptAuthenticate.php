<?php

  App::uses('FormAuthenticate', 'Controller/Component/Auth');

  /**
   * @see http://mark-story.com/posts/view/using-bcrypt-for-passwords-in-cakephp
   */
  class BcryptAuthenticate extends FormAuthenticate {

    /**
     * The cost factor for the hashing.
     *
     * @var integer
     */
    public static $cost = 10;

    /**
     * Password method used for logging in.
     *
     * @param string $password Password.
     * @return string Hashed password.
     */
    protected function _password($password) {
      return self::hash($password);
    }

    /**
     * Create a blowfish / bcrypt hash.
     * Individual salts are used to be even more secure.
     *
     * @param string $password Password.
     * @return string Hashed password.
     */
    public static function hash($password) {
      $salt = substr(Configure::read('Security.salt'), 0, 22);
      return crypt($password, '$2a$' . self::$cost . '$' . $salt);
    }

  }

?>