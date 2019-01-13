<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 13-1-2019
 * Time: 16:33
 */

namespace App\Models\Project;


use JsonSerializable;

class Project implements JsonSerializable
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
     * @var int | null
     */
    private $agendaId = null;

    /**
     * @var int | null
     */
    private $actionListId = null;

    public function __construct(
        int $id,
        string $name)
    {
        $this->id = $id;
        $this->name = $name;
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
     * @return int | null
     */
    public function getAgendaId()
    {
        return $this->agendaId;
    }

    /**
     * @param int $agendaId
     */
    public function setAgendaId(int $agendaId): void
    {
        $this->agendaId = $agendaId;
    }

    /**
     * @return int | null
     */
    public function getActionListId()
    {
        return $this->actionListId;
    }

    /**
     * @param int $actionListId
     */
    public function setActionListId(int $actionListId): void
    {
        $this->actionListId = $actionListId;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'agendaId' => $this->getAgendaId(),
            'actionListId' => $this->getActionListId(),
        ];
    }

}