<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require __DIR__ . '/vendor/autoload.php';
require 'database.php'; 

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$container = new DI\Container();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->options('/{routes:.+}', function (ResponseInterface $response) {
    return $response;
});

$app->get('/api/users', function (ServerRequestInterface $request, ResponseInterface $response) use ($pdo) {
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response->getBody()->write(json_encode($users));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/posts', function (ServerRequestInterface $request, ResponseInterface $response) use ($pdo) {
    $stmt = $pdo->query("SELECT * FROM posts");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response->getBody()->write(json_encode($posts));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/users/{id}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($pdo) {
    $id = $args['id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $response->getBody()->write(json_encode($user));
    } else {
        $response = $response->withStatus(404);
        $response->getBody()->write(json_encode(['error' => 'User not found']));
    }
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/posts/{id}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($pdo) {
    $id = $args['id'];
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($post) {
        $response->getBody()->write(json_encode($post));
    } else {
        $response = $response->withStatus(404);
        $response->getBody()->write(json_encode(['error' => 'Post not found']));
    }
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
