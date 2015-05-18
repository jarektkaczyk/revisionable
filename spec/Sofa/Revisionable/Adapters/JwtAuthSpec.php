<?php

namespace spec\Sofa\Revisionable\Adapters;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JwtAuthSpec extends ObjectBehavior
{

    /**
     * @param  \Tymon\JWTAuth\JWTAuth $guard
     */
    function it_provides_user($guard)
    {
        $this->beConstructedWith($guard);

        $this->shouldImplement('\Sofa\Revisionable\UserProvider');
    }

    /**
     * @param  \Tymon\JWTAuth\JWTAuth $guard
     * @param  \Tymon\JWTAuth\Providers\User\EloquentUserAdapter $user
     */
    function it_logs_auth_identifier_by_default($guard, $user)
    {
        $this->beConstructedWith($guard);
        $guard->parseToken()->shouldBeCalled()->willReturn($guard);
        $guard->toUser()->shouldBeCalled()->willReturn($user);

    	$guard->getIdentifier()->shouldBeCalled()->willReturn('id');

    	$this->getUser()->shouldReturn('id');
    }

    /**
     * @param  \Tymon\JWTAuth\JWTAuth $guard
     * @param  \Tymon\JWTAuth\Providers\User\EloquentUserAdapter $user
     */
    function it_logs_custom_field_from_user_object_if_provided($guard, $user)
    {
    	$user->custom_field = 'john@doe.com';
        $guard->parseToken()->shouldBeCalled()->willReturn($guard);
        $guard->toUser()->shouldBeCalled()->willReturn($user);

    	$this->beConstructedWith($guard, 'custom_field');

    	$this->getUser()->shouldReturn('john@doe.com');
    }
}
