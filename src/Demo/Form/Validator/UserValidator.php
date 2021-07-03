<?php

namespace Platine\Framework\Demo\Form\Validator;

use Platine\Framework\Demo\Form\Param\UserParam;
use Platine\Validator\Rule\AlphaDash;
use Platine\Validator\Rule\Max;
use Platine\Validator\Rule\Min;
use Platine\Validator\Rule\MinLength;
use Platine\Validator\Rule\NotEmpty;
use Platine\Validator\Rule\Number;
use Platine\Validator\Validator;

class UserValidator extends AbstractValidator
{

    protected UserParam $param;

    /**
     * Create new instance
     * @param UserParam $param
     * @param Validator|null $validator
     */
    public function __construct(UserParam $param, ?Validator $validator = null)
    {
        parent::__construct($validator);
        $this->param = $param;
    }

    public function setData(): void
    {
        $this->addData('username', $this->param->getUsername());
        $this->addData('lastname', $this->param->getLastname());
        $this->addData('firstname', $this->param->getFirstname());
        $this->addData('age', $this->param->getAge());
        $this->addData('password', $this->param->getPassword());
    }

    public function setRules(): void
    {
        $this->validator->addRules('username', [
           new NotEmpty(),
           new MinLength(3),
            new AlphaDash()
        ]);

        $this->validator->addRules('lastname', [
           new NotEmpty(),
           new MinLength(3)
        ]);

        $this->validator->addRules('firstname', [
           new NotEmpty(),
           new MinLength(3)
        ]);

        $this->validator->addRules('age', [
           new NotEmpty(),
           new Number(),
           new Min(0),
           new Max(100),
        ]);

        $this->validator->addRules('password', [
           new MinLength(5),
        ]);
    }
}
