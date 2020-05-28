<?php

declare(strict_types=1);

namespace App\Tests\AuthController\PasswordReset;

use App\DataFixtures\UserFixtures;
use App\Tests\AbstractTest;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordActionTest extends AbstractTest
{
    public function getFixtures() : array
    {
        return [UserFixtures::class];
    }

    public function setUp() : void
    {
        parent::setUp();
        $this->url .= '/auth/password/reset';
    }

    public function testResetPasswordAndConfirmValid(): void
    {
        $client = self::getClient();
        $client->request(
            'POST',
            $this->url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test0@mail.com'
            ])
        );

        /** @var Response $response */
        $response = $client->getResponse();
        $this->assertResponseOk($response);
        $email = $this->getMailerMessage(0);
        $crawler = new Crawler($email->getHtmlBody());
        $code = $crawler->filter('#resetCode');
        $this->assertIsString($code->text());

        $client->request(
            'POST',
            $this->url.'/confirm',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test0@mail.com',
                'confirmation_code' => $code->text(),
                'password' => 'qqqqqqqq',
                'plain_password' => 'qqqqqqqq'
            ])
        );

        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertResponseOk($response);
        $this->assertArrayHasKey('message', $content);
    }

    public function testResetPasswordInvalid() : void
    {
        $client = self::getClient();
        $client->request(
            'POST',
            $this->url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'not_found@mail.com',
            ])
        );

        /** @var Response $response */
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertResponseCode(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $response);
        $this->assertArrayHasKey('errors', $content);
        $this->assertArrayHasKey('email', $content['errors']);
    }
}