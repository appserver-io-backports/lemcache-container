<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dittertp
 * Date: 19.10.13
 * Time: 13:37
 * To change this template use File | Settings | File Templates.
 */


class AbstractMemCache
{
    protected $newLine="\r\n";
    protected $response;
    protected $state = "close";

    protected $action = FALSE;
    protected $flags;
    protected $expTime;
    protected $bytes;
    protected $data = "";
    protected $key;

    public function reset()
    {
        $this->newLine="\r\n";
        $this->response = "";
        $this->state = "";
        $this->action = FALSE;
        $this->flags = NULL;
        $this->expTime = NULL;
        $this->bytes = NULL;
        $this->data = "";
        $this->key = "";
    }


    public function getResponse()
    {
        return $this->response;
    }

    protected function setResponse($response)
    {
        $this->response = $response;
    }

    public function getState()
    {
        return $this->state;
    }

    protected function setState($var)
    {
        $this->state = $var;
    }

    protected function setFlags($flags)
    {
        $this->flags = $flags;
    }

    protected function getFlags()
    {
        return $this->flags;
    }

    protected function setBytes($bytes)
    {
        $this->bytes = $bytes;
    }

    protected function getBytes()
    {
        return $this->bytes;
    }

    protected function setExpTime($ExpTime)
    {
        $this->expTime = $ExpTime;
    }

    protected function getExpTime()
    {
        return $this->expTime;
    }

    protected function getNewLine()
    {
        return $this->newLine;
    }

    protected function setData($data)
    {
        $this->data .= $data;
    }

    protected function getData()
    {
        return $this->data;
    }

    protected function setKey($key)
    {
        $this->key = $key;
    }

    protected function getKey()
    {
        return $this->key;
    }

    protected function getStore()
    {
        return $this->store;
    }

    protected function isAction()
    {
        return $this->action;
    }

    protected function SetIsAction()
    {
        $this->action = true;
    }

}

class MemCache extends AbstractMemCache
{
    public $store;

    public $mutex;

    public function __construct($store, $mutex)
    {
        $this->store = $store;
        $this->mutex = $mutex;
    }

    public function push($request)
    {
        if ($this->isAction()) {
            $this->pushData($request);
        } else {
            if (($var = $this->parseRequest($request)) !== FALSE) {
                switch ($var[0]) {
                    case "set":
                        $this->setAction($var);
                        break;
                    case "get":
                        $this->GetAction($var);
                        break;
                    case "delete":
                        $this->DeleteAction($var);
                        break;
                    case "quit":
                        $this->QuitAction($var);
                        break;
                    default:
                        $this->setState("reset");
                        $this->setResponse("ERROR");
                        break;
                }
                unset($var);
            } else {

                $this->setState("reset");
                $this->setResponse("ERROR");
            }
        }
    }

    protected function parseRequest($request)
    {
        if (!$request OR $request == "\n" OR $request == "\r\n") { return FALSE; }

        //strip header from requesst (in case of a set request e.g)
        $header = strstr($request, $this->getNewLine(), TRUE);

        $data = substr(strstr($request, $this->getNewLine()),strlen($this->getNewLine()));
        // try to read action
        $var = explode(" ", trim($header));
        $var['data'] = $data;
        return $var;
    }

    protected function SetAction($request)
    {
        $this->setIsAction(TRUE);
        try {
                // set Action to "set"
                $this->key = $request[1];

                // validate Flag Value
                if (is_numeric($request[2])) {
                    $this->setFlags($request[2]);
                } else {
                    throw new \Exception("CLIENT_ERROR bad command line format");
                }

                // validate Expiretime value
                if (is_numeric($request[3])) {
                    $this->setExpTime($request[3]);
                } else {
                    throw new \Exception("CLIENT_ERROR bad data chunk");
                }

                // validate data-length in bytes
                if (is_numeric($request[4])) {
                    $this->setBytes($request[4]);
                } else {
                    throw new \Exception("CLIENT_ERROR bad data chunk");
                }

                $this->setState("resume");
                $this->setResponse("");

                if ($request['data']) {

                    $this->pushData($request['data']);
                }

        } catch (\Exception $e) {
            //$this->setState("resume");
            $this->setResponse("");
        }

        //$this->setState("reset");
        //$this->setResponse("STORED");

    }

    protected function GetAction($request)
    {

            $key = $request[1];
            // read response from Store
            $response = $this->StoreGet($key);
            // api object should deleted after sending response to client
            $this->setState("reset");
            // set Response for client communication
            $this->setResponse($response);

        //$this->setState("reset");
        //$this->setResponse("VALUE key 0 6\r\nkanban\r\nEND");

    }

    protected function DeleteAction($request)
    {
        $key = $request[1];
        // read response from Store
        $response = $this->StoreDelete($key);
        // api object should deleted after sending response to client
        $this->setState("reset");
        // set Response for client communication
        $this->setResponse($response);
    }

    protected function QuitAction($request)
    {
        // api object should deleted after sending response to client
        $this->setState("close");

        // set Response for client communication
        $this->setResponse("");
    }

