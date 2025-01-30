<?php

$router->middleware('SessionMiddleware');

$router->get('/', 'IndexCtrl');