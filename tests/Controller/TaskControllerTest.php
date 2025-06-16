<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createMockUser());
        $client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
    }

    public function testCreateTask(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createMockUser());
        $client->request('GET', '/tasks/create');

        $this->assertResponseIsSuccessful();
    }

    private function createMockUser(): UserInterface
    {
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn(['ROLE_ADMIN']);
        $user->method('getUserIdentifier')->willReturn('admin');
        return $user;
    }
}