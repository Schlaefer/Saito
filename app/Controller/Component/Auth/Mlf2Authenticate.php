<?php

  App::uses('FormAuthenticate', 'Controller/Component/Auth');

  /**
   * mylittleforum 2.x auth with salted sha1 passwords
   */
  class Mlf2Authenticate extends FormAuthenticate {

    /**
     * Find a user record using the standard options.
     *
     * @param string $username The username/identifier.
     * @param string $password The unhashed password.
     * @return Mixed Either false on failure, or an array of user data.
     */
    protected function _findUser($username, $password) {
      $userModel = $this->settings['userModel'];
      list($plugin, $model) = pluginSplit($userModel);
      $fields = $this->settings['fields'];

      $conditions = array(
          $model . '.' . $fields['username'] => $username,
      );
      if ( !empty($this->settings['scope']) ) {
        $conditions = array_merge($conditions, $this->settings['scope']);
      }
      $result = ClassRegistry::init($userModel)->find('first',
          array(
          'conditions' => $conditions,
          'recursive' => (int)$this->settings['recursive']
          ));
      if ( empty($result) || empty($result[$model]) ) {
        return false;
      } else {
      // mlf 2 auth
        $hash = $result[$model][$fields['password']];
        // from includes/functions.inc.php is_pw_correct() mlf 2.3
        $salted_hash = substr($hash, 0, 40);
        $salt = substr($hash, 40, 10);
        if ( sha1($password . $salt) != $salted_hash )
          return false;
      }

      unset($result[$model][$fields['password']]);
      return $result[$model];
    }

    protected function _password($password) {
      return FALSE;
    }

  }

?>