<?php
    use Cake\Core\Configure;
?>
{
    "categories": [
    <?php
        $out = [];
    foreach ($categories as $category) {
        $out[] = json_encode(
            [
                'id' => (int)$category['id'],
                'order' => (int)$category['category_order'],
                'title' => $category['category'],
                'description' => (string)$category['description'],
                'accession' => (int)$category['accession']
            ]
        );
    }
        echo implode(",\n", $out);
    ?>
    ],
    "settings": <?= json_encode(
        [
            'edit_period' => (int)Configure::read('Saito.Settings.edit_period'),
            'subject_maxlength' => (int)Configure::read('Saito.Settings.subject_maxlength')
        ]
    ) ?>,
    "server": {
        "time": "<?= date('c', time()); ?>"
    },
    "user": <?= $this->element('Api.user') ?>
}
