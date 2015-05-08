<?php namespace Sofa\Revisionable\Laravel;

use \Config;
use Sofa\Revisionable\Logger;
use Illuminate\Database\ConnectionInterface;

class DbLogger implements Logger
{
    /**
     * Custom database connection.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * Default database connection.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $defaultConnection;

    /**
     * Create a new DbLogger.
     *
     * @param \Illuminate\Database\ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->defaultConnection = $connection;
    }

    /**
     * Log data revisions in the db.
     *
     * @param  string  $type
     * @param  string  $table
     * @param  int     $id
     * @param  array   $old
     * @param  array   $new
     * @param  string  $user
     * @return void
     */
    public function revisionLog($type, $table, $id, array $old = [], array $new = [], $user = null)
    {
        $user = $this->parseUser($user);

        $connection = $this->getCurrentConnection();

        $revisionsTableName =
            // Laravel4
            Config::get('revisionable::config.table')

            // Laravel5
            ?: Config::get('sofa_revisionable.table', 'revisions');

        $connection->table($revisionsTableName)->insert([
            'type'         => substr($type, 0, 255),
            'table_name'   => substr($table, 0, 255),
            'row_id'       => substr($id, 0, 255),
            'old'          => json_encode($old),
            'new'          => json_encode($new),
            'user'         => substr($user, 0, 255),
            'ip'           => substr($this->getFromServer('REMOTE_ADDR'), 0, 255) ?: null,
            'ip_forwarded' => substr($this->getFromServer('HTTP_X_FORWARDED_FOR'), 0, 255) ?: null,
            'created_at'   => $connection->raw('CURRENT_TIMESTAMP'),
        ]);

        $this->resetConnection();
    }

    /**
     * Set custom connection for the next log.
     *
     * @param  \Illuminate\Database\ConnectionInterface $connection
     * @return static
     */
    public function on(ConnectionInterface $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Translate provided user to appropriate string.
     *
     * @param  mixed  $user
     * @return string
     */
    protected function parseUser($user)
    {
        return (is_string($user) || is_numeric($user)) ? $user : ' -- ';
    }

    /**
     * Return current connection instance to use for next log.
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    protected function getCurrentConnection()
    {
        return ($this->connection) ?: $this->defaultConnection;
    }

    /**
     * Reset custom connection.
     *
     * @return void
     */
    protected function resetConnection()
    {
        $this->connection = null;
    }

    /**
     * Get Server variable.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return string|array
     */
    protected function getFromServer($key, $default = null)
    {
        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }
}
