<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 19-3-2019
 * Time: 11:51
 */

namespace App\Models\Organisation;

use App\Models\Module\Module;
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

    /**
     * @var null | int
     */
    private $templatesNumber = null;

    /**
     * @var null | Module[]
     */
    private $modules = null;

    /**
     * @var null | \DateTime
     */
    private $demoPeriod = null;

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

    /**
     * @return int|null
     */
    public function getTemplatesNumber(): ?int
    {
        return $this->templatesNumber;
    }

    /**
     * @param int|null $templatesNumber
     */
    public function setTemplatesNumber(?int $templatesNumber): void
    {
        $this->templatesNumber = $templatesNumber;
    }

    /**
     * @return Module[]|null
     */
    public function getModules(): ?array
    {
        return $this->modules;
    }

    /**
     * @param Module[]|null $modules
     */
    public function setModules(?array $modules): void
    {
        $this->modules = $modules;
    }

    /**
     * @param int $id
     * @return Module|false
     */
    public function getModule(int $id)
    {
        $module = array_filter($this->getModules(), function($module) use ($id) {
            return $module->getId() === $id;
        });
        return $module ? $module[0] : $module;
    }

    public function getDemoPeriod(): ?\DateTime
    {
        return $this->demoPeriod;
    }

    public function setDemoPeriod(?\DateTime $period): void
    {
        $this->demoPeriod = $period;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'primaryColor' => $this->getPrimaryColor(),
            'secondaryColor' => $this->getSecondaryColor(),
            'maxUsers' => $this->getMaxUsers(),
            'templatesNumber' => $this->getTemplatesNumber(),
            'modules' => $this->getModules(),
            'demoPeriod' => $this->getDemoPeriod(),
            'hasLogo' => $this->getLogo() !== null,
        ];
    }

}
