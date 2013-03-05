<?php

namespace zibo\core\deploy\io;

use zibo\core\Zibo;

/**
 * Interface to read deploy profiles
 */
interface DeployProfileIO {

    /**
     * Reads a deploy profile
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @param string $name Name of the profile
     * @return zibo\core\deploy\DeployProfile
     */
    public function readProfile(Zibo $zibo, $name);

}