    protected function pushData($data)
    {
        if ($data == $this->getNewline() && strlen($this->getData()) == $this->getBytes()) {
            $this->StoreSet($this->getKey(), $this->getFlags(), $this->getExpTime(), $this->getBytes(), $this->getData());
            $this->setState("reset");
            $this->setResponse("STORED");
        } else {
            if ($data != $this->getNewLine()) {$data = rtrim($data);}
            $this->setData($data);
            if (strlen($this->getData()) == $this->getBytes()) {
                $this->StoreSet($this->getKey(), $this->getFlags(), $this->getExpTime(), $this->getBytes(), $this->getData());
                $this->setState("reset");
                $this->setResponse("STORED");
            } elseif (strlen($this->getData()) > $this->getBytes()) {
                $this->setState("reset");
                $this->setResponse("CLIENT_ERROR bad data chunk{$this->getNewLine()}ERROR");
            }
        }
    }


    protected function StoreGet($key)
    {
        $result = "";

        if ($this->store[$key]) {

            \Mutex::lock($this->mutex);
            $s = $this->store[$key];
            \Mutex::unlock($this->mutex);

            $result = "VALUE ".$s['key']." ";
            $result .= $s['flags']." ";
            $result .= $s['bytes'].$this->getNewLine();
            $result .= $s['value'].$this->getNewLine();
        }
        $result .= "END";
        return $result;
    }

    protected function StoreSet($key, $flags, $exptime, $bytes, $value)
    {
        $ar['key'] = $key;
        $ar['flags'] = $flags;
        $ar['exptime'] = $exptime;
        $ar['bytes'] = $bytes;
        $ar['value'] = $value;

        \Mutex::lock($this->mutex);
        $this->store[$key] = $ar;
        \Mutex::unlock($this->mutex);

        return TRUE;
    }

    protected function StoreDelete($key)
    {
        if ($this->store['key']) {

            \Mutex::lock($this->mutex);
            unset($this->store['key']);
            \Mutex::unlock($this->mutex);

            $result = "DELETED";
        } else {
            $result = "NOT_FOUND";
        }
        return $result;
    }




}



class Store extends Stackable
{
    public function run(){}
}

class Daemon extends Thread
{
    public function __construct(){
    }

    public function run()
    {

        $store = New Store();
        $mutex = Mutex::create(false);

        $socket = stream_socket_server('tcp://0.0.0.0:11210', $errno, $errstr, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN);
        stream_set_blocking ( $socket , 1);
        /*
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR,1);
        socket_set_block($socket);
        //socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 0, "usec" =>100));
        socket_bind($socket,"0.0.0.0",11210);
        socket_listen($socket,1024);
        */
        for($i=0;$i<=8;$i++)
        {
            $worker[] = new WorkerThread($socket, $store, $mutex);
        }
    }
}

class WorkerThread extends Thread
{
    protected $socket;
    protected $store;
    protected $mutex;

    protected $client;

    public function __construct($socket, $store, $mutex)
    {
        $this->socket = $socket;
        $this->store = $store;
        $this->mutex = $mutex;


        $this->start();
    }


    public function run()
    {
        while (true) {
            $api = new Memcache($this->store, $this->mutex);
            // accept client connection and process the request

            if ($this->client = stream_socket_accept($this->socket)) {
                //$req = new Request($client, $this->store, $this->mutex);

                $buffer = "";
                while(true)
                {
                    while ($buffer .= fread($this->client, 4096)) {
                        if (false !== strpos($buffer, "\r\n")) {
                            BREAK;
                        }
                    }

                    $api->push($buffer);

                    fwrite($this->client, $api->getResponse()."\r\n");

                    switch ($api->getState()) {
                        case "resume";
                            break;
                        case "reset";
                            $api->reset();
                            $buffer = "";
                            break;
                        case "close":
                            $api->reset();
                            $buffer = "";
                            stream_socket_shutdown($this->client,STREAM_SHUT_RDWR);
                            #socket_shutdown($this->client, 2);
                            #socket_close($this->client);
                            break 2;
                        default:
                            break;
                    }
                }
            }
        }
    }
}

class Request extends Thread
{
    protected $client;
    protected $store;
    protected $mutex;

    public function __construct($client, $store, $mutex)
    {
        $this->client = $client;
        $this->store = $store;
        $this->mutex = $mutex;

        $this->start();
    }

     public function run()
     {
         $buffer = "";
        while(true)
        {
            while ($buffer .= socket_read($this->client, 4096)) {
                if (false !== strpos($buffer, "\r\n")) {
                    BREAK;
               }
            }
            $api = new Memcache($this->store, $this->mutex);
            $api->push($buffer);

            socket_write($this->client, $api->getResponse()."\r\n");

            switch ($api->getState()) {
                case "resume";
                    break;
                case "reset";
                    $api->reset();
                    $buffer = "";
                    break;
                case "close":
                    $api->reset();
                    $buffer = "";
                    socket_shutdown($this->client, 2);
                    socket_close($this->client);
                    break 2;
                default:
                    break;
            }
        }
     }
}




class server
{

    public function start()
    {
        $daemon = New Daemon();

        $daemon->start();

    }
}
$s = new Server();
$s->start();
