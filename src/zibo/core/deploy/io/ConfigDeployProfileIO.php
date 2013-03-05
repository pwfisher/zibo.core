<?php

namespace zibo\core\deploy\io;

use zibo\core\deploy\DeployProfile;
use zibo\core\Zibo;

use zibo\library\config\Config;

use \Exception;

/**
 * Zibo configuration implementaion to read deploy profiles
 */
class ConfigDeployProfileIO implements DeployProfileIO {

    /**
     * Parameter prefix for a deploy profile
     * @var string
     */
    const PARAM_DEPLOY = 'deploy.';

    /**
     * Parameter name of the type
     * @var string
     */
    const PARAM_TYPE = 'type';

    /**
     * Parameter name of the environment
     * @var string
     */
    const PARAM_ENVIRONMENT = 'environment';

    /**
     * Parameter name of the remote server
     * @var string
     */
    const PARAM_SERVER = 'server';

    /**
     * Parameter name of the remote application directory
     * @var string
     */
    const PARAM_APPLICATION = 'path.application';

    /**
     * Parameter name of the remote public directory
     * @var string
     */
    const PARAM_PUBLIC = 'path.public';

    /**
     * Reads a deploy profile
     * @param zibo\core\Zibo $zibo Instance of Zibo
     * @param string $name Name of the profile
     * @return zibo\core\deploy\DeployProfile
     */
    public function readProfile(Zibo $zibo, $name) {
        $params = $zibo->getParameter(self::PARAM_DEPLOY . $name);
        if (!is_array($params)) {
            throw new Exception('Could not get deploy profile with name ' . $name . ': not defined');
        }
        $params = Config::flattenConfig($params);

        if (isset($params[self::PARAM_TYPE])) {
            $type = $params[self::PARAM_TYPE];
            unset($params[self::PARAM_TYPE]);
        } else {
            $type = 'ssh';
        }

        if (isset($params[self::PARAM_ENVIRONMENT])) {
            $environment = $params[self::PARAM_ENVIRONMENT];
            unset($params[self::PARAM_ENVIRONMENT]);
        } else {
            $environment = 'prod';
        }

        if (isset($params[self::PARAM_SERVER])) {
            $server = $params[self::PARAM_SERVER];
            unset($params[self::PARAM_SERVER]);
        } else {
            throw new Exception('Could not deploy ' . $name . ': no server parameter set');
        }

        if (isset($params[self::PARAM_APPLICATION])) {
            $application = $params[self::PARAM_APPLICATION];
            unset($params[self::PARAM_APPLICATION]);
        } else {
            throw new Exception('Could not deploy ' . $name . ': no path.application parameter set');
        }

        if (isset($params[self::PARAM_PUBLIC])) {
            $public = $params[self::PARAM_PUBLIC];
            unset($params[self::PARAM_PUBLIC]);
        } else {
            throw new Exception('Could not deploy ' . $name . ': no path.public parameter set');
        }

        $profile = new DeployProfile($name, $type, $environment, $server, $application, $public);

        foreach ($params as $name => $value) {
            $profile->setParameter($name, $value);
        }

        return $profile;
    }

}