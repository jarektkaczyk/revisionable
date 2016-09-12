<?php

namespace Sofa\Revisionable\Laravel;

use Carbon\Carbon;
use Sofa\Revisionable\UserProvider;

class Listener
{
    /**
     * @param \Sofa\Revisionable\UserProvider $userProvider
     * @param \Sofa\Revisionable\Logger       $logger
     */
    public function __construct(UserProvider $userProvider)
    {
        $this->userProvider = $userProvider;
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

        $revisioned->revisions()->create([
            'table_name' => $revisioned->getTable(),
            'action' => $action,
            'user_id' => $this->userProvider->getUserId(),
            'user' => $this->userProvider->getUser(),
            'old' => json_encode($old),
            'new' => json_encode($new),
            'ip' => data_get($_SERVER, 'REMOTE_ADDR'),
            'ip_forwarded' => data_get($_SERVER, 'HTTP_X_FORWARDED_FOR'),
            'created_at' => Carbon::now(),
        ]);
    }
}
