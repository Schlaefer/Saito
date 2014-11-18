<?php

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
