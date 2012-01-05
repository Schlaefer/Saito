<?php

/**
 * @package saito_entry
 */
class SaitoEntry extends Object {

	/**
	 * Checks if answering an entry is allowed
	 *
	 * @param array $entry
	 * @return boolean
	 */
	public function isAnsweringForbidden($entry = NULL) {
		$isAnsweringForbidden = true;

		if (!isset($entry['Entry']['locked'])) return true;

		$locked = $entry['Entry']['locked'];
		if ($locked == 0) {
			$isAnsweringForbidden = false;
		} else {
			$isAnsweringForbidden = 'locked';
		}

		return $isAnsweringForbidden;
	}

	/**
	 * Checks if someone is allowed to edit an entry
	 *
	 * @param <type> $entry
	 * @param <type> $user
	 * @param <type> $options
	 * @return boolean
	 */
	public function isEditingForbidden($entry, $user, $options = array()) {
			$defaults =  array('session' => false, 'user_type' => false);
			$options = array_merge($defaults, $options);
			extract($options);

			if ($user_type) $user['user_type'] = $user_type;

			$verboten = true;

			// Mod and Admin …
			# @td mods don't edit admin posts
			if ( $user['user_type'] == 'mod' || $user['user_type'] == 'admin' ) {
				if (
						$user['id'] == $entry['Entry']['user_id']
						&& (time() > strtotime($entry['Entry']['time'])+( Configure::read('Saito.Settings.edit_period') * 60 ))
					  && $user['user_type'] == 'mod'
					) { # mods dont mod themselfs
						$verboten = 'time';
					}
					else {
						# give mods/admins message that they edit an other user' posting
						# @td refactor out of the function (?)
						if ($session && $user['id'] != $entry['Entry']['user_id']) {
							# @ td build into action method when mod panel is done
							$session->setFlash(__('notice_you_are_editing_as_mod', true), 'flash/warning');
						}
						$verboten = false;
					}

			// Normal user and anonymous
			} else {
				// check if it's users own posting @td put admin and mods here;
				if ($user['id'] != $entry['Entry']['user_id']) {
					$verboten = 'user';
				}
				// check if time for editint ran out
				elseif (time() > strtotime($entry['Entry']['time'])+( Configure::read('Saito.Settings.edit_period') * 60 )) {
					$verboten = 'time';
				// entry is locked by admin or mod
				} elseif ($entry['Entry']['locked'] != 0) {
					$verboten = 'locked';
				} else {
					$verboten = false;
				}
			}

			return $verboten;
	}

	public function isDeletingForbidden($entry, $user, $options = array()) {
		if(!isset($entry)) {
			trigger_error(sprintf(__('Could not find entry to delete.', true), $name), E_USER_WARNING);
		}
		$forbidden = true;

	} // end isDeletingForbidden

	/**
	 * Decides if an $entry is new to/unseen by a $user
	 *
	 * @param type $entry
	 * @param type $user
	 * @return boolean
	 */
	public function isNewEntry($entry, $user) {
		$isNewEntry = FALSE;
		if ( strtotime($user['last_refresh']) < strtotime($entry['Entry']['time']) ):
			$isNewEntry = TRUE;
		endif;
		return $isNewEntry;
	}

	public function isNt($entry) {
		return empty($entry['Entry']['text']);
	}

	public function hasNewEntries($entry, $user) {
		if ( $entry['Entry']['pid'] != 0 ):
			throw new InvalidArgumentException("Entry is no thread-root, pid != 0");
		endif;

		return strtotime($user['last_refresh']) < strtotime($entry['Entry']['last_answer']);
	}
}
?>