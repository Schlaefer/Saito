<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Installer\Form;

use Cake\Datasource\ConnectionManager;
use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;

class UpdaterStartForm extends Form
{
    /**
     * {@inheritDoc}
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        return $schema
            ->addField('dbname', ['type' => 'string'])
            ->addField('dbpassword', ['type' => 'password']);
    }

    /**
     * {@inheritDoc}
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('dbname')
            ->notEmptyString('dbname')
            ->add('dbname', 'custom', [
                'rule' => [$this, 'validateDbName'],
            ]);

        $validator
            ->requirePresence('dbpassword')
            ->allowEmptyString('dbpassword')
            ->add('dbpassword', 'custom', [
                'rule' => [$this, 'validateDbPassword'],
            ]);

        return $validator;
    }

    /**
     * validate database-name
     *
     * @param string $data database-name
     * @return bool
     */
    public function validateDbName($data): bool
    {
        $connection = ConnectionManager::get('default');
        $dbConfig = $connection->config();

        return $data === $dbConfig['database'];
    }

    /**
     * validate database-password
     *
     * @param string $data database-password
     * @return bool
     */
    public function validateDbPassword($data): bool
    {
        $connection = ConnectionManager::get('default');
        $dbConfig = $connection->config();

        return $data === $dbConfig['password'];
    }
}
