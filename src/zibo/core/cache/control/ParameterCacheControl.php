<?php

namespace zibo\core\cache\control;

use zibo\core\BootstrapConfig;
use zibo\core\Zibo;

use zibo\library\config\io\CachedConfigIO;
use zibo\library\filesystem\File;

/**
 * Cache control implementation for the Zibo configuration
 */
class ParameterCacheControl implements CacheControl {

    /**
     * Name of this control
     * @var string
     */
    const NAME = 'parameters';

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
     * @param zibo\library\i18n\translation\Translator $translator
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

        return $this->config->willCacheParameters();
    }

    /**
     * Enables this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function enable(Zibo $zibo) {
        $this->config->read(new File(ZIBO_CONFIG));
        if ($this->config->willCacheParameters()) {
            return;
        }

        $this->config->setWillCacheParameters(true);
        $this->config->write(new File(ZIBO_CONFIG));
    }

    /**
     * Disable this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @return null
     */
    public function disable(Zibo $zibo) {
        $this->config->read(new File(ZIBO_CONFIG));
        if (!$this->config->willCacheParameters()) {
            return;
        }

        $this->config->setWillCacheParameters(false);
        $this->config->write(new File(ZIBO_CONFIG));
    }

    /**
	 * Clears this cache
     * @param zibo\core\Zibo $zibo Instance of Zibo
	 * @return null
     */
    public function clear(Zibo $zibo) {
        $io = $zibo->getEnvironment()->getConfigIO();
        if (!$io instanceof CachedConfigIO) {
            return;
        }

        $file = $io->getFile();
        if ($file->exists()) {
            $file->delete();
        }
    }

}