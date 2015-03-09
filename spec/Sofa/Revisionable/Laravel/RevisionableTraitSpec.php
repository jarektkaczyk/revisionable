<?php

namespace spec\Sofa\Revisionable\Laravel;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RevisionableTraitSpec extends ObjectBehavior
{
    function let()
    {
        $this->beAnInstanceOf('\Sofa\Revisionable\Tests\Laravel\RevisionableTraitStub');
    }

    function it_implements_revisionable_interface()
    {
        $this->shouldImplement('\Sofa\Revisionable\Revisionable');
    }

    function it_registers_created_updated_deleted_restored_events_by_default()
    {
        $this->getRevisionableEvents()->shouldReturn($this->defaultEvents);
    }

    function it_registers_default_listeners()
    {
        foreach ($this->defaultEvents as $event) {
            $this->{'register'.$event.'Listener'}()->shouldReturn(null);
        }

        $this->registerListeners();
    }

    function it_picks_all_attributes_for_revision_if_no_specified()
    {
        $this->getRevisionableItems($this->attributes)->shouldReturn($this->attributes);
    }

    function it_picks_only_specified_attributes_for_revision()
    {
        $this->setRevisionable(['foo', 'baz']);

        $this->getRevisionableItems($this->attributes)->shouldReturn(['foo' => 'foo_old', 'baz' => 'baz_new']);
    }

    function it_does_not_revision_timestamps_by_default()
    {
        $this->getNonRevisionable()->shouldReturn(['created_at', 'updated_at', 'deleted_at']);
    }

    function it_shows_diff_for_updated_attributes()
    {
        $this->getDiff()->shouldReturn(['bar' => 'bar_new', 'baz' => 'baz_new']);
    }
}

/***********************
    _______________     |
   |_|_____|_____|_|    |
  / //  S O|F T  \\ \   |
 ( )/_____o|n_____\( )  |
 |_|____S_O_F_A____|_|  |
  W                 W   |
                        |
************************/