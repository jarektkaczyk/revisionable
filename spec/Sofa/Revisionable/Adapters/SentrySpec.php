<?php

namespace spec\Sofa\Revisionable\Adapters;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SentrySpec extends ObjectBehavior
{
    function let($sentry)
    {
        $sentry->beADoubleOf('\Cartalyst\Sentry\Sentry');
 
        $this->beConstructedWith($sentry);
    }
    
    function it_provides_user()
    {
        $this->shouldImplement('\Sofa\Revisionable\UserProvider');
    }
}
