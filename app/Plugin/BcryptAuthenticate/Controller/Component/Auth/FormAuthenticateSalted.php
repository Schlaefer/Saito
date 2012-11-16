<?php

  App::uses('FormAuthenticate', 'Controller/Component/Auth');

  /**
   * Description of FormAuthenticateSalted
   *
   * @author Schlaefer
   */
  abstract class FormAuthenticateSalted extends FormAuthenticate {

    public static function hash($password) {
			throw new RuntimeException('Unimplemented');
		}

    public static function checkPassword($password, $hash) {
			throw new RuntimeException('Unimplemented');
		}

    /**
     * Find a user record using the standard options.
     *
     * @param string $username The username/identifier.
     * @param string $password The unhashed password.
     * @return Mixed Either false on failure, or an array of user data.
     */
    protected function _findUser($username, $password) {
      $out = FALSE;

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
      } elseif ( static::checkPassword($password, $result[$model][$fields['password']])) {
        unset($result[$model][$fields['password']]);
        $out = $result[$model];
      }

      return $out;
    }

    protected function _password($password) {
      return FALSE;
    }

    protected static function _generateRandomString($max_length = NULL) {
      $string = Security::generateAuthKey();
      if ( $max_length ) {
        $string = substr($string, 0, $max_length);
      }
      return $string;
    }

  }
