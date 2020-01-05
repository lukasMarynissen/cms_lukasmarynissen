<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Mapping\ClassMetadata;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email")
 */
class User implements UserInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank(message="Vul aub een geldig emailadres in")
     * @Assert\Email(message="Vul aub een geldig emailadres in")
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Vul aub een geldig wachtwoord in")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Vul aub een geldige voornaam in")
     */
    private $first_name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Vul aub een geldige naam in")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Vul aub een geldige bedrijfsnaam in")
     */
    private $company_name = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity", mappedBy="customer")
     */
    private $activities;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity", mappedBy="user")
     */
    private $workeractivities;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $subcontractor;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Rate", mappedBy="customer", cascade={"persist", "remove"})
     */
    private $rate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Period", mappedBy="customer")
     */
    private $periods;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity", mappedBy="worker")
     */
    private $worker_activities;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="sender")
     */
    private $sentmessages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="recipient")
     */
    private $receivedmessages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     */
    private $comments;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->workeractivities = new ArrayCollection();
        $this->periods = new ArrayCollection();
        $this->worker_activities = new ArrayCollection();
        $this->sentmessages = new ArrayCollection();
        $this->receivedmessages = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->company_name;
    }

    public function setCompanyName(?string $company_name): self
    {
        $this->company_name = $company_name;

        return $this;
    }

    /**
     * @return Collection|Activity[]
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): self
    {
        if (!$this->activities->contains($activity)) {
            $this->activities[] = $activity;
            $activity->setCustomer($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): self
    {
        if ($this->activities->contains($activity)) {
            $this->activities->removeElement($activity);
            // set the owning side to null (unless already changed)
            if ($activity->getCustomer() === $this) {
                $activity->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Activity[]
     */
    public function getWorkeractivities(): Collection
    {
        return $this->workeractivities;
    }

    public function addWorkeractivity(Activity $workeractivity): self
    {
        if (!$this->workeractivities->contains($workeractivity)) {
            $this->workeractivities[] = $workeractivity;
            $workeractivity->setUser($this);
        }

        return $this;
    }

    public function removeWorkeractivity(Activity $workeractivity): self
    {
        if ($this->workeractivities->contains($workeractivity)) {
            $this->workeractivities->removeElement($workeractivity);
            // set the owning side to null (unless already changed)
            if ($workeractivity->getUser() === $this) {
                $workeractivity->setUser(null);
            }
        }

        return $this;
    }

    public function getSubcontractor(): ?bool
    {
        return $this->subcontractor;
    }

    public function setSubcontractor(?bool $subcontractor): self
    {
        $this->subcontractor = $subcontractor;

        return $this;
    }

    public function getRate(): ?Rate
    {
        return $this->rate;
    }

    public function setRate(?Rate $rate): self
    {
        $this->rate = $rate;

        // set (or unset) the owning side of the relation if necessary
        $newCustomer = null === $rate ? null : $this;
        if ($rate->getCustomer() !== $newCustomer) {
            $rate->setCustomer($newCustomer);
        }

        return $this;
    }

    /**
     * @return Collection|Period[]
     */
    public function getPeriods(): Collection
    {
        return $this->periods;
    }

    public function addPeriod(Period $period): self
    {
        if (!$this->periods->contains($period)) {
            $this->periods[] = $period;
            $period->setCustomer($this);
        }

        return $this;
    }

    public function removePeriod(Period $period): self
    {
        if ($this->periods->contains($period)) {
            $this->periods->removeElement($period);
            // set the owning side to null (unless already changed)
            if ($period->getCustomer() === $this) {
                $period->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getSentmessages(): Collection
    {
        return $this->sentmessages;
    }

    public function addSentmessage(Message $sentmessage): self
    {
        if (!$this->sentmessages->contains($sentmessage)) {
            $this->sentmessages[] = $sentmessage;
            $sentmessage->setSender($this);
        }

        return $this;
    }

    public function removeSentmessage(Message $sentmessage): self
    {
        if ($this->sentmessages->contains($sentmessage)) {
            $this->sentmessages->removeElement($sentmessage);
            // set the owning side to null (unless already changed)
            if ($sentmessage->getSender() === $this) {
                $sentmessage->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getReceivedmessages(): Collection
    {
        return $this->receivedmessages;
    }

    public function addReceivedmessage(Message $receivedmessage): self
    {
        if (!$this->receivedmessages->contains($receivedmessage)) {
            $this->receivedmessages[] = $receivedmessage;
            $receivedmessage->setRecipient($this);
        }

        return $this;
    }

    public function removeReceivedmessage(Message $receivedmessage): self
    {
        if ($this->receivedmessages->contains($receivedmessage)) {
            $this->receivedmessages->removeElement($receivedmessage);
            // set the owning side to null (unless already changed)
            if ($receivedmessage->getRecipient() === $this) {
                $receivedmessage->setRecipient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

}
