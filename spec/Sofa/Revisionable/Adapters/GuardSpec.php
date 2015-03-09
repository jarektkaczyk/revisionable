<?php

namespace spec\Sofa\Revisionable\Adapters;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GuardSpec extends ObjectBehavior
{

    /**
     * @param  \Illuminate\Auth\Guard $guard
     */
    function it_provides_user($guard)
    {
        $this->beConstructedWith($guard);

        $this->shouldImplement('\Sofa\Revisionable\UserProvider');
    }

    /**
     * @param  \Illuminate\Auth\Guard $guard
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     */
    function it_logs_auth_identifier_by_default($guard, $user)
    {
        $guard->user()->shouldBeCalled()->willReturn($user);
    	$this->beConstructedWith($guard);

    	$user->getAuthIdentifier()->shouldBeCalled()->willReturn('default_id');

    	$this->getUser()->shouldReturn('default_id');
    }

    /**
     * @param  \Illuminate\Auth\Guard $guard
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     */
    function it_logs_custom_field_from_user_object_if_provided($guard, $user)
    {
    	$user->custom_field = 'john@doe.com';
        $guard->user()->shouldBeCalled()->willReturn($user);

    	$this->beConstructedWith($guard, 'custom_field');

    	$this->getUser()->shouldReturn('john@doe.com');
    }
}
