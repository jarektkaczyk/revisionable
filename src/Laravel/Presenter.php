<?php namespace Sofa\Revisionable\Laravel;

use Sofa\Revisionable\Presenter as AbstractPresenter;
use Illuminate\Config\Repository as Config;

class Presenter extends AbstractPresenter
{
    /**
     * Application config repository.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Templates.
     *
     * @var array
     */
    protected $templates = [];

    /**
     * Create a new revision presenter.
     *
     * @param \Sofa\Revisionable\Revision|array $revision
     * @param array $templates
     */
    public function __construct($revision, array $templates)
    {
        if ($revision instanceof Revision) {
            $revision = $revision->getAttributes();
        }

        $this->templates = $templates;

        parent::__construct($revision);
    }

    /**
     * Render diff in human readable manner as defined in the config.
     *
     * @return string
     */
    public function renderDiff()
    {
        $html = '<div>';

        foreach ($this->getDiff() as $key => $values) {
            $html .= array_get($this->templates, 'diff.start');

            $html .= str_replace(
                [':key', ':old', ':new'],
                [$key, $this->old($key), $this->new($key)],
                array_get($this->templates, 'diff.body')
            );

            $html .= array_get($this->templates, 'diff.end');
        }

        $html .= '</div>';

        return $html;
    }
}
