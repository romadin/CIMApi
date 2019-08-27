<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 19-6-2019
 * Time: 16:49
 */

namespace App\Models\Company;


use App\Models\WorkFunction\WorkFunction;
use JsonSerializable;

class Company implements JsonSerializable
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
     * @var WorkFunction|null
     */
    private $parent = null;

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
     * @return WorkFunction|null
     */
    public function getParent(): ?WorkFunction
    {
        return $this->parent;
    }

    /**
     * @param WorkFunction|null $parent
     */
    public function setParent(?WorkFunction $parent): void
    {
        $this->parent = $parent;
    }


    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'parent' => $this->getParent(),
        ];
    }

}