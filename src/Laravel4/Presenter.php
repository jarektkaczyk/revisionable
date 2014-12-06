<?php namespace Sofa\Revisionable\Laravel4;

use Sofa\Revisionable\Presenter as AbstractPresenter;

class Presenter extends AbstractPresenter
{
    /**
     * Create a new revision presenter.
     *
     * @param \Sofa\Revisionable\Revision|array $revision
     */
    public function __construct($revision)
    {
        if ($revision instanceof Revision) {
            $revision = $revision->getAttributes();
        }

        parent::__construct($revision);
    }

    /**
     * {@inheritdoc}
     */
    public function renderDiff()
    {
        $html = '<div>';

        foreach ($this->getDiff() as $key => $values) {
            $html .= Config::get('revisionable::templates.diff.start');

            $html .= str_replace(
                [':key', ':old', ':new'],
                [$key, $this->old($key), $this->new($key)],
                Config::get('revisionable::templates.diff.body')
            );

            $html .= Config::get('revisionable::templates.diff.end');
        }

        $html .= '</div>';

        return $html;
    }
}
