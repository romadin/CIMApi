<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 9-5-2019
 * Time: 14:16
 */

namespace App\Models\WorkFunction;


use App\Models\Company\Company;
use JsonSerializable;
use App\Models\Chapter\Chapter;
use App\Models\Headline\Headline;

class WorkFunction implements JsonSerializable
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
     * @var boolean
     */
    private $mainFunction;

    /**
     * @var int|null
     */
    private $order;

    /**
     * @var null | int
     */
    private $templateId = null;

    /**
     * @var null | int
     */
    private $projectId = null;

    /**
     * @var null | Headline[]
     */
    private $headlines = null;
    /**
     * @var null | Chapter[]
     */
    private $chapters = null;

    /**
     * @var null | boolean
     */
    private $on = null;

    /**
     * @var null | boolean
     */
    private $fromTemplate = null;

    /**
     * @var null | Company[]
     */
    private $companies = null;

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
    public function isMainFunction(): int
    {
        return $this->mainFunction;
    }

    /**
     * @param int $mainFunction
     */
    public function setMainFunction(int $mainFunction): void
    {
        $this->mainFunction = $mainFunction;
    }

    /**
     * @return int|null
     */
    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @param int|null $order
     */
    public function setOrder(?int $order): void
    {
        $this->order = $order;
    }

    /**
     * @return int|null
     */
    public function getTemplateId(): ?int
    {
        return $this->templateId;
    }

    /**
     * @param int|null $templateId
     */
    public function setTemplateId(?int $templateId): void
    {
        $this->templateId = $templateId;
    }

    /**
     * @return int|null
     */
    public function getProjectId(): ?int
    {
        return $this->projectId;
    }

    /**
     * @param int|null $projectId
     */
    public function setProjectId(?int $projectId): void
    {
        $this->projectId = $projectId;
    }

    /**
     * @return Headline[]|null
     */
    public function getHeadlines(): ?array
    {
        return $this->headlines;
    }

    /**
     * @param Headline[]|null $headlines
     */
    public function setHeadlines(?array $headlines): void
    {
        $this->headlines = $headlines;
    }

    /**
     * @return Chapter[]|null
     */
    public function getChapters(): ?array
    {
        return $this->chapters;
    }

    /**
     * @param Chapter[]|null $chapters
     */
    public function setChapters(?array $chapters): void
    {
        $this->chapters = $chapters;
    }

    /**
     * @return bool|null
     */
    public function getOn(): ?bool
    {
        return $this->on;
    }

    /**
     * @param bool|null $on
     */
    public function setOn(?bool $on): void
    {
        $this->on = $on;
    }

    /**
     * @return bool|null
     */
    public function getFromTemplate(): ?bool
    {
        return $this->fromTemplate;
    }

    /**
     * @param bool|null $fromTemplate
     */
    public function setFromTemplate(?bool $fromTemplate): void
    {
        $this->fromTemplate = $fromTemplate;
    }

    /**
     * @return Company[]|null
     */
    public function getCompanies(): ?array
    {
        return $this->companies;
    }

    /**
     * @param Company[]|null $companies
     */
    public function setCompanies(?array $companies): void
    {
        $this->companies = $companies;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'isMainFunction' => $this->isMainFunction(),
            'order' => $this->getOrder(),
            'parentId' => $this->getTemplateId() !== null ?: $this->getProjectId(),
            'on' => $this->getOn(),
            'fromTemplate' => $this->getFromTemplate(),
            'companies' => $this->getCompanies(),
        ];
    }
}