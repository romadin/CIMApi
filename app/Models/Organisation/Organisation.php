<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 19-3-2019
 * Time: 11:51
 */

namespace App\Models\Organisation;

use JsonSerializable;

class Organisation implements JsonSerializable
{
    /**
     * @var null | int
     */
    private $id = null;

    /**
     * @var null | string
     */
    private $name = null;

    /**
     * @var null | string
     */
    private $primaryColor = null;

    /**
     * @var null | string
     */
    private $secondaryColor = null;

    /**
     * @var null | string
     */
    private $logo = null;

    /**
     * @var null | int
     */
    private $maxUsers = null;

    public function __construct()
    {
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
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
    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    /**
     * @param null|string $primaryColor
     */
    public function setPrimaryColor(?string $primaryColor): void
    {
        $this->primaryColor = $primaryColor;
    }

    /**
     * @return null|string
     */
    public function getSecondaryColor(): ?string
    {
        return $this->secondaryColor;
    }

    /**
     * @param null|string $secondaryColor
     */
    public function setSecondaryColor(?string $secondaryColor): void
    {
        $this->secondaryColor = $secondaryColor;
    }

    /**
     * @return null|string
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * @param null|string $logo
     */
    public function setLogo(?string $logo): void
    {
        $this->logo = $logo;
    }

    /**
     * @return int|null
     */
    public function getMaxUsers(): ?int
    {
        return $this->maxUsers;
    }

    /**
     * @param int|null $maxUsers
     */
    public function setMaxUsers(?int $maxUsers): void
    {
        $this->maxUsers = $maxUsers;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'primaryColor ' => $this->getPrimaryColor(),
            'secondaryColor ' => $this->getsecondaryColor(),
            'maxUsers' => $this->getMaxUsers(),
            'hasLogo' => $this->getLogo() !== null,
        ];
    }

}