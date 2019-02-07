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
     * @var string
     */
    private $actionHolder;

    /**
     * @var string
     */
    private $week;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $comments;

    /**
     * @var boolean
     */
    private $isDone;

    /**
     * Action constructor.
     * @param int $id
     * @param string $code
     * @param string $description
     * @param string $actionHolder
     * @param string $week
     * @param string $status
     * @param string $comments
     * @param bool $isDone
     */
    public function __construct(int $id, string $code, string $description, string $actionHolder, string $week, string $status, string $comments, bool $isDone)
    {
        $this->id = $id;
        $this->code = $code;
        $this->description = $description;
        $this->actionHolder = $actionHolder;
        $this->week = $week;
        $this->status = $status;
        $this->comments = $comments;
        $this->isDone = $isDone;
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
     * @return string
     */
    public function getActionHolder(): string
    {
        return $this->actionHolder;
    }

    /**
     * @param string $actionHolder
     */
    public function setActionHolder(string $actionHolder): void
    {
        $this->actionHolder = $actionHolder;
    }

    /**
     * @return string
     */
    public function getWeek(): string
    {
        return $this->week;
    }

    /**
     * @param string $week
     */
    public function setWeek(string $week): void
    {
        $this->week = $week;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getComments(): string
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     */
    public function setComments(string $comments): void
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
            'status' => $this->getStatus(),
            'comments' => $this->isMcomments(),
            'isDone' => $this->isDone(),
        ];
    }
}