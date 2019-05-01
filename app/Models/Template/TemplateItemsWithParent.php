<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 25-4-2019
 * Time: 17:16
 */

namespace App\Models\Template;


use JsonSerializable;

class TemplateItemsWithParent implements JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var TemplateItem[]
     */
    private $items;

    public function __construct()
    {
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
     * @return TemplateItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param TemplateItem[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->getName(),
            'items' => $this->getItems()
        ];
    }
}