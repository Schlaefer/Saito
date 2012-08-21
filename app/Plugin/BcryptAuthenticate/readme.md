Simple Bcrypt Authenticator for Cake 2

Setup Auth configuration in controller:

		$this->Auth->authorize = array(
				'BcryptAuthenticate.Bcrypt',
		);

For validation in model if needed:

		App::uses('BcryptAuthenticate', 'BcryptAuthenticate.Controller/Component/Auth');

		class User extends AppModel {

			…

			protected function _checkPassword($password, $hash) {
				return BcryptAuthenticate::checkPassword($password, $hash);
			}

			protected function _hashPassword($password) {
				return BcryptAuthenticate::hash($password);
			}

		}

Don't forget to activate the plugin in your `bootstrap.php` if necessary.