<?php

namespace App\Auth;

use Cake\Auth\AbstractPasswordHasher;
use Cake\Utility\Security;
use Cake\Utility\Text;

/**
 * mylittleforum 2.x salted sha1 passwords.
 */
class Mlf2PasswordHasher extends AbstractPasswordHasher
{
    public function hash($password)
    {
        // compare to includes/functions.inc.php generate_pw_hash() mlf 2.3
        $salt = self::_generateRandomString(10);
        $saltedHash = sha1($password . $salt);
        $hashWithSalt = $saltedHash . $salt;

        return $hashWithSalt;
    }

    protected static function _generateRandomString($maxLength = null)
    {
        $string = Security::hash(Text::uuid());
        if ($maxLength) {
            $string = substr($string, 0, $maxLength);
        }

        return $string;
    }

    public function check($password, $hash)
    {
        $out = false;
        // compare to includes/functions.inc.php is_pw_correct() mlf 2.3
        $saltedHash = substr($hash, 0, 40);
        $salt = substr($hash, 40, 10);
        if (sha1($password . $salt) == $saltedHash) :
            $out = true;
        endif;

        return $out;
    }
}
