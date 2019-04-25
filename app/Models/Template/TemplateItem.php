<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 25-4-2019
 * Time: 15:24
 */

namespace App\Models\Template;


class TemplateItem
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $order;

    /**
     * @var null | string
     */
    private $content = null;

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
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @param string $order
     */
    public function setOrder(string $order): void
    {
        $this->order = $order;
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
}