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

    	$user->getUserLogin()->shouldBeCalled()->willReturn('default_login');

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

    /**
     * @param  \Cartalyst\Sentinel\Sentinel $sentinel
     * @param  \Cartalyst\Sentinel\Users\UserInterface $user
     */
    function it_gets_the_active_user_model($sentinel, $user)
    {
        $sentinel->getUser()->shouldBeCalled()->willReturn($user);

        $this->beConstructedWith($sentinel);

        $this->getUserModel()->shouldReturn($user);
    }

    /**
     * @param  \Cartalyst\Sentinel\Sentinel $sentinel
     * @param  \Cartalyst\Sentinel\Users\UserInterface $user
     * @param  \Cartalyst\Sentinel\Users\UserRepositoryInterface $users
     */
    function it_gets_the_user_model_by_id($sentinel, $user, $users)
    {
        $users->findByCredentials(Argument::type('array'))->shouldBeCalled()->willReturn($user);
        $sentinel->getUserRepository()->shouldBeCalled()->willReturn($users);

        $this->beConstructedWith($sentinel);

        $this->getUserModel('john@doe.com')->shouldReturn($user);
    }
}
