<?php

namespace zibo\core\environment\config;

use zibo\library\config\io\ConfigIO;

/**
 * Implementation for the configuration I/O based on the Zibo file system
 * structure
 */
interface ZiboConfigIO extends ConfigIO {

    /**
     * Sets the name of the environment
     * @param string $environment Name of the environment
     * @return null
     * @throws Exception when the provided name is empty or not a string
     */
    public function setEnvironment($environment = null);

}