<?php

$router->middleware('SessionMiddleware');

$router->register('home', '/', 'GET', 'IndexCtrl');
