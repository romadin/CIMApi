<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 19-6-2019
 * Time: 15:19
 */

namespace App\Models\Module;


use JsonSerializable;
use PHPUnit\Util\Json;
use stdClass;

class Module implements JsonSerializable
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
    private $isOn;
    /**
     * @var stdClass|null
     */
    private $restrictions = null;

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
     * @return bool
     */
    public function isOn(): bool
    {
        return $this->isOn;
    }

    /**
     * @param bool $isOn
     */
    public function setIsOn(bool $isOn): void
    {
        $this->isOn = $isOn;
    }

    /**
     * @return null|stdClass
     */
    public function getRestrictions(): ?stdClass
    {
        return $this->restrictions;
    }

    /**
     * @param null|stdClass $restrictions
     */
    public function setRestrictions(?stdClass $restrictions): void
    {
        $this->restrictions = $restrictions;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'on' => $this->isOn(),
            'restrictions' => $this->getRestrictions(),
        ];
    }
}
