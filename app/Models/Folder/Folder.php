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
     * @var int | null
     */
    private $projectId;

    /**
     * @var Folder[] | null
     */
    private $subFolders = null;

    /**
     * @var int
     */
    private $order;

    /**
     * @var boolean
     */
    private $fromTemplate;

    /**
     * @var null | int[]
     */
    private $parentFolders = null;

    /**
     * Folder constructor.
     * @param int $id
     * @param string $name
     * @param int $order
     * @param boolean $fromTemplate
     * @param int | null $projectId
     */
    public function __construct(int $id, string $name, $order, $fromTemplate, $projectId = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->order = $order;
        $this->projectId = $projectId;
        $this->fromTemplate = $fromTemplate;
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
     * @return int | null
     */
    public function getProjectId():? int
    {
        return $this->projectId;
    }

    /**
     * @param int | null $projectId
     */
    public function setProjectId(?int $projectId): void
    {
        $this->projectId = $projectId;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    /**
     * @return Folder[] | null
     */
    public function getSubFolders()
    {
        return $this->subFolders;
    }

    /**
     * @param Folder[] | null $subFolders
     */
    public function setSubFolders($subFolders): void
    {
        $this->subFolders = $subFolders;
    }

    /**
     * @return bool
     */
    public function isFromTemplate(): bool
    {
        return $this->fromTemplate;
    }

    /**
     * @param bool $fromTemplate
     */
    public function setFromTemplate(bool $fromTemplate): void
    {
        $this->fromTemplate = $fromTemplate;
    }

    /**
     * @return int[]|null
     */
    public function getParentFoldersId(): ?array
    {
        return $this->parentFolders;
    }

    /**
     * @param int[]|null $parentFolders
     */
    public function setParentFoldersId(?array $parentFolders): void
    {
        $this->parentFolders = $parentFolders;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'subFolders' => $this->getSubFolders(),
            'order' => $this->getOrder(),
            'fromTemplate' => $this->isfromTemplate(),
        ];
    }

}