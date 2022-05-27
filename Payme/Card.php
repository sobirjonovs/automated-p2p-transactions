<?php

namespace App\Payme;

class Card extends Formatter
{
    /**
     * Defines card id
     * @property $id
     */
    private string $id;
    /**
     * Defines card name
     * @property $name
     */
    private string $name;
    /**
     * Defines card number
     * @property $number
     */
    private string $number;
    /**
     * Defines card expire date,
     * i.e 9912 (99 - year, 12 - month)
     * @property $expire
     */
    private int $expire;
    /**
     * Defines card status
     * @property $isActive
     */
    private bool $isActive;
    /**
     * Defines card owner full name
     * @property $owner
     */
    private string $owner;
    /**
     * Defines card balance
     * @property $balance
     */
    private int $balance;
    /**
     * Defines card is main
     * @property $isMain
     */
    private bool $isMain;
    /**
     * Defines card date
     * @property $date
     */
    private int $date;

    /**
     * Card constructor.
     * @param int $id
     * @param string $name
     * @param string $number
     * @param int $expire
     * @param bool $isActive
     * @param string $owner
     * @param int $balance
     * @param bool $isMain
     * @param int $date
     */
    public function __construct(
        string $id,
        string $name,
        string $number,
        int $expire,
        bool $isActive,
        string $owner,
        int $balance,
        bool $isMain,
        int $date
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->number = $number;
        $this->expire = $expire;
        $this->isActive = $isActive;
        $this->owner = $owner;
        $this->balance = $balance;
        $this->isMain = $isMain;
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @return int
     */
    public function getExpire(): int
    {
        return $this->expire;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getBalance(): string
    {
        return $this->formatMoney($this->balance);
    }

    /**
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->isMain;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->formatDate($this->date);
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $number
     */
    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    /**
     * @param int $expire
     */
    public function setExpire(int $expire): void
    {
        $this->expire = $expire;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @param string $owner
     */
    public function setOwner(string $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @param int $balance
     */
    public function setBalance(int $balance): void
    {
        $this->balance = $balance;
    }

    /**
     * @param bool $isMain
     */
    public function setIsMain(bool $isMain): void
    {
        $this->isMain = $isMain;
    }

    /**
     * @param int $date
     */
    public function setDate(int $date): void
    {
        $this->date = $date;
    }
}