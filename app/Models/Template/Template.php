<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 25-4-2019
 * Time: 15:08
 */

namespace App\Models\Template;

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
     * @var null | TemplateItem[]
     */
    private $folders = null;
    /**
     * @var null | TemplateItem[]
     */
    private $subFolders = null;
    /**
     * @var null | TemplateItem[]
     */
    private $documents = null;
    /**
     * @var null | TemplateItemsWithParent[]
     */
    private $subDocuments = null;

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
     * @return null|TemplateItem[]
     */
    public function getFolders()
    {
        return $this->folders;
    }

    /**
     * @param null|TemplateItem[] $folders
     */
    public function setFolders($folders): void
    {
        $this->folders = $folders;
    }

    /**
     * @return null|TemplateItem[]
     */
    public function getSubFolders()
    {
        return $this->subFolders;
    }

    /**
     * @param null|TemplateItem[] $subFolders
     */
    public function setSubFolders($subFolders): void
    {
        $this->subFolders = $subFolders;
    }

    /**
     * @return null|TemplateItem[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param null|TemplateItem[] $documents
     */
    public function setDocuments($documents): void
    {
        $this->documents = $documents;
    }

    /**
     * @return null|TemplateItemsWithParent[]
     */
    public function getSubDocuments()
    {
        return $this->subDocuments;
    }

    /**
     * @param null|TemplateItemsWithParent[] $subDocuments
     */
    public function setSubDocuments($subDocuments): void
    {
        $this->subDocuments = $subDocuments;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'folders' => $this->getFolders(),
            'subFolders' => $this->getSubFolders(),
            'documents' => $this->getDocuments(),
            'subDocuments' => $this->getSubDocuments(),
        ];
    }

}