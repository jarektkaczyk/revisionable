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
}
