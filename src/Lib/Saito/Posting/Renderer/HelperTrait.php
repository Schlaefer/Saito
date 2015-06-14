<?php

namespace Saito\Posting\Renderer;

use Saito\Event\SaitoEventManager;
use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Posting\Posting;
use Saito\Posting\PostingInterface;

/**
 * Helper methods for rendering postings
 */
trait HelperTrait
{

    /**
     * Get badges
     *
     * @param PostingInterface $entry posting
     * @return string
     */
    public function getBadges(PostingInterface $entry)
    {
        $out = '';
        if ($entry->isPinned()) {
            $out .= '<i class="fa fa-thumb-tack" title="' . __('fixed') . '"></i> ';
        }
        // anchor for inserting solve-icon via FE-JS
        $out .= '<span class="solves ' . $entry->get('id') . '">';
        if ($entry->get('solves')) {
            $out .= $this->solvedBadge();
        }
        $out .= '</span>';

        if (!isset($this->_SEM)) {
            $this->_SEM = SaitoEventManager::getInstance();
        }
        $additionalBadges = $this->_SEM->dispatch(
            'Request.Saito.View.Posting.badges',
            ['posting' => $entry->toArray()]
        );
        if ($additionalBadges) {
            $out .= implode('', $additionalBadges);
        }

        return $out;
    }

    /**
     * Get solved badge
     *
     * @return string
     */
    public function solvedBadge()
    {
        return '<i class="fa fa-badge-solves solves-isSolved" title="' .
        __('Helpful entry') . '"></i>';
    }

    /**
     * This function may be called serveral hundred times on the front page.
     * Don't make ist slow, benchmark!
     *
     * @param BasicPostingInterface $posting posting
     * @return string
     */
    public function getSubject(BasicPostingInterface $posting)
    {
        return \h($posting->get('subject')) . ($posting->isNt() ? ' n/t' : '');
    }
}
