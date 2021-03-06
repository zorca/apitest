<?php

namespace App;

use Predis\Client;

class Api
{

    protected $method;
    protected $parameters;
    protected $db;
    protected $index;

    public function __construct()
    {
        $this->db = new Client();
        $this->method = array_key_exists('REQUEST_METHOD', $_SERVER) ? $_SERVER['REQUEST_METHOD'] : null;
        $this->parameters = $_REQUEST;
        if (! $this->db->exists(TODO_INDEX_KEY)) {
            $this->migration();
        }
        $this->index = (int) $this->db->get(TODO_INDEX_KEY);
        switch($this->method) {
            case 'GET':
                $this->method = 'GET';
                if (array_key_exists('id', $this->parameters)) {
                    echo $this->viewAction($this->parameters['id']);
                    break;
                }
                echo $this->indexAction();
                break;
            case 'POST':
                $this->method = 'POST';
                echo $this->createAction($this->parameters['title'], $this->parameters['done']);
                break;
            case 'PUT':
                $this->method = 'PUT';
                echo $this->updateAction($this->parameters['id'], $this->parameters['title'], $this->parameters['done']);
                break;
            case 'DELETE':
                $this->method = 'DELETE';
                echo $this->deleteAction($this->parameters['id']);
                break;
            default:
                echo $this->response('Method No Found', 405);
                break;
        }
    }

    protected function migration()
    {
        $migrateData = [
            1 => ['title' => 'First Item', 'done' => false],
            2 => ['title' => 'Second Item', 'done' => true],
            3 => ['title' => 'Third Item', 'done' => false],
        ];
        $migrateCount = count($migrateData);
        foreach ($migrateData as $index => $item) {
            $this->db->set(TODO_ITEM_PREFIX.$index, json_encode($item));
        }
        $this->db->set(TODO_INDEX_KEY, $migrateCount);
    }

    protected function indexAction()
    {
        $items = [];
        $keys = $this->db->keys(TODO_ITEM_PREFIX.'*');
        natsort($keys);
        foreach ($keys as $key) {
            $items[(int) str_replace(TODO_ITEM_PREFIX, '', $key)] = json_decode($this->db->get($key));
        }
        return $this->response(['method' => $this->method, 'data' => $items], 200);
    }

    protected function viewAction($id)
    {
        if (! $this->db->exists(TODO_ITEM_PREFIX.$id)) {
            return $this->response('Item with id=' . $id . ' not found', 404);
        }
        return $this->response(['method' => $method = $this->method, 'data' => json_decode($this->db->get(TODO_ITEM_PREFIX.$id), true)], 200);
    }

    protected function createAction($title, $done)
    {
        $this->index++;
        $this->db->set(TODO_INDEX_KEY, $this->index);
        $this->db->set(TODO_ITEM_PREFIX.$this->index, json_encode(['title' => $title, 'done' => $done ? $done : false], JSON_UNESCAPED_UNICODE));
        return $this->response('Item with id=' . $this->index . ' created', 200);
    }

    protected function updateAction($id, $title, $done)
    {
        if (! $this->db->exists(TODO_ITEM_PREFIX.$id)) {
            return $this->response('Item with id=' . $id . ' not found', 404);
        }
        $this->db->set(TODO_ITEM_PREFIX.$id, json_encode(['title' => $title, 'done' => $done ? $done : false], JSON_UNESCAPED_UNICODE));
        return $this->response(['method' => $method = $this->method, 'data' => json_decode($this->db->get(TODO_ITEM_PREFIX.$id), true)], 200);
    }

    protected function deleteAction($id)
    {
        if (! $this->db->exists(TODO_ITEM_PREFIX.$id)) {
            return $this->response('Item with id=' . $id . ' not found', 404);
        }
        $this->db->del([TODO_ITEM_PREFIX.$id]);
        return $this->response(['method' => $this->method, 'message' => 'Item removed'], 200);
    }

    protected function response($data, $status = 500)
    {
        $statusText = [
            200 => 'Ok',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error'
        ];
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header('Content-type: application/json');
        header('HTTP/1.1 ' . $status . ' ' . $statusText[$status]);
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}