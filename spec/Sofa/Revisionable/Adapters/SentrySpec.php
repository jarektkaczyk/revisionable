<?php

namespace spec\Sofa\Revisionable\Adapters;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SentrySpec extends ObjectBehavior
{

    /**
     * @param  \Cartalyst\Sentry\Sentry $sentry
     */
    function it_provides_user($sentry)
    {
    	$this->beConstructedWith($sentry);

        $this->shouldImplement('\Sofa\Revisionable\UserProvider');
    }

    /**
     * @param  \Cartalyst\Sentry\Sentry $sentry
     * @param  \Cartalyst\Sentry\Users\UserInterface $user
     */
    function it_logs_login_field_by_default($sentry, $user)
    {
        $sentry->getUser()->shouldBeCalled()->willReturn($user);
    	$this->beConstructedWith($sentry);

    	$user->getLogin()->shouldBeCalled()->willReturn('default_login');

    	$this->getUser()->shouldReturn('default_login');
    }

    /**
     * @param  \Cartalyst\Sentry\Sentry $sentry
     * @param  \Cartalyst\Sentry\Users\UserInterface $user
     */
    function it_logs_custom_field_from_user_object_if_provided($sentry, $user)
    {
    	$user->custom_field = 'john@doe.com';
        $sentry->getUser()->shouldBeCalled()->willReturn($user);

    	$this->beConstructedWith($sentry, 'custom_field');

    	$this->getUser()->shouldReturn('john@doe.com');
    }

    /**
     * @param  \Cartalyst\Sentry\Sentry $sentry
     * @param  \Cartalyst\Sentry\Users\UserInterface $user
     */
    function it_gets_the_active_user_model($sentry, $user)
    {
        $sentry->getUser()->shouldBeCalled()->willReturn($user);

        $this->beConstructedWith($sentry);

        $this->getUserModel()->shouldReturn($user);
    }

    /**
     * @param  \Cartalyst\Sentry\Sentry $sentry
     * @param  \Cartalyst\Sentry\Users\UserInterface $user
     * @param  \Cartalyst\Sentry\Users\ProviderInterface $users
     */
    function it_gets_the_user_model_by_id($sentry, $user, $users)
    {
        $users->findByCredentials(Argument::type('array'))->shouldBeCalled()->willReturn($user);
        $sentry->getUserProvider()->shouldBeCalled()->willReturn($users);

        $this->beConstructedWith($sentry);

        $this->getUserModel('john@doe.com')->shouldReturn($user);
    }
}
