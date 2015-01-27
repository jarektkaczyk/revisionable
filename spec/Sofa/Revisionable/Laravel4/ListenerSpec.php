<?php

namespace spec\Sofa\Revisionable\Laravel4;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ListenerSpec extends ObjectBehavior
{
    function let($userProvider)
    {
        $userProvider->beADoubleOf('\Sofa\Revisionable\Tests\Laravel4\UserProviderStub');

        $this->beConstructedWith($userProvider);
    }

    function it_implements_listener_interface()
    {
        $this->shouldImplement('\Sofa\Revisionable\Listener');
    }

    /**
     * @param  \Sofa\Revisionable\Tests\Laravel4\EloquentRevisionableStub $eloquent
     * @param  \StdClass $object
     */
    function it_handles_only_eloquent_models($eloquent, $object)
    {
        $this->shouldNotThrow('InvalidArgumentException')->during('onCreated', [$eloquent]);
        $this->shouldThrow('InvalidArgumentException')->during('onCreated', [$object]);
    }

    /**
     * @param  \Sofa\Revisionable\Tests\Laravel4\EloquentRevisionableStub $model
     * @param  \Sofa\Revisionable\Tests\Laravel4\UserProviderStub $auth
     * @param  \Sofa\Revisionable\Tests\Laravel4\LoggerStub $logger
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
        $logger->revisionLog('create', 'table', 1, [], ['foo' => 'bar'], 'user')->shouldBeCalled();

        $this->onCreated($model)->shouldReturn(null);
    }

    /**
     * @param  \Sofa\Revisionable\Tests\Laravel4\EloquentRevisionableStub $model
     */
    function it_does_not_track_changes_when_revisioning_is_disabled($model)
    {
        $model->isRevisioned()->shouldBeCalled()->willReturn(false);

        $this->onCreated($model)->shouldReturn(false);
    }

    /**
     * @param  \Sofa\Revisionable\Tests\Laravel4\EloquentRevisionableStub $model
     */
    function it_does_not_track_update_if_none_of_revisionable_attributes_were_changed($model)
    {
        $model->getDiff()->shouldBeCalled()->willReturn([]);

        $this->onUpdated($model)->shouldReturn(false);
    }

    /**
     * @param  \Sofa\Revisionable\Tests\Laravel4\EloquentRevisionableStub $model
     * @param  \Sofa\Revisionable\Tests\Laravel4\UserProviderStub $auth
     * @param  \Sofa\Revisionable\Tests\Laravel4\LoggerStub $logger
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
        $logger->revisionLog('create', 'table', 1, [], ['foo' => 'bar'], 'user')->shouldBeCalled();

        $this->onCreated($model)->shouldReturn(null);
    }
}
