<div class="search_results panel">
    <div class="panel-content">
        <?php
        if (empty($results) || ($results->count() === 0)) {
            echo $this->element(
                'generic/no-content-yet',
                [
                    'message' => __d('saito_search', 'nothingFound.l'),
                ]
            );
        } else {
            foreach ($results as $result) {
                echo $this->Posting->renderThread($result->toPosting()->withCurrentUser($CurrentUser), ['rootWrap' => true]);
            }
        }
        ?>
    </div>
</div>
