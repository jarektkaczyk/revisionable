<?php

namespace Sofa\Revisionable\Laravel;

use Sofa\Revisionable\Logger;
use Sofa\Revisionable\UserProvider;

class Listener
{
    /** @var \Sofa\Revisionable\UserProvider */
    protected $userProvider;

    /** @var \Sofa\Revisionable\Logger */
    protected $logger;

    /**
     * @param \Sofa\Revisionable\UserProvider $userProvider
     * @param \Sofa\Revisionable\Logger       $logger
     */
    public function __construct(UserProvider $userProvider, Logger $logger)
    {
        $this->userProvider = $userProvider;
        $this->logger = $logger;
    }

    /**
     * Handle created event.
     *
     * @param \Illuminate\Database\Eloquent\Model $revisioned
     */
    public function created($revisioned)
    {
        $this->log('created', $revisioned);
    }

    /**
     * Handle updated event.
     *
     * @param \Illuminate\Database\Eloquent\Model $revisioned
     */
    public function updated($revisioned)
    {
        if (count($revisioned->getDiff())) {
            $this->log('updated', $revisioned);
        }
    }

    /**
     * Handle deleted event.
     *
     * @param \Illuminate\Database\Eloquent\Model $revisioned
     */
    public function deleted($revisioned)
    {
        $this->log('deleted', $revisioned);
    }

    /**
     * Handle restored event.
     *
     * @param \Illuminate\Database\Eloquent\Model $revisioned
     */
    public function restored($revisioned)
    {
        $this->log('restored', $revisioned);
    }

    /**
     * Log the revision.
     *
     * @param string $action
     * @param  \Illuminate\Database\Eloquent\Model
     */
    protected function log($action, $revisioned)
    {
        if (!in_array(Revisionable::class, class_uses_recursive($revisioned))) {
            throw new RuntimeException(sprintf(
                'Class [%s] must use Revisionable trait in order to track revisions',
                get_class($revisioned)
            ));
        }

        $table = $revisioned->getTable();
        $id = $revisioned->getKey();
        $user = $this->userProvider->getUser();
        $old = $new = [];

        switch ($action) {
            case 'created':
                $new = $revisioned->getNewAttributes();
                break;
            case 'deleted':
                $old = $revisioned->getOldAttributes();
                break;
            case 'updated':
                $old = $revisioned->getOldAttributes();
                $new = $revisioned->getNewAttributes();
                break;
        }

        if ($connection = $revisioned->getRevisionableConnection()) {
            $this->logger->on($connection);
        }

        $this->logger->revisionLog($action, $table, $id, $old, $new, $user);
    }
}
