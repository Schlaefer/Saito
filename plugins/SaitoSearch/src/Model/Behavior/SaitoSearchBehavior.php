<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace SaitoSearch\Model\Behavior;

use App\Model\Table\EntriesTable;
use Cake\ORM\Behavior;
use Cake\ORM\Query;

class SaitoSearchBehavior extends Behavior
{
    /**
     * Finder for simple search ordered by time
     *
     * @param Query $query query
     * @param array $options options
     *  - `searchTerm` SaitoSearch\Lib\SimpleSearchBehavior
     *  - 'categories' array of int list of category-IDs
     * @return Query
     */
    public function findSimpleSearchByTime(Query $query, array $options): Query
    {
        $connection = $this->getTable()->getConnection();
        $q = $connection->quote($options['searchTerm']->replaceOperators());
        $query = $this->prepareFindSimpleSearch($query, $options)
            ->where([
                "MATCH (Entries.subject, Entries.text, Entries.name) AGAINST ($q IN BOOLEAN MODE)"
            ])
            ->order(['`Entries`.`time`' => 'DESC']);

        return $query;
    }

    /**
     * Finder for simple search ordered by rank
     *
     * Check for performance if queries are changed!
     *
     * @param Query $query query
     * @param array $options options
     *  - `searchTerm` SaitoSearch\Lib\SimpleSearchBehavior
     *  - 'categories' array of int list of category-IDs
     * @return Query
     */
    public function findSimpleSearchByRank(Query $query, array $options): Query
    {
        /** @var EntriesTable */
        $table = $this->getTable();
        $connection = $table->getConnection();
        // $q = $connection->quote($options['searchTerm']->replaceOperators());
        $connection->getDriver()->enableAutoQuoting(false);
        $query = $this->prepareFindSimpleSearch($query, $options)
            ->select(
                [
                    'relSubject' => 'MATCH (Entries.subject) AGAINST (:q IN BOOLEAN MODE)',
                    'relText' => 'MATCH (Entries.text) AGAINST (:q IN BOOLEAN MODE)',
                    'relName' => 'MATCH (Entries.name) AGAINST (:q IN BOOLEAN MODE)'
                ] + $table->threadLineFieldList
            )
            ->where("MATCH (`Entries`.`subject`, `Entries`.`text`, `Entries`.`name`) AGAINST (:q IN BOOLEAN MODE)")
            ->order(['(2*relSubject + relText + 4*relName)' => 'DESC', '`Entries`.`time`' => 'DESC'])
            ->bind(':q', $options['searchTerm']->replaceOperators());

        return $query;
    }

    /**
     * Shared finder code for simple-search-finders
     *
     * @param Query $query query
     * @param array $options options
     *  - `searchTerm` SaitoSearch\Lib\SimpleSearchBehavior
     *  - 'categories' array of int list of category-IDs
     * @return Query
     */
    private function prepareFindSimpleSearch(Query $query, array $options): Query
    {
        if (empty($options['categories'])) {
            throw new \RuntimeException();
        }
        if (empty($options['searchTerm'])) {
            throw new \RuntimeException();
        }

        $connection = $this->getTable()->getConnection();

        $minWordLength = $connection
            ->execute("SHOW VARIABLES LIKE 'ft_min_word_len'")
            ->fetch()[1];
        $options['searchTerm']->setMinWordLength((int)$minWordLength);

        $query
            ->where(['Entries.category_id IN' => $options['categories']])
            ->contain(['Categories', 'Users'])
            ->limit(25);

        return $query;
    }
}
