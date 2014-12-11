<?php

namespace spec\Sofa\Revisionable\Adapters;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IlluminateAuthSpec extends ObjectBehavior
{
    function let($guard)
    {
        $guard->beADoubleOf('\Illuminate\Auth\Guard');
        
        $this->beConstructedWith($guard);
    }

    function it_provides_user()
    {
        $this->shouldImplement('Sofa\Revisionable\UserProvider');
    }
}
