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

    /**
     * @param  \Illuminate\Auth\Guard $guard
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     */
    function it_gets_the_active_user_model($guard, $user)
    {
        $guard->user()->shouldBeCalled()->willReturn($user);

        $this->beConstructedWith($guard);

        $this->getUserModel()->shouldReturn($user);
    }

    /**
     * @param  \Illuminate\Auth\Guard $guard
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  \Illuminate\Contracts\Auth\UserProvider $provider
     */
    function it_gets_the_user_model_by_id($guard, $user, $provider)
    {
        $provider->retrieveById(1)->shouldBeCalled()->willReturn($user);
        $guard->getProvider()->shouldBeCalled()->willReturn($provider);

        $this->beConstructedWith($guard);

        $this->getUserModel(1)->shouldReturn($user);
    }
}
