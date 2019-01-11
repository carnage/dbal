<?php

namespace Doctrine\DBAL\Logging;

use function microtime;

/**
 * Logs all queries along with a best guess at where in your code they originated from
 */
class QueriesWithSource implements SQLLogger
{
    /**
     * Executed SQL queries.
     *
     * @var mixed[][]
     */
    public $queries = [];

    /**
     * If Logging is enabled (log queries) or not.
     *
     * @var bool
     */
    public $enabled = true;

    /** @var float|null */
    public $start = null;

    /** @var int */
    public $currentQuery = 0;

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        if (! $this->enabled) {
            return;
        }
        
        $this->start = microtime(true);
        $log = [
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
            'executionMS' => 0,
            'querySource' => $this->findQuerySource(),
        ];

        $this->queries[++$this->currentQuery] = $log;
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        if (! $this->enabled) {
            return;
        }

        $this->queries[$this->currentQuery]['executionMS'] = microtime(true) - $this->start;
    }

    private function findQuerySource()
    {
        foreach (debug_backtrace() as $row) {
            if (stripos($row['file'], 'vendor') === false) {
                return $row;
            }
        }
        return ['file' => '*unknown*', 'line' => 0];
    }
}
