<?php
namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->loginUser($this->createMockUser());
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Task Management');
    }

    public function testCreateTask(): void
    {
        $this->client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    private function createMockUser(): UserInterface
    {
        $user = $this->createMock(User::class);
        $user->method('getRoles')->willReturn(['ROLE_ADMIN']);
        $user->method('getUserIdentifier')->willReturn('admin');
        return $user;
    }
}