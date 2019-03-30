<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 30-3-2019
 * Time: 13:38
 */

namespace App\Models\Event;


use JsonSerializable;

class Location implements JsonSerializable
{
    /**
     * @var string
     */
    private $streetName;

    /**
     * @var string
     */
    private $zipCode;

    /**
     * @var string
     */
    private $residence;

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getStreetName(): string
    {
        return $this->streetName;
    }

    /**
     * @param string $streetName
     */
    public function setStreetName(string $streetName): void
    {
        $this->streetName = $streetName;
    }

    /**
     * @return string
     */
    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    /**
     * @return string
     */
    public function getResidence(): string
    {
        return $this->residence;
    }

    /**
     * @param string $residence
     */
    public function setResidence(string $residence): void
    {
        $this->residence = $residence;
    }

    public function jsonSerialize()
    {
        return [
            'streetName' => $this->getStreetName(),
            'zipCode' => $this->getZipCode(),
            'residence' => $this->getResidence(),
        ];
    }
}