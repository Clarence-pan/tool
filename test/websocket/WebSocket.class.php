<?php

// Usage: $master=new WebSocket("localhost",12345);

class WebSocket {
    var $master;
    var $sockets = array();
    var $users = array();
    var $debug = false;

    function __construct($address, $port) {
        error_reporting(E_ALL);
        set_time_limit(0);
        ob_implicit_flush();

        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create() failed");
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1) or die("socket_option() failed");
        socket_bind($this->master, $address, $port) or die("socket_bind() failed");
        socket_listen($this->master, 20) or die("socket_listen() failed");
        $this->sockets[] = $this->master;
        $this->say("Server Started : " . date('Y-m-d H:i:s'));
        $this->say("Listening on   : " . $address . " port " . $port);
        $this->say("Master socket  : " . $this->master . "\n");

        while (true) {
            $changed = $this->sockets;
            socket_select($changed, $write = null, $except = null, null);
            foreach ($changed as $socket) {
                if ($socket == $this->master) {
                    $client = socket_accept($this->master);
                    if ($client < 0) {
                        $this->log("socket_accept() failed");
                        continue;
                    } else {
                        $this->connect($client);
                    }
                } else {
                    $bytes = @socket_recv($socket, $buffer, 2048, 0);
                    if ($bytes == 0) {
                        $this->disconnect($socket);
                    } else {
                        $user = $this->getuserbysocket($socket);
                        if (!$user->handshake) {
                            $this->doHandshake($user, $buffer);
                        } else {
                            $this->process($user, $this->unwrap($buffer));
                        }
                    }
                }
            }
        }
    }

    function process($user, $msg) {
        /* Extend and modify this method to suit your needs */
        /* Basic usage is to echo incoming messages back to client */
        $this->send($user->socket, $msg);
    }

    function send($client, $msg) {
        $this->say("> " . $msg);
        $msg = $this->wrap($msg);
        socket_write($client, $msg, strlen($msg));
        $this->say("! " . strlen($msg));
    }

    function connect($socket) {
        $user = new User();
        $user->id = uniqid();
        $user->socket = $socket;
        array_push($this->users, $user);
        array_push($this->sockets, $socket);
        $this->log($socket . " CONNECTED!");
        $this->log(date("d/n/Y ") . "at " . date("H:i:s T"));
    }

    function disconnect($socket) {
        $found = null;
        $n = count($this->users);
        for ($i = 0; $i < $n; $i++) {
            if ($this->users[$i]->socket == $socket) {
                $found = $i;
                break;
            }
        }
        if (!is_null($found)) {
            array_splice($this->users, $found, 1);
        }
        $index = array_search($socket, $this->sockets);
        socket_close($socket);
        $this->log($socket . " DISCONNECTED!");
        if ($index >= 0) {
            array_splice($this->sockets, $index, 1);
        }
    }

    function doHandshake(User $user, $buffer) {
        $this->log("\nRequesting handshake...");
        $this->log($buffer);
        $header = $this->parseHeader($buffer);
        $this->log("Handshaking...");
        $responseHeaderAccept = base64_encode(sha1(($header['Sec-WebSocket-Key']) . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        $upgrade =  "HTTP/1.1 101 WebSocket Protocol Handshake\r\n" .
                    "Upgrade: WebSocket\r\n" .
                    "Connection: Upgrade\r\n" .
                    "Sec-WebSocket-Origin: " . $header['URI'] . "\r\n" .
                    "Sec-WebSocket-Location: " . $header['URI'] . "\r\n" .
                    "Sec-WebSocket-Accept: "  . $responseHeaderAccept . "\r\n".
                    "\r\n" ;
        $this->writeResponse($user, $upgrade);
        $user->handshake = true;
        $this->log($upgrade);
        $this->log("Done handshaking...");

        return true;
    }

    /**
     * 写响应
     * @param User $user
     * @param string $data
     * @return int
     */
    function writeResponse(User $user, $data){
        return socket_write($user->socket, $data, strlen($data));
    }

    function calcKey($key1, $key2, $l8b) {
        //Get the numbers
        preg_match_all('/([\d]+)/', $key1, $key1_num);
        preg_match_all('/([\d]+)/', $key2, $key2_num);
        //Number crunching [/bad pun]
        $this->log("Key1: " . $key1_num = implode($key1_num[0]));
        $this->log("Key2: " . $key2_num = implode($key2_num[0]));
        //Count spaces
        preg_match_all('/([ ]+)/', $key1, $key1_spc);
        preg_match_all('/([ ]+)/', $key2, $key2_spc);
        //How many spaces did it find?
        $this->log("Key1 Spaces: " . $key1_spc = strlen(implode($key1_spc[0])));
        $this->log("Key2 Spaces: " . $key2_spc = strlen(implode($key2_spc[0])));
        if ($key1_spc == 0 | $key2_spc == 0) {
            $this->log("Invalid key");

            return;
        }
        //Some math
        $key1_sec = pack("N", $key1_num / $key1_spc); //Get the 32bit secret key, minus the other thing
        $key2_sec = pack("N", $key2_num / $key2_spc);

        //This needs checking, I'm not completely sure it should be a binary string
        return md5($key1_sec . $key2_sec . $l8b, 1); //The result, I think
    }

    function parseHeader($requestHeader) {
        $headerLines = explode("\n", $requestHeader);
        $firstLine = array_shift($headerLines);
        if (!preg_match('/GET (\S*) HTTP/', $firstLine, $matches)){
            throw new WebSocketException('Invalid header! Cannot find route in "{line}".', array('line' => $firstLine));
        }

        $header = array(
            'URI' => $matches[1]
        );

        foreach ($headerLines as $line) {
            if (preg_match('/(\S*): (\S*)\S?/', $line, $matches)){
                list($all, $key, $value) = $matches;
                $header[$key] = $value;
            }
        }

        var_dump($header);
        return $header;
    }

    function getuserbysocket($socket) {
        $found = null;
        foreach ($this->users as $user) {
            if ($user->socket == $socket) {
                $found = $user;
                break;
            }
        }

        return $found;
    }

    function say($msg = "") {
        echo $msg . "\n";
    }

    function log($msg = "") {
        if ($this->debug) {
            echo $msg . "\n";
        }
    }

    function    wrap($msg = "") {
        return chr(0) . $msg . chr(255);
    }

    function  unwrap($msg = "") {
        return substr($msg, 1, strlen($msg) - 2);
    }

}

class User {
    var $id;
    var $socket;
    var $handshake;
}

class WebSocketException extends Exception
{
    public function __construct($msg, $params, $code = 500, $previousException=null){
        parent::__construct(strtr($msg, $params), $code, $previousException);
    }
}