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
    private $originalName;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string | null
     */
    private $content;

    /**
     * @var int | null
     */
    private $order = null;

    /**
     * @var boolean
     */
    private $fromTemplate;

    /**
     * Document constructor.
     * @param int $id
     * @param string $originalName
     * @param string | null $name
     * @param string | null $content
     * @param boolean $fromTemplate
     */
    public function __construct(int $id, string $originalName, $name, $content,  bool $fromTemplate)
    {
        $this->id = $id;
        $this->originalName = $originalName;
        $this->name = $name;
        $this->content = $content;
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
    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    /**
     * @param string $originalName
     */
    public function setOriginalName(string $originalName): void
    {
        $this->originalName = $originalName;
    }

    /**
     * @return string | null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string | null $name
     */
    public function setName($name): void
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

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'originalName' => $this->getOriginalName(),
            'name' => $this->getName(),
            'content' => $this->getContent(),
            'order' => $this->getOrder(),
            'fromTemplate' => $this->isfromTemplate(),
        ];
    }


}