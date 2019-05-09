<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 25-4-2019
 * Time: 15:08
 */

namespace App\Models\Template;

use App\Models\Chapter\Chapter;
use App\Models\Headline\Headline;
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
     * @var null | TemplateItem[]
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
     * @return null|TemplateItem[]
     */
    public function getWorkFunctions()
    {
        return $this->workFunctions;
    }

    /**
     * @param null|TemplateItem[] $workFunctions
     */
    public function setWorkFunctions($workFunctions): void
    {
        $this->workFunctions = $workFunctions;
    }

    /**
     * @return null|TemplateItem[]
     */
    public function getHeadlines()
    {
        return $this->headlines;
    }

    /**
     * @param null|TemplateItem[] $headlines
     */
    public function setHeadlines($headlines): void
    {
        $this->headlines = $headlines;
    }

    /**
     * @return null|TemplateItem[]
     */
    public function getChapters()
    {
        return $this->chapters;
    }

    /**
     * @param null|TemplateItem[] $chapters
     */
    public function setChapters($chapters): void
    {
        $this->chapters = $chapters;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'isDefault' => $this->isDefault(),
            'organisationId' => $this->getOrganisationId(),
            'folders' => $this->getWorkFunctions(),
            'subFolders' => $this->getHeadlines(),
            'documents' => $this->getChapters(),
        ];
    }

}