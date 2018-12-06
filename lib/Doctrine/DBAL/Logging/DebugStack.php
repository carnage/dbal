<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\DBAL\Logging;

/**
 * Includes executed SQLs in a Debug Stack.
 *
 * @link   www.doctrine-project.org
 * @since  2.0
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 */
class DebugStack implements SQLLogger
{
    const SOURCE_DISABLED = 0;
    const SOURCE_ENABLED = 1;

    /**
     * Executed SQL queries.
     *
     * @var array
     */
    public $queries = [];

    /**
     * If Debug Stack is enabled (log queries) or not.
     *
     * @var boolean
     */
    public $enabled = true;

    /**
     * @var float|null
     */
    public $start = null;

    /**
     * @var integer
     */
    public $currentQuery = 0;

    private $includeSource;

    public function __construct($includeSource = self::SOURCE_DISABLED)
    {
        $this->includeSource = $includeSource;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        if (!$this->enabled) {
            return;
        }
        
        $this->start = microtime(true);
        $log = [
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
            'executionMS' => 0,
        ];

        if ($this->includeSource !== self::SOURCE_DISABLED) {
            $log['querySource'] = $this->findQuerySource();
        }

        $this->queries[++$this->currentQuery] = $log;
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        if (!$this->enabled) {
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
