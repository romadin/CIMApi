<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 9-5-2019
 * Time: 14:09
 */

namespace App\Models\Headline;

use App\Models\Chapter\Chapter;
use JsonSerializable;

class Headline implements JsonSerializable
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
     * @var Chapter[] | null
     */
    private $chapters = null;

    /**
     * @var int|null
     */
    private $order;

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
            'order' => $this->getOrder(),
        ];
    }
}