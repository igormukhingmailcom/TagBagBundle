<?php

namespace spec\Setono\TagBagBundle\EventListener;

use Setono\TagBagBundle\EventListener\PopulateTagBagSubscriber;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Setono\TagBagBundle\TagBag\TagBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class PopulateTagBagSubscriberSpec extends ObjectBehavior
{
    private $sessionKey = 'session_key';

    public function let(TagBagInterface $tagBag): void
    {
        $this->beConstructedWith($tagBag, $this->sessionKey);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PopulateTagBagSubscriber::class);
    }

    public function it_listens_to_request_event(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(KernelEvents::REQUEST);
    }

    public function it_does_not_set_tag_bag_when_session_is_null(GetResponseEvent $event, Request $request, SessionInterface $session): void
    {
        $request->getSession()->willReturn(null)->shouldBeCalled();
        $event->getRequest()->willReturn($request)->shouldBeCalled();

        $session->has(Argument::any())->shouldNotBeCalled();

        $this->populate($event);
    }

    public function it_does_not_set_tag_bag_when_session_is_not_started(GetResponseEvent $event, Request $request, SessionInterface $session): void
    {
        $session->has(Argument::any())->shouldNotBeCalled();
        $session->isStarted()->willReturn(false);
        $request->getSession()->willReturn($session)->shouldBeCalled();
        $event->getRequest()->willReturn($request)->shouldBeCalled();

        $this->populate($event);
    }

    public function it_does_not_set_tag_bag_when_session_does_not_have_session(GetResponseEvent $event, Request $request, SessionInterface $session): void
    {
        $session->isStarted()->willReturn(true);
        $session->has(Argument::any())->willReturn(false)->shouldBeCalled();
        $session->get(Argument::any())->shouldNotBeCalled();

        $request->getSession()->willReturn($session);
        $event->getRequest()->willReturn($request);

        $this->populate($event);
    }

    public function it_sets_tag_bag(TagBagInterface $tagBag, GetResponseEvent $event, Request $request, SessionInterface $session): void
    {
        $arr = ['section' => ['tag1']];

        $session->isStarted()->willReturn(true);
        $session->has(Argument::any())->willReturn(true);
        $session->get(Argument::any())->willReturn($arr);

        $request->getSession()->willReturn($session);
        $event->getRequest()->willReturn($request);

        $tagBag->initialize($arr)->shouldBeCalled();

        $this->populate($event);
    }
}