<?php
/**
 * You must run `composer install` order to generate autoloader
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use \Exception;
use \Silex\Application;
use \Silex\Provider\TwigServiceProvider;
use \Silex\Provider\SessionServiceProvider;
use \Symfony\Component\HttpFoundation\Response;
use \Instafeed\Tag;

/**
 * Application bootstrap
 */

// Silex
$app = new Application();
$app->register(
    new TwigServiceProvider(),
    array(
        'twig.path' => dirname(__DIR__) . '/view'
    )
);
$app->register(
    new SessionServiceProvider()
);

/**
 * Instafeed setup
 *
 * First parameter is your client id, second is secret key.
 *
 * Obtain those from: http://instagram.com/developer/clients/manage/.
 *
 * Your redirect Uri should reflect your domain with /callback prefix.
 *
 * It must also match callback URI you set within client manager.
 */
$instafeed = new Tag(
    'client_id',
    'secret_key'
);
$instafeed->setRedirectUri(
    'http://instafeed.domain.com/callback'
);

/**
 * Exception handler setup
 */
$app->error(
    function (Exception $exception, $code) {
        switch ($code) {
            case 404:
                $message = 'The requested page could not be found.';
                break;
            case 412:
                $message = $exception->getMessage();
                break;
            default:
                $message = 'We are sorry, but something went terribly wrong.';
        }

        return new Response($message);
    }
);

/**
 * Routing setup
 */
$app->get(
    '/',
    function () use ($app, $instafeed) {
        // Handle tokens for Instafeed
        $token = $app['session']->get('instafeed_token');
        if (!$token) {
            return $app->redirect(
                $instafeed->authorizeUri()
            );
        }

        $instafeed->setToken($token);

        return $app['twig']->render(
            'index.twig'
        );
    }
);

$app->get(
    '/tag/{name}',
    function ($name) use ($app, $instafeed) {
        // Handle tokens for Instafeed
        $token = $app['session']->get('instafeed_token');
        if (!$token) {
            return $app->redirect(
                $instafeed->authorizeUri()
            );
        }

        $instafeed->setToken($token);

        return $app['twig']->render(
            'tag.twig',
            array(
                'name' => $name,
                'tags' => $instafeed->recent($name),
            )
        );
    }
);

$app->get(
    '/callback',
    function () use ($app, $instafeed) {
        $error = $app['request']->get('error_reason');
        if ($error) {
            $app->abort(412, $error);
        }

        $code = $app['request']->get('code');
        if (!$code) {
            $app->abort(412, 'Instagram did not provide us with token, try again later.');
        }

        $token = $instafeed->token($code);
        if ($token) {
            $app['session']->set('instafeed_token', $token);
        }

        return $app->redirect('/');
    }
);

$app->run();