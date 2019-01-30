<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 29-1-2019
 * Time: 20:24
 */

namespace App\Models\Document;

use JsonSerializable;

class Document implements JsonSerializable
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
    private $content;

    /**
     * @var int[]
     */
    private $parentFolderIds;

    /**
     * Document constructor.
     * @param int $id
     * @param string $name
     * @param string | null $content
     * @param int[] $parentFolderIds
     */
    public function __construct(int $id, string $name, $content, $parentFolderIds)
    {
        $this->id = $id;
        $this->name = $name;
        $this->content = $content;
        $this->parentFolderIds = $parentFolderIds;
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
     * @return string | null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string | null $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * @return int[]
     */
    public function getParentFolderIds(): array
    {
        return $this->parentFolderIds;
    }

    /**
     * @param int[] $parentFolderIds
     */
    public function setParentFolderIds(array $parentFolderIds): void
    {
        $this->parentFolderIds = $parentFolderIds;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'content' => $this->getContent(),
            'foldersId' => $this->getParentFolderIds()
        ];
    }


}