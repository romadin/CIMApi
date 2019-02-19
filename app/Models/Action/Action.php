<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 7-2-2019
 * Time: 23:38
 */

namespace App\Models\Action;

use JsonSerializable;

class Action implements JsonSerializable
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string | null
     */
    private $actionHolder = null;

    /**
     * @var int | null
     */
    private $week = null;

    /**
     * @var string | null
     */
    private $comments = null;

    /**
     * @var boolean
     */
    private $isDone;

    /**
     * @var int
     */
    private $projectId;


    /**
     * Action constructor.
     * @param int $id
     * @param string $code
     * @param string $description
     * @param bool $isDone
     * @param int $projectId
     */
    public function __construct(int $id, string $code, string $description, bool $isDone, int $projectId)
    {
        $this->id = $id;
        $this->code = $code;
        $this->description = $description;
        $this->isDone = $isDone;
        $this->projectId = $projectId;
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
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string | null
     */
    public function getActionHolder()
    {
        return $this->actionHolder;
    }

    /**
     * @param string | null $actionHolder
     */
    public function setActionHolder($actionHolder): void
    {
        $this->actionHolder = $actionHolder;
    }

    /**
     * @return int | null
     */
    public function getWeek()
    {
        return $this->week;
    }

    /**
     * @param int | null $week
     */
    public function setWeek($week): void
    {
        $this->week = $week;
    }

    /**
     * @return string | null
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param string | null $comments
     */
    public function setComments($comments): void
    {
        $this->comments = $comments;
    }

    /**
     * @return bool
     */
    public function isDone(): bool
    {
        return $this->isDone;
    }

    /**
     * @param bool $isDone
     */
    public function setIsDone(bool $isDone): void
    {
        $this->isDone = $isDone;
    }

    /**
     * @return int
     */
    public function getProjectId(): int
    {
        return $this->projectId;
    }

    /**
     * @param int $projectId
     */
    public function setProjectId(int $projectId): void
    {
        $this->projectId = $projectId;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'description' => $this->getDescription(),
            'actionHolder' => $this->getActionHolder(),
            'week' => $this->getWeek(),
            'comments' => $this->getComments(),
            'isDone' => $this->isDone(),
            'projectId' => $this->getProjectId(),
        ];
    }
}