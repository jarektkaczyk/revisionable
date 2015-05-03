<?php

namespace spec\Sofa\Revisionable\Adapters;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SentinelSpec extends ObjectBehavior
{

    /**
     * @param  \Cartalyst\Sentinel\Sentinel $sentinel
     */
    function it_provides_user($sentinel)
    {
    	$this->beConstructedWith($sentinel);

        $this->shouldImplement('\Sofa\Revisionable\UserProvider');
    }

    /**
     * @param  \Cartalyst\Sentinel\Sentinel $sentinel
     * @param  \Cartalyst\Sentinel\Users\UserInterface $user
     */
    function it_logs_login_field_by_default($sentinel, $user)
    {
        $sentinel->getUser()->shouldBeCalled()->willReturn($user);
    	$this->beConstructedWith($sentinel);

    	$user->getLogin()->shouldBeCalled()->willReturn('default_login');

    	$this->getUser()->shouldReturn('default_login');
    }

    /**
     * @param  \Cartalyst\Sentinel\Sentinel $sentinel
     * @param  \Cartalyst\Sentinel\Users\UserInterface $user
     */
    function it_logs_custom_field_from_user_object_if_provided($sentinel, $user)
    {
    	$user->custom_field = 'john@doe.com';
        $sentinel->getUser()->shouldBeCalled()->willReturn($user);

    	$this->beConstructedWith($sentinel, 'custom_field');

    	$this->getUser()->shouldReturn('john@doe.com');
    }
}
