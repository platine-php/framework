<?php

/**
 * Platine Framework
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Framework
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Platine\Framework\Http\Action;

use Platine\Framework\Config\AppDatabaseConfig;
use Platine\Framework\Enum\YesNoStatus;
use Platine\Framework\Helper\ActionHelper;
use Platine\Http\ResponseInterface;

/**
* @class BaseConfigurationAction
* @package Platine\Framework\Http\Action
*/
abstract class BaseConfigurationAction extends BaseAction
{
    /**
    * {@inheritdoc}
    */
    public function __construct(
        ActionHelper $actionHelper,
        protected AppDatabaseConfig $dbConfig,
    ) {
        parent::__construct($actionHelper);
    }

    /**
    * {@inheritdoc}
    */
    public function respond(): ResponseInterface
    {
        $this->setView($this->getViewName());
        $param = $this->param;
        $request = $this->request;

        $paramName = $this->getParamName();
        $validatorName = $this->getValidatorName();

        if ($request->getMethod() === 'GET') {
            $configToParam = (new $paramName())->fromConfig($this->dbConfig);
            $this->addContext('param', $configToParam);

            return $this->viewResponse();
        }

        $formParam = new $paramName($param->posts());
        $this->addContext('param', $formParam);

        $validator = new $validatorName($formParam, $this->lang);
        if ($validator->validate() === false) {
            $this->addContext('errors', $validator->getErrors());

            return $this->viewResponse();
        }

        // Save the configuration
        $this->save();

        $this->flash->setSuccess($this->lang->tr('Configuration saved successfully'));

        return $this->redirect($this->getRouteName());
    }

    /**
     * Save the configuration
     * @return void
     */
    protected function save(): void
    {
        $params = $this->getParamDefinitions();
        $moduleName = $this->getModuleName();
        //TODO get all configuration in order to know if need insert/update
        $this->dbConfig->get(sprintf('%s.', $moduleName));

        foreach ($params as $name => $arr) {
            $postKey = $arr['key'] ?? $name;
            $value = $this->param->post($postKey);
            // if is array and empty
            if ($arr['type'] === 'array' && empty($value)) {
                $value = [];
            } elseif ($arr['type'] === 'callable' && is_callable($arr['callable'])) {
                $value = call_user_func_array($arr['callable'], [$this->dbConfig]);
                if ($value !== null) {
                    $arr['type'] = gettype($value);
                }
            }

            if (is_array($value)) {
                $value = serialize($value);
            }
            $key = sprintf('%s.%s', $moduleName, $name);
            if ($this->dbConfig->has($key)) {
                $entity = $this->dbConfig->getLoader()->loadConfig([
                    'module' => $moduleName,
                    'name' => $name,
                ]);

                if ($entity !== null) {
                    $entity->name = $name;
                    $entity->env = null;
                    $entity->module = $moduleName;
                    $entity->value = $value;
                    $entity->status = YesNoStatus::YES;
                    $entity->type = $arr['type'];
                    $entity->comment = $arr['comment'];

                    $this->dbConfig->getLoader()->updateConfig($entity);
                }
            } else {
                $this->dbConfig->getLoader()->insertConfig([
                    'name' => $name,
                    'env' => null,
                    'module' => $moduleName,
                    'value' => $value,
                    'status' => YesNoStatus::YES,
                    'type' => $arr['type'],
                    'comment' => $arr['comment'],
                ]);
            }
        }
    }

    /**
     * Return the view name
     * @return string
     */
    protected function getViewName(): string
    {
        return '';
    }

    /**
     * Return the route name
     * @return string
     */
    protected function getRouteName(): string
    {
        return '';
    }

    /**
     * Return the form parameter class name
     * @return class-string<\Platine\Framework\Form\Param\BaseParam>
     */
    abstract protected function getParamName(): string;

    /**
     * Return the validation class name
     * @return class-string<\Platine\Framework\Form\Validator\AbstractValidator>
     */
    abstract protected function getValidatorName(): string;

    /**
     * Return the parameters definition
     * @return array<string, array<string, mixed>>
     */
    abstract protected function getParamDefinitions(): array;

    /**
     * Return the configuration module name
     * @return string
     */
    abstract protected function getModuleName(): string;
}
