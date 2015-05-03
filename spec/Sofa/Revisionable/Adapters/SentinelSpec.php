<?php

namespace spec\Sofa\Revisionable\Adapters;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SentinelSpec extends ObjectBehavior
{
    function let($sentinel)
    {
		$sentinel->beADoubleOf('\Cartalyst\Sentinel\Sentinel');

        $this->beConstructedWith($sentinel);
    }

    function it_provides_user()
    {
        $this->shouldImplement('\Sofa\Revisionable\UserProvider');
    }
}
