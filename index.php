<?php

define('TODO_PREFIX', 'todo_');
define('TODO_INDEX_KEY', TODO_PREFIX . 'index');
define('TODO_ITEM_PREFIX', TODO_PREFIX . 'item_');

require __DIR__ . '/vendor/autoload.php';

new \App\Api();