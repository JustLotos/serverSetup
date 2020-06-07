<?php

declare(strict_types=1);

namespace App\Controller\API\Auth;

use App\Controller\ControllerHelper;
use App\Domain\User\UseCase\ResetPassword\Confirm\Command as ConfirmCommand;
use App\Domain\User\UseCase\ResetPassword\Confirm\Handler as ConfirmHandler;
use App\Domain\User\UseCase\ResetPassword\Request\Command as ResetCommand;
use App\Domain\User\UseCase\ResetPassword\Request\Handler as ResetHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/** @Route(value="api/v1/auth/reset/password") */
class ResetPasswordController extends AbstractController
{
    use ControllerHelper;

    /** @Route("/", name="resetPassword", methods={"POST"}) */
    public function reset(Request $request, ResetHandler $handler): Response
    {
        /** @var ResetCommand $command */
        $command = $this->serializer->serialize($request, ResetCommand::class);
        $handler->handle($command);
        return $this->response($this->getSimpleSuccessResponse());
    }

    /** @Route("/confirm/{token}", name="resetPasswordConfirm", methods={"POST"}) */
    public function confirm(ConfirmHandler $handler, string $token): RedirectResponse
    {
        $handler->handle(new ConfirmCommand($token));
        return $this->redirectToRoute('index', [
            'vueRouting' => '',
            'resetPassword' => 'confirm'
        ]);
    }
}
