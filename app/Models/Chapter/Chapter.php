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
     * @var int|null
     */
    private $headlineId = null;

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
     * @return int|null
     */
    public function getHeadlineId(): ?int
    {
        return $this->headlineId;
    }

    /**
     * @param int|null $headlineId
     */
    public function setHeadlineId(?int $headlineId): void
    {
        $this->headlineId = $headlineId;
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
            'order' => $this->getOrder(),
            'headlineId' => $this->getHeadlineId(),
        ];
    }
}