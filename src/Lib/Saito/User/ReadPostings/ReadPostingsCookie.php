<?php

namespace Saito\User\ReadPostings;

use App\Controller\Component\CurrentUserComponent;
use Saito\User\Cookie\Storage;

/**
 * Handles read posting by a client side cookie. Used for non logged-in
 * users.
 */
class ReadPostingsCookie extends ReadPostingsAbstract
{

    protected $cookieConfig = ['encryption' => false];

    /**
     * Max number of postings in cookie
     */
    protected $maxPostings = 240;

    protected $Cookie;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        CurrentUserComponent $CurrentUser,
        Storage $storage
    ) {
        parent::__construct($CurrentUser);
        $this->Cookie = $storage;
        $this->Cookie->setConfig($this->cookieConfig);
    }

    /**
     * {@inheritDoc}
     */
    public function set($entries)
    {
        $entries = $this->_prepareForSave($entries);
        if (empty($entries)) {
            return;
        }

        $entries = array_fill_keys($entries, 1);
        $new = $this->_get() + $entries;
        if (empty($new)) {
            return;
        }
        $this->readPostings = $new;

        $this->_gc();

        // make simple string and don't encrypt it to keep cookie small enough
        // to fit $this->_maxPostings into 4 kB
        $data = implode('.', array_keys($this->readPostings));
        $this->Cookie->write($data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete()
    {
        $this->Cookie->delete();
    }

    /**
     * limits the number of postings saved in cookie
     *
     * cookie size should not exceed 4 kB
     *
     * @return void
     */
    protected function _gc()
    {
        $overhead = count($this->readPostings) - $this->maxPostings;
        if ($overhead < 0) {
            return;
        }
        ksort($this->readPostings);
        $this->readPostings = array_slice(
            $this->readPostings,
            $overhead,
            null,
            true
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function _get()
    {
        if ($this->readPostings !== null) {
            return $this->readPostings;
        }
        $this->readPostings = $this->Cookie->read();
        if (empty($this->readPostings)
            || !preg_match('/^[0-9\.]*$/', $this->readPostings)
        ) {
            $this->readPostings = [];
        } else {
            $this->readPostings = explode('.', $this->readPostings);
            $this->readPostings = array_fill_keys($this->readPostings, 1);
        }
        return $this->readPostings;
    }
}
