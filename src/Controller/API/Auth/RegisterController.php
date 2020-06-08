<?php

declare(strict_types=1);

namespace App\Controller\API\Auth;

use App\Controller\ControllerHelper;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\UserDTO;
use App\Domain\User\UseCase\Register\Confirm\Command as ConfirmCommand;
use App\Domain\User\UseCase\Register\Confirm\Handler as ConfirmHandler;
use App\Domain\User\UseCase\Register\Request\Command as RegisterPayloads;
use App\Domain\User\UseCase\Register\Request\Handler as RegisterHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route(value="api/v1/auth/register") */
class RegisterController extends AbstractController
{
    use ControllerHelper;
    /** @Route("", name="register", methods={"POST"}) */
    public function register(
        Request $request,
        RegisterHandler $handler,
        AuthenticationSuccessHandler $ash
    ): JWTAuthenticationSuccessResponse {
        /** @var RegisterPayloads $command */
        $command = $this->serializer->deserialize($request, RegisterPayloads::class);
        /** @var User $user */
        $user = $handler->handle($command);
        return $ash->handleAuthenticationSuccess($user);
    }

    /** @Route("/confirm/{token}", name="registerConfirm", methods={"GET"}) */
    public function confirm(ConfirmHandler $handler, string $token): RedirectResponse
    {
        $handler->handle(new ConfirmCommand($token));
        return $this->redirectToRoute('index', [
            'vueRouting' => '',
            'register' => 'confirm'
        ]);
    }
}
