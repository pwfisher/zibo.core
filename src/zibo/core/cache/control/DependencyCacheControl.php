<?php

namespace zibo\core\cache\control;

use zibo\core\BootstrapConfig;
use zibo\core\Zibo;

use zibo\library\dependency\io\CachedDependencyIO;
use zibo\library\filesystem\File;

/**
 * Cache control implementation for the Zibo configuration
 */
class DependencyCacheControl implements CacheControl {

    /**
     * Name of this control
     * @var string
     */
    const NAME = 'dependencies';

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

        return $this->config->willCacheDependencies();
    }

    /**
     * Enables this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function enable(Zibo $zibo) {
        $this->config->read(new File(ZIBO_CONFIG));
        if ($this->config->willCacheDependencies()) {
            return;
        }

        $this->config->setWillCacheDependencies(true);
        $this->config->write(new File(ZIBO_CONFIG));
    }

    /**
     * Disable this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function disable(Zibo $zibo) {
        $this->config->read(new File(ZIBO_CONFIG));
        if (!$this->config->willCacheDependencies()) {
            return;
        }

        $this->config->setWillCacheDependencies(false);
        $this->config->write(new File(ZIBO_CONFIG));
    }

    /**
	 * Clears this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
	 * @return null
     */
    public function clear(Zibo $zibo) {
        $io = $zibo->getEnvironment()->getDependencyIO();
        if (!$io instanceof CachedDependencyIO) {
            return;
        }

        $file = $io->getFile();
        if ($file->exists()) {
            $file->delete();
        }
    }

}