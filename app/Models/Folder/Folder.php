<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 24-1-2019
 * Time: 00:39
 */

namespace App\Models\Folder;

use JsonSerializable;

class Folder implements JsonSerializable
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
    private $projectId;

    /**
     * @var boolean
     */
    private $on;

    /**
     * Folder constructor.
     * @param int $id
     * @param string $name
     * @param int $projectId
     * @param bool $on
     */
    public function __construct(int $id, string $name, int $projectId, bool $on)
    {
        $this->id = $id;
        $this->name = $name;
        $this->projectId = $projectId;
        $this->on = $on;
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
     * @return bool
     */
    public function isOn(): bool
    {
        return $this->on;
    }

    /**
     * @param bool $on
     */
    public function setOn(bool $on): void
    {
        $this->on = $on;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'projectId' => $this->getProjectId(),
            'on' => $this->isOn(),
        ];
    }

}