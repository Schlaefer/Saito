<?php

	namespace App\Model\Entity;

	use Cake\ORM\Entity;
	use Cake\ORM\TableRegistry;
	use Saito\User\SaitoUser;

	class User extends Entity {

        protected $cache = [];

        public function numberOfPostings()
        {
            if (isset($this->cache['numberOfPostings'])) {
                return $this->cache['numberOfPostings'];
            }
            $Entries = TableRegistry::get('Entries');
            /*
             * @todo 3.0 use counter cache instead of $this->cache
             *
              # @mlf change after mlf is gone, we only use `entry_count` then
              $count = $this->data['User']['entry_count'];
              if ( $count == 0 )
             */
            {
                $this->cache['numberOfPostings'] = $Entries->find()
                    ->where(['user_id' => $this->get('id')])
                    ->count();
            }
            return $this->cache['numberOfPostings'];
        }

        public function getRole() {
            // @todo 3.0 better implementation
            $user = new SaitoUser($this);
            return $user->getRole();
        }

        public function isLocked() {
            return (bool)$this->get('user_lock');
        }

		protected function _getUserColorActualPosting($value) {
			return $this->_emptyHexColor($value);
		}

		protected function _getUserColorNewPostings($value) {
			return $this->_emptyHexColor($value);
		}

		protected function _getUserColorOldPostings($value) {
			return $this->_emptyHexColor($value);
		}

		protected function _emptyHexColor($value) {
			if (empty($value)) {
				$value = '#';
			}
			return $value;
		}

	}
