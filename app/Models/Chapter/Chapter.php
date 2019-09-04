<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 9-5-2019
 * Time: 14:00
 */

namespace App\Models\Chapter;

use JsonSerializable;

class Chapter implements JsonSerializable
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
     * @var string|null
     */
    private $content = null;

    /**
     * @var Chapter[]|null
     */
    private $chapters = null;

    /**
     * @var int|null
     */
    private $parentChapterId = null;

    /**
     * @var int|null
     */
    private $order = null;

    /**
     * Chapter constructor.
     */
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
     * @return null|string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param null|string $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
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
     * @return int|null
     */
    public function getParentChapterId(): ?int
    {
        return $this->parentChapterId;
    }

    /**
     * @param int|null $parentChapterId
     */
    public function setParentChapterId(?int $parentChapterId): void
    {
        $this->parentChapterId = $parentChapterId;
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

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'content' => $this->getContent(),
            'chapters' => $this->getChapters() ? array_map(function(Chapter $c) { return $c->getId(); }, $this->getChapters()) : [],
            'parentChapterId' => $this->getParentChapterId(),
            'order' => $this->getOrder(),
        ];
    }
}