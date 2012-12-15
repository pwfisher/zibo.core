<?php

namespace zibo\core\server;

use zibo\core\environment\sapi\ServerSapi;
use zibo\core\Mime;
use zibo\core\Zibo;

use zibo\library\filesystem\File;
use zibo\library\http\Header;
use zibo\library\http\Request;
use zibo\library\log\listener\EchoLogListener;
use zibo\library\log\Log;
use zibo\library\mvc\Response;
use zibo\library\Thread;

use \Exception;

/**
 * Implements a basic web server to run Zibo standalone
 * Note: this is for testing/learning purposes, not a live environment!
 */
class Server {

    /**
     * Name of the log source
     * @var string
     */
    const LOG_SOURCE = 'server';

    /**
     * Instance of Zibo
     * @var zibo\core\Zibo
     */
    private $zibo;

    /**
     * Instance of the Log
     * @var zibo\library\log\Log
     */
    private $log;

    /**
     * Hostname of the server
     * @var string
     */
    private $host;

    /**
     * Port to listen on
     * @var integer
     */
    private $port;

    /**
     * Incoming request queue
     * @var array
     */
    private $requests;

    /**
     * Current active workers
     * @var array
     */
    private $workers;

    /**
     * Constructs a new Zibo web server
     * @param Zibo $zibo instance of Zibo
     * @param integer $port The port to listen on
     * @return null
     */
    public function __construct(Zibo $zibo, $host = 'localhost', $port = 8080) {
        $this->setHost($host, $port);

        $this->zibo = $zibo;

        $this->log = $zibo->getLog();
        if (!$this->log) {
            $this->log = new Log();
        }

        $this->log->addLogListener(new EchoLogListener());

        $environment = $zibo->getEnvironment();
        if (!$environment->isCli()) {
			throw new Exception('The server can only be run in CLI mode');
        }

        $this->sapi = $zibo->getEnvironment()->getSapi();
        if (!$this->sapi instanceof ServerSapi) {
            throw new Exception('Invalid SAPI in the environment: needs zibo\\core\\environment\\sapi\\ServerSapi, got ' . get_class($this->sapi));
        }

        $this->sapi->setLog($this->log);
    }

    /**
     * Sets the host  and the port to listen to
     * @param string $host Host
     * @param integer $port Port
     * @return null
     * @throws Exception when the provided port is invalid
     */
    public function setHost($host, $port) {
        if (!is_integer($port) || $port <= 0) {
            throw new Exception('Provided port is invalid');
        }

        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Gets the hostname of the server
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Gets the port the server is listening to
     * @return integer
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Gets the instance of Zibo
     * @return zibo\core\Zibo
     */
    public function getZibo() {
        return $this->zibo;
    }

    /**
     * Gets the Log of the server
     * @return zibo\library\log\Log
     */
    public function getLog() {
        return $this->log;
    }

    /**
     * Starts servicing requests
     * @throws Exception when no socket could be created
     */
    public function service() {
        $this->pool = array();
        $this->workers = array();
        $timeout = 5;

        $address = 'tcp://0.0.0.0:' . $this->port;

        $socket = stream_socket_server($address, $errno, $error);
        if (!$socket) {
            throw new Exception($errno . ': ' . $error);
        }
        $this->pool[] = $socket;

        echo 'Zibo ' . Zibo::VERSION . ' application container (' . $this->zibo->getEnvironment()->getName() . ")\n";
        $this->log->logInformation('Listening to ' . $address, null, self::LOG_SOURCE);

        while (1) {
            $this->handleWorkers();

            gc_collect_cycles();

            $connections = $this->pool;
            $write = null;
            $except = null;

            $numModifiedConnections = stream_select($connections, $write, $except, 1);
            if ($numModifiedConnections === false) {
                throw new Exception('Server got interupted');
            }

            for ($i = 0; $i < $numModifiedConnections; ++$i) {
                if ($connections[$i] === $socket) {
                    // incoming connection
                    try {
                        $connection = stream_socket_accept($socket);

//                         // does not work :-(
//                         socket_getpeername($connection, $address, $port);
//                         $this->log->logInformation('client ip', $address);

                        $this->pool[] = $connection;
                    } catch (Exception $exception) {
                        $this->log->logException($exception);
                    }

                    continue;
                }

                // handle request
                $requestData = fread($connections[$i], 1024);
                if (strlen($requestData) === 0) {
                    // connection closed
                    $connection = array_search($connections[$i], $this->pool, false);
                    fclose($connections[$i]);
                    unset($this->pool[$connection]);
                } elseif ($requestData === false) {
                    // internal server error
                    $this->log->logError('Could not handle request', null, self::LOG_SOURCE);
                    $connection = array_search($connections[$i], $this->pool, false);
                    unset($this->pool[$connection]);
                } else {
                    $request = Request::createFromString($requestData);

                    $this->requests[] = new Worker($this, $request, $connections[$i]);
                }
            }
        }
    }

    /**
     * Handle the workers, this method is to implement threads
     * @return null
     */
    protected function handleWorkers() {
        if (!$this->requests) {
            return;
        }

        $worker = array_shift($this->requests);
        $worker->work();
    }

    /**
     * Closes a connection
     * @param resource $handle The handle of the connection
     * @return null
     */
    public function closeConnection($handle) {
        fclose($handle);
        unset($this->pool[array_search($handle, $this->pool)]);
    }

}