<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 13-1-2019
 * Time: 16:33
 */

namespace App\Models\Project;


use JsonSerializable;

class Project implements JsonSerializable
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
     * @var int
     */
    private $organisationId;


    public function __construct(int $id, string $name, int $organisationId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->organisationId = $organisationId;
    }

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
     * @return int
     */
    public function getOrganisationId(): int
    {
        return $this->organisationId;
    }

    /**
     * @param int $organisationId
     */
    public function setOrganisationId(int $organisationId): void
    {
        $this->organisationId = $organisationId;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'organisationId' => $this->getOrganisationId(),
        ];
    }

}