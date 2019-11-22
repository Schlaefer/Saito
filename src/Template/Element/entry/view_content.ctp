<?php
use Cake\Core\Configure;

$signature = $signature ?? false;
$schemaMeta = [];
?>
<article itemscope itemtype="http://schema.org/Article" class="postingBody">
    <header>
        <h2 itemprop="headline name" class="postingBody-heading">
            <?php
            $subject = $this->Posting->getSubject($entry);
            $url = $this->Url->build(
                '/entries/view/' . $entry->get('id'),
                true
            );
            $schemaMeta['url'] = $url;
            // only make subject a link if it is not in entries/view
            if ($this->request->getParam('action') !== 'preview' &&
                ($this->request->is('ajax') || $this->request->getParam('action') === 'mix')
            ) {
                $subject = $this->Html->link(
                    $subject,
                    $url,
                    ['escape' => false, 'class' => 'et']
                );
            }
            echo $subject;
            ?>
        </h2>
    </header>
    <aside class="postingBody-info">
        <span class="c-category acs-<?= $entry->get('category')['accession']; ?>"
                title="<?= h($entry->get('category')['description']) ?>">
        <?= h($entry->get('category')['category']) ?>
        </span>
        –
        <span itemscope itemprop="author"
                itemtype="http://schema.org/Person">
            <span itemprop="name" class="c-username">
                <?=
                $this->User->linkToUserProfile(
                    $entry->get('user'),
                    $CurrentUser
                );
?></span>,
        </span>

        <span class="meta">
            <?php
            if ($entry->get('user')->get('user_place')) {
                echo h($entry->get('user')->get('user_place')) . ', ';
            }

            echo $this->TimeH->formatTime($entry->get('time'));
            $schemaMeta['datePublished'] = date(
                'c',
                strtotime($entry->get('time'))
            );

            $editedBy = $entry->get('edited_by');
            if (!empty($editedBy)) {
                $editDelay = $entry->get('time')->getTimestamp() +
                    ((int)Configure::read('Saito.Settings.edit_delay') * 60);
                if ($entry->get('edited')->getTimestamp() > $editDelay) {
                    echo ' – ';
                    echo __(
                        '{0} edited by {1}',
                        [
                            $this->TimeH->formatTime($entry->get('edited')),
                            $entry->get('edited_by')
                        ]
                    );
                }
                $schemaMeta['dateModified'] = date('c', strtotime($entry->get('edited')));
            }

            // SEO: removes keyword "views"
            if ($CurrentUser->isLoggedIn()) {
                echo ', ' . $this->Posting->views($entry);
            }
            $schemaMeta['interactionCount'] = "UserPageVisits:{$entry->get('views')}";

            if (Configure::read('Saito.Settings.store_ip') && $CurrentUser->permission('saito.core.posting.ip.view')) {
                echo ', IP: ' . $entry->get('ip');
            }

            echo ' <span class="posting-badges">';
            echo $this->Posting->getBadges($entry);
            echo '</span>';
            ?>
        </span>
    </aside>

    <div itemprop="articleBody text" class='postingBody-text'>
        <?= $this->Parser->parse($entry->get('text')) ?>
    </div>

    <?php if ($signature) : ?>
        <footer class="postingBody-signature">
            <div class="postingBody-signature-divider">
                <?= Configure::read('Saito.Settings.signature_separator') ?>
            </div>
            <?php
            $multimedia = ($CurrentUser->isLoggedIn()) ? !$CurrentUser->get(
                'user_signatures_images_hide'
            ) : true;
            echo $this->Parser->parse(
                $entry->get('user')->get('signature'),
                ['embed' => false, 'multimedia' => $multimedia]
            );
            ?>
        </footer>
        <?php
    endif;
    array_walk(
        $schemaMeta,
        function ($value, $attribute) {
            switch ($attribute) {
                case 'url':
                    echo "<link itemprop=\"$attribute\" href=\"$value\"/>";
                    break;
                default:
                    echo "<meta itemprop=\"$attribute\" content=\"$value\"/>";
            }
        }
    );
    ?>
</article>
