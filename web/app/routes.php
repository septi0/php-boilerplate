<?php

$router->middleware('SessionMiddleware');

$router->get('home', '/', 'IndexCtrl');