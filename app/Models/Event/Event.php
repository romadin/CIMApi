<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 30-3-2019
 * Time: 08:55
 */

namespace App\Models\Event;


use DateTime;
use JsonSerializable;

class Event implements JsonSerializable
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string | null
     */
    private $description = null;

    /**
     * @var int
     */
    private $projectId;

    /**
     * @var DateTime
     */
    private $startDate;

    /**
     * @var DateTime
     */
    private $endDate;

    /**
     * @var Location | null
     */
    private $location = null;

    public function __construct() {}

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string | null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string | null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getProjectId(): int
    {
        return $this->projectId;
    }

    /**
     * @param int $projectId
     */
    public function setProjectId(int $projectId): void
    {
        $this->projectId = $projectId;
    }

    /**
     * @return DateTime
     */
    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime $startDate
     */
    public function setStartDate(DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return DateTime
     */
    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime $endDate
     */
    public function setEndDate(DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return Location | null
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * @param Location | null $location
     */
    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'projectId' => $this->getProjectId(),
            'startDate' => $this->getStartDate(),
            'endDate' => $this->getEndDate(),
            'location' => $this->getLocation(),
        ];
    }
}