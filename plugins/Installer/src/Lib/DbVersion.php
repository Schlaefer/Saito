<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Installer\Lib;

use App\Model\Table\SettingsTable;

class DbVersion
{
    /** @var SettingsTable */
    private $table;

    /**
     * Constructor
     *
     * @param SettingsTable $table settings-table
     */
    public function __construct(SettingsTable $table)
    {
        $this->table = $table;
    }

    /**
     * Get version string from DB
     *
     * @return string|null
     */
    public function get(): ?string
    {
        $dbSetting = $this->table->findByName('db_version')->first();
        if (!$dbSetting) {
            return null;
        }

        return $dbSetting->get('value');
    }

    /**
     * Set version string in DB
     *
     * @param string|null $version version-string to set
     * @return void
     */
    public function set(?string $version): void
    {
        $dbSetting = $this->table->findByName('db_version')->first();
        if (!$dbSetting) {
            $dbSetting = $this->table->newEntity();
        }
        $dbSetting->set('value', $version);
        $this->table->save($dbSetting);
    }
}
