<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ApplicationRepository;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Project $project;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $applicant;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getProject(): Project { return $this->project; }
    public function setProject(Project $p): self { $this->project = $p; return $this; }
    public function getApplicant(): User { return $this->applicant; }
    public function setApplicant(User $u): self { $this->applicant = $u; return $this; }
    public function getMessage(): string { return $this->message; }
    public function setMessage(string $m): self { $this->message = $m; return $this; }
    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $d): self { $this->createdAt = $d; return $this; }
}
