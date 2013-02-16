<?php

namespace zibo\core\cache\control;

use zibo\core\BootstrapConfig;
use zibo\core\Bootstrap;
use zibo\core\Zibo;

use zibo\library\filesystem\File;

/**
 * Cache control implementation for the common classes
 */
class ClassesCacheControl implements CacheControl {

    /**
     * Name of this control
     * @var string
     */
    const NAME = 'classes';

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
     * Gets whether this cache can be toggled
     * @return boolean
     */
    public function canToggle() {
        return true;
    }

    /**
    * Gets whether this cache is enabled
    * @param zibo\core\Zibo $zibo Instance of Zibo
    * @return boolean
    */
    public function isEnabled(Zibo $zibo) {
        $this->config->read(new File(ZIBO_CONFIG));

        return $this->config->willCacheClasses();
    }

    /**
     * Enables this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function enable(Zibo $zibo) {
        $this->config->read(new File(ZIBO_CONFIG));
        if ($this->config->willCacheClasses()) {
            return;
        }

        $this->config->setWillCacheClasses(true);
        $this->config->write(new File(ZIBO_CONFIG));
    }

    /**
     * Disable this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function disable(Zibo $zibo) {
        $this->config->read(new File(ZIBO_CONFIG));
        if (!$this->config->willCacheClasses()) {
            return;
        }

        $this->config->setWillCacheClasses(false);
        $this->config->write(new File(ZIBO_CONFIG));
    }

    /**
	 * Clears this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
	 * @return null
     */
    public function clear(Zibo $zibo) {
        $file = new File($zibo->getApplicationDirectory() . Bootstrap::FILE_CLASSES_CACHE);
        if ($file->exists()) {
            $file->delete();
        }
    }

}