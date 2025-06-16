<?php
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User extends BaseUser
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid_binary', unique: true)]
    protected $id;

    public function __construct()
    {
        parent::__construct();
        $this->roles = ['ROLE_ADMIN'];
        $this->id = Uuid::uuid4();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }
}