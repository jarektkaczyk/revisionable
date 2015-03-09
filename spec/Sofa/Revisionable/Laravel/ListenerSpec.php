<?php

namespace spec\Sofa\Revisionable\Laravel;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ListenerSpec extends ObjectBehavior
{
    function let($userProvider)
    {
        $userProvider->beADoubleOf('\Sofa\Revisionable\Tests\Laravel\UserProviderStub');

        $this->beConstructedWith($userProvider);
    }

    function it_implements_listener_interface()
    {
        $this->shouldImplement('\Sofa\Revisionable\Listener');
    }

    /**
     * @param  \Sofa\Revisionable\Tests\Laravel\EloquentRevisionableStub $eloquent
     * @param  \StdClass $object
     */
    function it_handles_only_revisionable($eloquent, $object)
    {
        $this->shouldNotThrow('PhpSpec\Exception\Example\ErrorException')->during('onCreated', [$eloquent]);
        $this->shouldThrow('PhpSpec\Exception\Example\ErrorException')->during('onCreated', [$object]);
    }

    /**
     * @param  \Sofa\Revisionable\Tests\Laravel\EloquentRevisionableStub $model
     * @param  \Sofa\Revisionable\Tests\Laravel\UserProviderStub $auth
     * @param  \Sofa\Revisionable\Tests\Laravel\LoggerStub $logger
     */
    function it_tracks_changes_when_revisioning_is_enabled($model, $auth, $logger)
    {
        $auth->getUser()->shouldBeCalled()->willReturn('user');
        $this->beConstructedWith($auth);

        $model->isRevisioned()->shouldBeCalled()->willReturn(true);
        $model->getTable()->shouldBeCalled()->willReturn('table');
        $model->getKey()->shouldBeCalled()->willReturn(1);
        $model->getNewAttributes()->shouldBeCalled()->willReturn(['foo' => 'bar']);
        $model->getRevisionableLogger()->shouldBeCalled()->willReturn($logger);
        $model->getRevisionableConnection()->shouldBeCalled()->willReturn('connection');

        $logger->on('connection')->shouldBeCalled()->willReturn($logger);
        $logger->revisionLog('created', 'table', 1, [], ['foo' => 'bar'], 'user')->shouldBeCalled();

        $this->onCreated($model)->shouldReturn(null);
    }

    /**
     * @param  \Sofa\Revisionable\Tests\Laravel\EloquentRevisionableStub $model
     */
    function it_does_not_track_changes_when_revisioning_is_disabled($model)
    {
        $model->isRevisioned()->shouldBeCalled()->willReturn(false);

        $this->onCreated($model)->shouldReturn(null);
    }

    /**
     * @param  \Sofa\Revisionable\Tests\Laravel\EloquentRevisionableStub $model
     */
    function it_does_not_track_update_if_none_of_revisionable_attributes_were_changed($model)
    {
        $model->getDiff()->shouldBeCalled()->willReturn([]);

        $this->onUpdated($model)->shouldReturn(null);
    }

    /**
     * @param  \Sofa\Revisionable\Tests\Laravel\EloquentRevisionableStub $model
     * @param  \Sofa\Revisionable\Tests\Laravel\UserProviderStub $auth
     * @param  \Sofa\Revisionable\Tests\Laravel\LoggerStub $logger
     */
    function it_uses_model_connection_if_set($model, $auth, $logger)
    {
        $auth->getUser()->shouldBeCalled()->willReturn('user');
        $this->beConstructedWith($auth);

        $model->isRevisioned()->shouldBeCalled()->willReturn(true);
        $model->getTable()->shouldBeCalled()->willReturn('table');
        $model->getKey()->shouldBeCalled()->willReturn(1);
        $model->getNewAttributes()->shouldBeCalled()->willReturn(['foo' => 'bar']);
        $model->getRevisionableLogger()->shouldBeCalled()->willReturn($logger);
        $model->getRevisionableConnection()->shouldBeCalled()->willReturn('custom_connection');

        $logger->on('custom_connection')->shouldBeCalled()->willReturn($logger);
        $logger->revisionLog('created', 'table', 1, [], ['foo' => 'bar'], 'user')->shouldBeCalled();

        $this->onCreated($model)->shouldReturn(null);
    }
}
