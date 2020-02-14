<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Contact;

interface ContactInterface
{
    /**
     * get title/(user-)name of the contact
     *
     * @return string
     */
    public function getName();

    /**
     * get address indentifier for the contact
     *
     * @return string
     */
    public function getAddress();

    /**
     * get address in cake format [<address> => <name>]
     *
     * @return array
     */
    public function toCake();
}
