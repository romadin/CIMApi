<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 23-5-2019
 * Time: 15:45
 */

namespace App\Models\CacheItem;


use JsonSerializable;

class CacheItem implements JsonSerializable
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var null|string
     */
    private $name = null;
    /**
     * @var null|string
     */
    private $url = null;
    /**
     * @var null |
     */
    private $options = null;
    /**
     * @var null|string
     */
    private $hash = null;


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
     * @return null|string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param null|string $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return null
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param null $options
     */
    public function setOptions($options): void
    {
        $this->options = $options;
    }

    /**
     * @return null|string
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @param null|string $hash
     */
    public function setHash(?string $hash): void
    {
        $this->hash = $hash;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'url' => $this->getUrl(),
            'hash' => $this->getHash(),
        ];
    }
}