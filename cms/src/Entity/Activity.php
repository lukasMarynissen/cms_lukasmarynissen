<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActivityRepository")
 */
class Activity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $user_id;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="Vul aub een geldig startmoment in")
     */
    private $start_time;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="Vul aub een geldig eindmoment in")
     */
    private $end_time;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Vul aub een geldige pauzetijd in")
     */
    private $break_length;

    /**
     * @ORM\Column(type="integer")
     */
    private $customer_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $used_materials;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Vul aub de transportafstand in")
     */
    private $transport_distance;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $activity_description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="activities")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="workeractivities")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="worker_activities")
     */
    private $worker;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->start_time;
    }

    public function setStartTime(\DateTimeInterface $start_time): self
    {
        $this->start_time = $start_time;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->end_time;
    }

    public function setEndTime(\DateTimeInterface $end_time): self
    {
        $this->end_time = $end_time;

        return $this;
    }

    public function getBreakLength(): ?int
    {
        return $this->break_length;
    }

    public function setBreakLength(?int $break_length): self
    {
        $this->break_length = $break_length;

        return $this;
    }

    public function getCustomerId(): ?int
    {
        return $this->customer_id;
    }

    public function setCustomerId(int $customer_id): self
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    public function getUsedMaterials(): ?string
    {
        return $this->used_materials;
    }

    public function setUsedMaterials(?string $used_materials): self
    {
        $this->used_materials = $used_materials;

        return $this;
    }

    public function getTransportDistance(): ?int
    {
        return $this->transport_distance;
    }

    public function setTransportDistance(?int $transport_distance): self
    {
        $this->transport_distance = $transport_distance;

        return $this;
    }

    public function getActivityDescription(): ?string
    {
        return $this->activity_description;
    }

    public function setActivityDescription(?string $activity_description): self
    {
        $this->activity_description = $activity_description;

        return $this;
    }

    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    public function setCustomer(?User $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getWorker(): ?User
    {
        return $this->worker;
    }

    public function setWorker(?User $worker): self
    {
        $this->worker = $worker;

        return $this;
    }
}
