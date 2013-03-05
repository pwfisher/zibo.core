<?php

namespace zibo\core\deploy;

/**
 * Data container for the deploy parameters
 */
class DeployProfile {

    /**
     * Name of the profile
     * @var string
     */
    protected $name;

    /**
     * Type of deploy
     * @var string
     */
    protected $type;

    /**
     * Name of the environment for the remote installation
     * @var string
     */
    protected $environment;

    /**
     * Hostname/IP address of the remote server
     * @var string
     */
    protected $server;

    /**
     * Path of the application directory on the remote server
     * @var string
     */
    protected $pathApplication;

    /**
     * Path of the public directory on the remote server
     * @var string
     */
    protected $pathPublic;

    /**
     * Custom parameters
     * @var array
     */
    protected $parameters;

    /**
     * Constructs a new deploy profile
     * @param string $name Name of the profile
     * @param string $server Hostname/IP address of the remote server
     * @param string $environment Name of the environment for the remote
     * installation
     * @param string $pathApplication Path to the application directory on the
     * remote server
     * @param string $pathPublic Path to the public directory on the remote
     * server
     * @return null
     */
    public function __construct($name, $type, $environment, $server, $pathApplication, $pathPublic) {
        $this->setName($name);
        $this->setType($type);
        $this->setEnvironment($environment);
        $this->setServer($server);
        $this->setApplicationPath($pathApplication);
        $this->setPublicPath($pathPublic);

        $this->parameters = array();
    }

    /**
     * Sets the name of this profile
     * @param string $name
     * @return null
     * @throws Exception when the provided name is invalid
     */
    protected function setName($name) {
        if (!is_string($name) || $name == '') {
            throw new Exception('Provided name is invalid: ' . $name);
        }

        $this->name = $name;
    }

    /**
     * Gets the name of this profile
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Sets the file synchronization type of this profile
     * @param string $type Dependency id for the FileSync interface
     * @return null
     * @throws Exception when the provided type is invalid
     */
    protected function setType($type) {
        if (!is_string($type) || $type == '') {
            throw new Exception('Provided type is invalid: ' . $type);
        }

        $this->type = $type;
    }

    /**
     * Gets the file synchronization type of this profile
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Sets the environment for the remote installation
     * @param string $environment Name of the environment
     * @return null
     * @throws Exception when the provided environment is invalid
     */
    protected function setEnvironment($environment) {
        if (!is_string($environment) || $environment == '') {
            throw new Exception('Provided environment is invalid: ' . $environment);
        }

        $this->environment = $environment;
    }

    /**
     * Gets the name of the environment for the remote installation
     * @return string
     */
    public function getEnvironment() {
        return $this->environment;
    }

    /**
     * Sets the remote server
     * @param string $server Hostname or IP address of the server
     * @return null
     * @throws Exception when the provided server is invalid
     */
    protected function setServer($server) {
        if (!is_string($server) || $server == '' || strpos($server, ' ') !== false) {
            throw new Exception('Provided remote server is invalid: ' . $server);
        }

        $this->server = $server;
    }

    /**
     * Gets the remote server
     * @return string Hostname or IP address
     */
    public function getServer() {
        return $this->server;
    }

    /**
     * Sets the application path on the remote server
     * @param string $path Path of the application directory on the remote
     * server
     * @return null
     * @throws Exception when the provided path is invalid
     */
    protected function setApplicationPath($path) {
        if (!is_string($path) || $path == '' || strpos($path, ' ') !== false) {
            throw new Exception('Provided remote application path is invalid: ' . $path);
        }

        $this->pathApplication = rtrim($path, '/');
    }

    /**
     * Gets the application path on the remote server
     * @return string
     */
    public function getApplicationPath() {
        return $this->pathApplication;
    }

    /**
     * Sets the public path on the remote server
     * @param string $path Path of the public directory on the remote server
     * @return null
     * @throws Exception when the provided path is invalid
     */
    protected function setPublicPath($path) {
        if (!is_string($path) || $path == '' || strpos($path, ' ') !== false) {
            throw new Exception('Provided remote public path is invalid: ' . $path);
        }

        $this->pathPublic = rtrim($path, '/');
    }

    /**
     * Gets the public path on the remote server
     * @return string
     */
    public function getPublicPath() {
        return $this->pathPublic;
    }

    /**
     * Sets a custom parameter for the deploy hooks
     * @param string $name Name of the parameter
     * @param mixed $value Value of the parameter
     * @return null
     */
    public function setParameter($name, $value) {
        $this->parameters[$name] = $value;
    }

    /**
     * Gets a custom parameter
     * @param string $name Name of the parameter
     * @param mixed $default Default value for when the parameter not set
     * @return mixed Parameter value if set, the provided default value
     * otherwise
     */
    public function getParameter($name, $default = null) {
        if (!isset($this->parameters[$name])) {
            return $default;
        }

        return $this->parameters[$name];
    }

}