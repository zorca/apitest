<?php

class API
{

    protected $method;
    protected $parameters;
    protected $data;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->parameters = $_REQUEST;
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
                echo $this->viewAction();
                break;
            case 'PUT':
                $this->method = 'PUT';
                echo $this->createAction();
                break;
            case 'DELETE':
                $this->method = 'DELETE';
                echo $this->deleteAction();
                break;
            default:
                break;
        }
    }

    protected function indexAction()
    {
        $method = $this->method;

        return json_encode(['method' => $method, 'data' => $this->data]);
    }

    protected function viewAction($id)
    {
        $method = $this->method;
        return json_encode(['method' => $method, 'data' =>json_decode($this->data['id'], true)]);
    }

    protected function createAction($title, $done)
    {
        $method = $this->method;
        return json_encode(['method' => $method]);
    }

    protected function updateAction($id)
    {
        $method = $this->method;
        return json_encode(['method' => $method]);
    }

    protected function deleteAction($id)
    {
        $method = $this->method;
        return json_encode(['method' => $method]);
    }
}