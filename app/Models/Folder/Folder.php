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
     * @var int | null
     */
    private $parentFolderId;

    /**
     * Folder constructor.
     * @param int $id
     * @param string $name
     * @param int $projectId
     * @param bool $on
     * @param int | null $parentFolderId
     */
    public function __construct(int $id, string $name, int $projectId, bool $on, $parentFolderId = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->projectId = $projectId;
        $this->on = $on;
        $this->parentFolderId = $parentFolderId;
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

    /**
     * @return int | null
     */
    public function getParentFolderId(): ?int
    {
        return $this->parentFolderId;
    }

    /**
     * @param int | null $parentFolderId
     */
    public function setParentFolderId(?int $parentFolderId): void
    {
        $this->parentFolderId = $parentFolderId;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'projectId' => $this->getProjectId(),
            'on' => $this->isOn(),
            'parentFolderId' => $this->getParentFolderId(),
        ];
    }

}