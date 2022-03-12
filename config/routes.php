<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController::index');

Router::get('/oauth/authorize', [App\Controller\OauthController::class, 'authorize']);
Router::get('/oauth/login', [App\Controller\OauthController::class, 'login']);
Router::post('/oauth/login', [App\Controller\OauthController::class, 'login']);

Router::post('/report/item', [App\Controller\ReportController::class, 'addItem']);
Router::get('/report', [App\Controller\ReportController::class, 'index']);
