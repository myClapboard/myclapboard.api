<?php

/**
 * This file belongs to myClapboard.
 * The source code of application includes a LICENSE file
 * with all information about license.
 *
 * @author benatespina <benatespina@gmail.com>
 * @author gorkalaucirica <gorka.lauzirika@gmail.com>
 */

namespace spec\Myclapboard\UserBundle\Controller;

use FOS\RestBundle\View\ViewHandler;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\Model\UserManager;
use Myclapboard\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class RegistrationControllerSpec.
 *
 * @package spec\Myclapboard\UserBundle\Controller
 */
class RegistrationControllerSpec extends ObjectBehavior
{
    function let(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Myclapboard\UserBundle\Controller\RegistrationController');
    }

    function it_extends_base_api_controller()
    {
        $this->shouldHaveType('Myclapboard\CoreBundle\Controller\BaseApiController');
    }

// ToDo
//    function it_does_not_register_because_the_event_has_a_response()
//    {
//    }

    function it_does_not_register_because_the_plainPassword_is_not_an_array(
        ContainerInterface $container,
        FactoryInterface $formFactory,
        UserManager $userManager,
        TraceableEventDispatcherInterface $dispatcher,
        Request $request,
        FormInterface $form,
        User $user,
        Event $event
    )
    {
        $container->get('fos_user.registration.form.factory')
            ->shouldBeCalled()->willReturn($formFactory);
        $container->get('fos_user.user_manager')
            ->shouldBeCalled()->willReturn($userManager);
        $container->get('event_dispatcher')
            ->shouldBeCalled()->willReturn($dispatcher);
        $container->get('request')
            ->shouldBeCalled()->willReturn($request);

        $userManager->createUser()
            ->shouldBeCalled()->willReturn($user);
        $dispatcher->dispatch(Argument::any(), Argument::any())
            ->shouldBeCalled()->willReturn($event);

        $formFactory->createForm()->shouldBeCalled()->willReturn($form);
        $form->setData($user)->shouldBeCalled()->willReturn($form);
        $form->submit($request)->shouldBeCalled()->willReturn($form);

        $form->get('plainPassword')->shouldBeCalled()->willReturn($form);
        $form->getViewData()->shouldBeCalled()->willReturn('plain-password');

        $this->shouldThrow(
            new BadRequestHttpException(
                'This is not the right way to insert the password, for more info you could read the Api documentation'
            )
        )->during('registerAction');
    }

    function it_does_not_register_because_the_form_is_not_valid(
        ContainerInterface $container,
        FactoryInterface $formFactory,
        UserManager $userManager,
        TraceableEventDispatcherInterface $dispatcher,
        Request $request,
        FormInterface $form,
        User $user,
        Event $event,
        FormError $error,
        FormInterface $formChild,
        FormInterface $formGrandChild,
        ViewHandler $viewHandler
    )
    {
        $container->get('fos_user.registration.form.factory')
            ->shouldBeCalled()->willReturn($formFactory);
        $container->get('fos_user.user_manager')
            ->shouldBeCalled()->willReturn($userManager);
        $container->get('event_dispatcher')
            ->shouldBeCalled()->willReturn($dispatcher);
        $container->get('request')
            ->shouldBeCalled()->willReturn($request);

        $userManager->createUser()
            ->shouldBeCalled()->willReturn($user);
        $dispatcher->dispatch(Argument::any(), Argument::any())
            ->shouldBeCalled()->willReturn($event);

        $formFactory->createForm()->shouldBeCalled()->willReturn($form);
        $form->setData($user)->shouldBeCalled()->willReturn($form);
        $form->submit($request)->shouldBeCalled()->willReturn($form);

        $form->get('plainPassword')->shouldBeCalled()->willReturn($form);
        $form->getViewData()->shouldBeCalled()->willReturn(array());

        $form->isValid()->shouldBeCalled()->willReturn(false);
        $form->getErrors()->shouldBeCalled()->willReturn(array($error));
        $error->getMessage()->shouldBeCalled()->willReturn('error message');
        $form->all()->shouldBeCalled()->willReturn(array($formChild));
        $formChild->isValid()->shouldBeCalled()->willReturn(false);
        $formChild->getName()->shouldBeCalled()->willReturn('form child name');

        $formChild->getErrors()->shouldBeCalled()->willReturn(array($error));
        $error->getMessage()->shouldBeCalled()->willReturn('error message');
        $formChild->all()->shouldBeCalled()->willReturn(array($formGrandChild));
        $formGrandChild->isValid()->shouldBeCalled()->willReturn(true);

        $container->get('fos_rest.view_handler')->shouldBeCalled()->willReturn($viewHandler);

        $this->registerAction();
    }

