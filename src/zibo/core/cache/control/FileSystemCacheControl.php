<?php

namespace zibo\core\cache\control;

use zibo\core\BootstrapConfig;
use zibo\core\environment\filebrowser\IndexedFileBrowser;
use zibo\core\Zibo;

use zibo\library\filesystem\File;

/**
 * Cache control implementation for the indexed file browser
 */
class FileSystemCacheControl implements CacheControl {

    /**
     * Name of this control
     * @var string
     */
    const NAME = 'filesystem';

    /**
     * Instance of Zibo bootstrap configuration
     * @var zibo\core\build\Config
     */
    private $config;

    /**
     * Constructs a new cache control
     * @param zibo\core\Zibo $zibo
     * @return null
     */
    public function __construct() {
        $this->config = new BootstrapConfig();
    }

    /**
     * Gets the name of this cache
     * @return string
     */
    public function getName() {
        return self::NAME;
    }

    /**
    * Gets whether this cache is enabled
    * @param zibo\core\Zibo $zibo Instance of Zibo
    * @return boolean
    */
    public function isEnabled(Zibo $zibo) {
        $this->config->read(new File(ZIBO_CONFIG));

        return $this->config->willCacheFileSystem();
    }

    /**
     * Enables this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function enable(Zibo $zibo) {
        $this->config->read(new File(ZIBO_CONFIG));
        if ($this->config->willCacheFileSystem()) {
            return;
        }

        $this->config->setWillCacheFileSystem(true);
        $this->config->write(new File(ZIBO_CONFIG));
    }

    /**
     * Disable this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function disable(Zibo $zibo) {
        $this->config->read(new File(ZIBO_CONFIG));
        if (!$this->config->willCacheFileSystem()) {
            return;
        }

        $this->config->setWillCacheFileSystem(false);
        $this->config->write(new File(ZIBO_CONFIG));
    }

    /**
	 * Clears this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
	 * @return null
     */
    public function clear(Zibo $zibo) {
        $fileBrowser = $zibo->getEnvironment()->getFileBrowser();
        if (!$fileBrowser instanceof IndexedFileBrowser) {
            return;
        }

        $fileBrowser->reset();
    }

}