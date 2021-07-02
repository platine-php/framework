<?php

namespace Platine\Framework\Demo\Form\Validator;

use Platine\Validator\Validator;

abstract class AbstractValidator
{
    /**
     * The validator instance
     * @var Validator
     */
    protected Validator $validator;

    /**
     * The data to validate
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Create new instance
     * @param Validator|null $validator
     */
    public function __construct(?Validator $validator = null)
    {
        $this->validator = $validator ?? new Validator();
    }


    public function validate(): bool
    {
        $this->setRules();
        $this->setData();
        $this->validator->setData($this->data);

        return $this->validator->validate();
    }

    public function isValid(): bool
    {
        return $this->validator->isValid();
    }

    /**
     * Return the validations errors
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->validator->getErrors();
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addData(string $name, $value): self
    {

        $this->data[$name] = $value;

        return $this;
    }

    /**
     * Set the validation rules
     * @return void
     */
    abstract public function setRules(): void;

    /**
     * Set the validation data
     * @return void
     */
    abstract public function setData(): void;
}
