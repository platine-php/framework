<?php

namespace Platine\Framework\Demo\Form\Validator;

use Platine\Framework\Demo\Form\Param\AuthParam;
use Platine\Validator\Rule\MinLength;
use Platine\Validator\Rule\NotEmpty;
use Platine\Validator\Validator;

class AuthValidator extends AbstractValidator
{

    protected AuthParam $param;

    /**
     * Create new instance
     * @param AuthParam $param
     * @param Validator|null $validator
     */
    public function __construct(AuthParam $param, ?Validator $validator = null)
    {
        parent::__construct($validator);
        $this->param = $param;
    }

    public function setData(): void
    {
        $this->addData('username', $this->param->getUsername());
    }

    public function setRules(): void
    {
        $this->validator->addRules('username', [
           new NotEmpty(),
            new MinLength(3)
        ]);
    }
}
