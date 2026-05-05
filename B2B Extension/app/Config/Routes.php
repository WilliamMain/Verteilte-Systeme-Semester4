<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route - show products page
$routes->get('/', 'Products::index');

// Product routes
$routes->get('products', 'Products::index');
$routes->get('home', 'Products::index');

// Cart routes
$routes->get('cart', 'Cart::index');
$routes->get('warenkorb', 'Cart::index');
$routes->post('cart/checkout', 'Cart::checkout');

// Auth routes
$routes->get('login', 'Auth::login');
$routes->get('mitarbeiter', 'Auth::login');
$routes->get('mitarbeiter-login', 'Auth::login');
$routes->post('auth/loginProcess', 'Auth::loginProcess');
$routes->get('auth/logout', 'Auth::logout');

// Dashboard routes
$routes->match(['get', 'post'], 'mitarbeiter-dashboard', 'MitarbeiterDashboard::index');

// API Routes für B2C Integration (updated format)
$routes->group('api', function($routes) {
    $routes->post('create-order', 'Api::createOrder');
    $routes->get('test', 'Api::test');
    $routes->options('create-order', 'Api::createOrder');
});

$routes->post('api/check-stock', 'Api::checkStock');

//TEST Route
$routes->get('test-simple', function() {
    return 'Hello World';
});