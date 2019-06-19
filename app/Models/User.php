<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 7-1-2019
 * Time: 18:46
 */

namespace App\Models;


use App\Models\Company\Company;
use JsonSerializable;

class User implements JsonSerializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $insertion;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $function;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Role
     */
    private $role;

    /**
     * @var int[]
     */
    private $projectsId = [];

    /**
     * @var null | string
     */
    private $image = null;

    /**
     * @var null | string
     */
    private $token = null;

    /**
     * @var null | string
     */
    private $phoneNumber = null;

    /**
     * @var null | int
     */
    private $organisationId = null;

    /**
     * @var null | Company
     */
    private $company = null;

    public function __construct(
        int $id,
        string $firstName,
        string $insertion = null,
        string $lastName,
        string $email,
        string $function,
        string $password,
        Role $role)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->insertion = $insertion;
        $this->lastName = $lastName;
        $this->password = $password;
        $this->email = $email;
        $this->function = $function;
        $this->role = $role;
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
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string | null
     */
    public function getInsertion()
    {
        return $this->insertion;
    }

    /**
     * @param string $insertion
     */
    public function setInsertion(string $insertion): void
    {
        $this->insertion = $insertion;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getFunction(): string
    {
        return $this->function;
    }

    /**
     * @param string $function
     */
    public function setFunction(string $function): void
    {
        $this->function = $function;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * remove password property for front end.
     */
    public function removePassword(): void
    {
        unset($this->password);
    }

    /**
     * @return Role
     */
    public function getRole(): Role
    {
        return $this->role;
    }

    /**
     * @param Role $role
     */
    public function setRole(Role $role): void
    {
        $this->role = $role;
    }

    /**
     * @return int[]
     */
    public function getProjectsId(): array
    {
        return $this->projectsId;
    }

    /**
     * @param int[] $projectsId
     */
    public function setProjectsId(array $projectsId): void
    {
        $this->projectsId = $projectsId;
    }

    /**
     * @return null|string
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param null|string $image
     */
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return null|string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param null|string $token
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string|null $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return null|int
     */
    public function getOrganisationId(): ?int
    {
        return $this->organisationId;
    }

    /**
     * @param null|int $organisationId
     */
    public function setOrganisationId(?int $organisationId): void
    {
        $this->organisationId = $organisationId;
    }

    /**
     * @return null|Company
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * @param null|Company $company
     */
    public function setCompany(?Company $company): void
    {
        $this->company = $company;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'firstName' => $this->getFirstName(),
            'insertion' => $this->getInsertion(),
            'lastName' => $this->getLastName(),
            'email' => $this->getEmail(),
            'function' => $this->getFunction(),
            'role' => $this->getRole(),
            'projectsId' => $this->getProjectsId(),
            'hasImage' => $this->getImage() !== null,
            'phoneNumber' => $this->getPhoneNumber(),
            'organisationId' => $this->getOrganisationId(),
            'company' => $this->getCompany(),
        ];
    }

}
