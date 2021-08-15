<?php

namespace App\Payme;

class Account
{
    public string $name;
    public string $title;
    public string $value;

    /**
     * Account constructor.
     * @param string $name
     * @param string $title
     * @param string $value
     */
    public function __construct(string $name, string $title, string $value)
    {
        $this->name = $name;
        $this->title = $title;
        $this->value = $value;
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
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}