<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 25-4-2019
 * Time: 15:08
 */

namespace App\Models\Template;

use App\Models\WorkFunction\WorkFunction;
use JsonSerializable;

class Template implements JsonSerializable
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var null | string
     */
    private $name = null;
    /**
     * @var null | boolean
     */
    private $isDefault = null;
    /**
     * @var int
     */
    private $organisationId;
    /**
     * @var null | WorkFunction[]
     */
    private $workFunctions = null;

    public function __construct()
    {
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
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool|null
     */
    public function isDefault(): ?bool
    {
        return $this->isDefault;
    }

    /**
     * @param bool|null $isDefault
     */
    public function setDefault(?bool $isDefault): void
    {
        $this->isDefault = $isDefault;
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

    /**
     * @return null|WorkFunction[]
     */
    public function getWorkFunctions()
    {
        return $this->workFunctions;
    }

    /**
     * @param null|WorkFunction[] $workFunctions
     */
    public function setWorkFunctions($workFunctions): void
    {
        $this->workFunctions = $workFunctions;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'isDefault' => $this->isDefault(),
            'organisationId' => $this->getOrganisationId(),
        ];
    }

}