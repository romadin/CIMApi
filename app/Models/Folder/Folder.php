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
     * @var boolean
     */
    private $on;

    /**
     * @var Folder[] | null
     */
    private $subFolders;

    /**
     * @var boolean
     */
    private $isMainFolder;

    /**
     * @var int
     */
    private $order;

    /**
     * Folder constructor.
     * @param int $id
     * @param string $name
     * @param bool $on
     * @param bool $isMainFolder
     * @param int $order
     * @param int | null $projectId
     * @param Folder[] | null $subFolders
     */
    public function __construct(int $id, string $name, bool $on, $isMainFolder, $order, $projectId = null, $subFolders = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->on = $on;
        $this->isMainFolder = $isMainFolder;
        $this->order = $order;
        $this->projectId = $projectId;
        $this->subFolders = $subFolders;
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
    public function isMainFolder(): bool
    {
        return $this->isMainFolder;
    }

    /**
     * @param bool $isMainFolder
     */
    public function setIsMainFolder(bool $isMainFolder): void
    {
        $this->isMainFolder = $isMainFolder;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'projectId' => $this->getProjectId(),
            'on' => $this->isOn(),
            'subFolders' => $this->getSubFolders(),
            'order' => $this->getOrder(),
            'isMain' => $this->isMainFolder(),
        ];
    }

}