<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Live;

use Joomla\Database\DatabaseFactory;
use RuntimeException;

final class StandInputs
{
    /** @return array<string, mixed> */
    public static function load(string $standRoot): array
    {
        if (realpath((string)getcwd()) !== realpath($standRoot)) {
            throw new RuntimeException('Run from the explicitly selected Joomla test stand root.');
        }
        if (!is_file($standRoot . '/configuration.php')) {
            throw new RuntimeException('Joomla test stand configuration was not found.');
        }
        $sdkLoader = require dirname(__DIR__, 2) . '/vendor/autoload.php';
        require_once $standRoot . '/libraries/vendor/autoload.php';
        $sdkLoader->unregister();
        $sdkLoader->register(true);
        require_once $standRoot . '/configuration.php';
        $config = new \JConfig();
        $db = (new DatabaseFactory())->getDriver($config->dbtype, [
            'host' => $config->host, 'user' => $config->user, 'password' => $config->password,
            'database' => $config->db, 'prefix' => $config->dbprefix,
        ]);
        $query = $db->getQuery(true)->select($db->quoteName('params'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('wtmax'));
        $params = json_decode((string)$db->setQuery($query)->loadResult(), true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($params) || empty($params['bot_token']) || empty($params['default_chat_id'])) {
            throw new RuntimeException('WT Max test plugin must contain a token and default test chat.');
        }

        return $params;
    }
}
