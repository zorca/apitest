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
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->parameters = $_REQUEST;
        $this->migration();
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
            ['title' => 'First Item', 'done' => false],
            ['title' => 'Second Item', 'done' => true],
            ['title' => 'Third Item', 'done' => false],
        ];
        $count = count($migrateData);
        echo $this->response($count);
    }

    protected function indexAction()
    {
        return $this->response(['method' => $method = $this->method, 'data' => $this->db->get]);
    }

    protected function viewAction($id)
    {
        if (! $this->db->exists($id)) {
            return $this->response('Item with id=' . $id . ' not found', 404);
        }
        return $this->response(['method' => $method = $this->method, 'data' =>json_decode($id, true)]);
    }

    protected function createAction($title, $done)
    {
        $this->db->set($this->index, json_encode(['title' => $title, 'done' => $done ? $done : false]));
        return $this->response(['method' => $method = $this->method]);
    }

    protected function updateAction($id, $title, $done)
    {
        $this->db->set($id, json_encode(['title' => $title, 'done' => $done ? $done : false]));
        return $this->response(['method' => $method = $this->method]);
    }

    protected function deleteAction($id)
    {
        if (! $this->db->exists($id)) {
            return $this->response('Item not found', 404);
        }
        $this->db->del($id);
        return $this->response(['method' => $this->method]);
    }

    protected function response($data, $status = 500)
    {
        $statusText = [
            200 => 'Ok',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error'
        ];
        header('Content-type: application/json');
        header('HTTP/1.1 ' . $status . ' ' . $statusText[$status]);
        return json_encode($data);
    }
}