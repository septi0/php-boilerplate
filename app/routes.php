<?php

$router->middleware(SessionMiddleware::class);

$router->register('home', '/', 'GET', IndexCtrl::class);
