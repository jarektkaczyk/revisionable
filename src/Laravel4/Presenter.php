<?php namespace Sofa\Revisionable\Laravel4;

use Sofa\Revisionable\Presenter as AbstractPresenter;
use Illuminate\Config\Repository as Config;

class Presenter extends AbstractPresenter
{
    /**
     * Create a new revision presenter.
     *
     * @param \Sofa\Revisionable\Revision|array $revision
     */
    public function __construct($revision, Config $config)
    {
        if ($revision instanceof Revision) {
            $revision = $revision->getAttributes();
        }

        $this->config = $config;

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
            $html .= $this->config->get('revisionable::config.templates.diff.start');

            $html .= str_replace(
                [':key', ':old', ':new'],
                [$key, $this->old($key), $this->new($key)],
                $this->config->get('revisionable::config.templates.diff.body')
            );

            $html .= $this->config->get('revisionable::config.templates.diff.end');
        }

        $html .= '</div>';

        return $html;
    }
}