    function it_registers_the_user(
        ContainerInterface $container,
        FactoryInterface $formFactory,
        UserManager $fosUserManager,
        TraceableEventDispatcherInterface $dispatcher,
        Request $request,
        FormInterface $form,
        User $user,
        Event $event,
        \Myclapboard\UserBundle\Manager\UserManager $userManager,
        ViewHandler $viewHandler
    )
    {
        $container->get('fos_user.registration.form.factory')
            ->shouldBeCalled()->willReturn($formFactory);
        $container->get('fos_user.user_manager')
            ->shouldBeCalled()->willReturn($fosUserManager);
        $container->get('event_dispatcher')
            ->shouldBeCalled()->willReturn($dispatcher);
        $container->get('request')
            ->shouldBeCalled()->willReturn($request);

        $fosUserManager->createUser()
            ->shouldBeCalled()->willReturn($user);
        $dispatcher->dispatch(Argument::any(), Argument::any())
            ->shouldBeCalled()->willReturn($event);

        $formFactory->createForm()->shouldBeCalled()->willReturn($form);
        $form->setData($user)->shouldBeCalled()->willReturn($form);
        $form->submit($request)->shouldBeCalled()->willReturn($form);

        $form->get('plainPassword')->shouldBeCalled()->willReturn($form);
        $form->getViewData()->shouldBeCalled()->willReturn(array());

        $form->isValid()->shouldBeCalled()->willReturn(true);
        $dispatcher->dispatch(Argument::any(), Argument::any())
            ->shouldBeCalled()->willReturn($event);
        $fosUserManager->updateUser($user)->shouldBeCalled();

        $container->get('myclapboard_user.manager.user')
            ->shouldBeCalled()->willReturn($userManager);
        $userManager->createApiKey($user)
            ->shouldBeCalled()->willReturn('token-string');

        $container->get('fos_rest.view_handler')
            ->shouldBeCalled()->willReturn($viewHandler);

        $this->registerAction();
    }

    function it_does_not_confirm_because_this_user_has_not_acces_to_this_section(
        ContainerInterface $container,
        SecurityContext $securityContext,
        TokenInterface $token
    )
    {
        $container->has('security.context')
            ->shouldBeCalled()->willReturn(true);
        $container->get('security.context')
            ->shouldBeCalled()->willReturn($securityContext);
        $securityContext->getToken()
            ->shouldBeCalled()->willReturn($token);
        $token->getUser()
            ->shouldBeCalled()->willReturn(null);

        $this->shouldThrow(
            new AccessDeniedException('This user does not have access to this section')
        )->during('confirmedAction');
    }

    function it_confirms_the_account(
        ContainerInterface $container,
        SecurityContext $securityContext,
        TokenInterface $token,
        User $user,
        ViewHandler $viewHandler
    )
    {
        $container->has('security.context')
            ->shouldBeCalled()->willReturn(true);
        $container->get('security.context')
            ->shouldBeCalled()->willReturn($securityContext);
        $securityContext->getToken()
            ->shouldBeCalled()->willReturn($token);
        $token->getUser()
            ->shouldBeCalled()->willReturn($user);

        $container->get('fos_rest.view_handler')
            ->shouldBeCalled()->willReturn($viewHandler);

        $this->confirmedAction();
    }
}
