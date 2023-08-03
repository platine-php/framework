<?php

/**
 * Platine PHP
 *
 * Platine PHP is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine PHP
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

/**
 *  @file OAuth2ServiceProvider.php
 *
 *  The OAuth2 service provider class
 *
 *  @package    Platine\Framework\Service\Provider
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Service\Provider;

use Platine\Config\Config;
use Platine\Container\ContainerInterface;
use Platine\Framework\OAuth2\Handler\AccessTokenRequestHandler;
use Platine\Framework\OAuth2\Handler\AuthorizationRequestHandler;
use Platine\Framework\OAuth2\Handler\TokenRevocationRequestHandler;
use Platine\Framework\OAuth2\Middleware\OauthResourceMiddleware;
use Platine\Framework\OAuth2\Repository\AccessTokenRepository;
use Platine\Framework\OAuth2\Repository\AuthorizationCodeRepository;
use Platine\Framework\OAuth2\Repository\ClientRepository;
use Platine\Framework\OAuth2\Repository\RefreshTokenRepository;
use Platine\Framework\OAuth2\Repository\ScopeRepository;
use Platine\Framework\OAuth2\User\UserAuthentication;
use Platine\Framework\Service\ServiceProvider;
use Platine\OAuth2\AuthorizationServer;
use Platine\OAuth2\AuthorizationServerInterface;
use Platine\OAuth2\Configuration;
use Platine\OAuth2\Entity\UserAuthenticationInterface;
use Platine\OAuth2\Grant\AuthorizationGrant;
use Platine\OAuth2\Grant\ClientCredentialsGrant;
use Platine\OAuth2\Grant\PasswordGrant;
use Platine\OAuth2\Grant\RefreshTokenGrant;
use Platine\OAuth2\Middleware\AuthorizationRequestMiddleware;
use Platine\OAuth2\Middleware\ResourceServerMiddleware;
use Platine\OAuth2\Middleware\RevocationRequestMiddleware;
use Platine\OAuth2\Middleware\TokenRequestMiddleware;
use Platine\OAuth2\Repository\AccessTokenRepositoryInterface;
use Platine\OAuth2\Repository\AuthorizationCodeRepositoryInterface;
use Platine\OAuth2\Repository\ClientRepositoryInterface;
use Platine\OAuth2\Repository\RefreshTokenRepositoryInterface;
use Platine\OAuth2\Repository\ScopeRepositoryInterface;
use Platine\OAuth2\ResourceServer;
use Platine\OAuth2\ResourceServerInterface;
use Platine\OAuth2\Service\AccessTokenService;
use Platine\OAuth2\Service\AuthorizationCodeService;
use Platine\OAuth2\Service\ClientService;
use Platine\OAuth2\Service\RefreshTokenService;
use Platine\OAuth2\Service\ScopeService;
use Platine\Route\Router;




/**
 * @class OAuth2ServiceProvider
 * @package Platine\App\Provider
 */
class OAuth2ServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {

        // User authentication
        $this->app->bind(UserAuthenticationInterface::class, UserAuthentication::class);

        // Configuration
        $this->app->bind(Configuration::class, function (ContainerInterface $app) {
            return new Configuration($app->get(Config::class)->get('oauth2', []));
        });

        // Grants
        $this->app->bind(AuthorizationGrant::class);
        $this->app->bind(ClientCredentialsGrant::class);
        $this->app->bind(RefreshTokenGrant::class);
        $this->app->bind(PasswordGrant::class);

        // Repositories
        $this->app->bind(AccessTokenRepositoryInterface::class, AccessTokenRepository::class);
        $this->app->bind(ScopeRepositoryInterface::class, ScopeRepository::class);
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(RefreshTokenRepositoryInterface::class, RefreshTokenRepository::class);
        $this->app->bind(AuthorizationCodeRepositoryInterface::class, AuthorizationCodeRepository::class);

        // Services
        $this->app->bind(ScopeService::class);
        $this->app->bind(AccessTokenService::class);
        $this->app->bind(ClientService::class);
        $this->app->bind(RefreshTokenService::class);
        $this->app->bind(AuthorizationCodeService::class);

        // Servers
        /** @var Configuration $cfg */
        $cfg = $this->app->get(Configuration::class);
        $grants = $cfg->getGrants();
        $serverGrants = [];
        foreach ($grants as $grant) {
            if ($this->app->has($grant)) {
                $serverGrants[] = $this->app->get($grant);
            } else {
                $serverGrants[] = new $grant();
            }
        }
        $this->app->bind(ResourceServerInterface::class, ResourceServer::class);
        $this->app->bind(AuthorizationServerInterface::class, AuthorizationServer::class, [
            'grants' => $serverGrants
        ]);

        // Middlewares
        $this->app->bind(ResourceServerMiddleware::class);
        $this->app->bind(AuthorizationRequestMiddleware::class);
        $this->app->bind(TokenRequestMiddleware::class);
        $this->app->bind(RevocationRequestMiddleware::class);
        $this->app->bind(OauthResourceMiddleware::class);

        // Handlers
        $this->app->bind(AccessTokenRequestHandler::class);
        $this->app->bind(AuthorizationRequestHandler::class);
        $this->app->bind(TokenRevocationRequestHandler::class);
    }

    /**
     * {@inheritdoc}
     */
    public function addRoutes(Router $router): void
    {
        $router->group('/oauth2', function (Router $router) {
            $router->post('/token', AccessTokenRequestHandler::class, 'oauth2_access_token');
            $router->add('/authorize', AuthorizationRequestHandler::class, ['GET', 'POST'], 'oauth2_authorization_code');
            $router->post('/revocation', TokenRevocationRequestHandler::class, 'oauth2_revoke_access_token');
        });
    }
}
