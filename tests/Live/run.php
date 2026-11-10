<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use Joomla\Http\HttpFactory;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Webtolk\Max\Config\MaxConfig;
use Webtolk\Max\Max;
use Webtolk\Max\Tests\Live\ContractRunner;
use Webtolk\Max\Tests\Live\RecordingClient;
use Webtolk\Max\Tests\Live\StandInputs;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$options = getopt('', ['allow-live', 'insecure', 'stand-root:', 'output:', 'transport:', 'phase:', 'fixtures:']);
if (!array_key_exists('allow-live', $options) || !isset($options['stand-root'], $options['output'], $options['transport'])) {
    throw new RuntimeException('Opt-in required: --allow-live --stand-root=... --output=<private-directory> --transport=guzzle|joomla-http');
}
$transport = (string)$options['transport'];
if (!in_array($transport, ['guzzle', 'joomla-http'], true)) {
    throw new RuntimeException('Unsupported transport.');
}
$params = StandInputs::load((string)$options['stand-root']);
if (isset($options['fixtures'])) {
    $fixtures = json_decode((string)file_get_contents((string)$options['fixtures']), true, flags: JSON_THROW_ON_ERROR);
    if (isset($fixtures['test_channel_id'])) {
        $params['test_channel_id'] = (int)$fixtures['test_channel_id'];
    }
}
$output = rtrim((string)$options['output'], '/\\');
if (!is_dir($output) && !mkdir($output, 0700, true) && !is_dir($output)) {
    throw new RuntimeException('Cannot create private evidence directory.');
}
$insecure = array_key_exists('insecure', $options);
$joomlaOptions = ['timeout' => 25];
if ($insecure) {
    $joomlaOptions['transport.curl'] = [CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 0];
}
$http = $transport === 'guzzle'
    ? new Client(['http_errors' => false, 'timeout' => 25, 'connect_timeout' => 8, 'verify' => !$insecure])
    : (new HttpFactory())->getHttp($joomlaOptions);
$client = new RecordingClient($http);
$sdk = (new Max(new MaxConfig((string)$params['bot_token'])))->setTransport($client, new RequestFactory(), new StreamFactory());
$chatId = (int)$params['default_chat_id'];
$source = hash_file('sha256', dirname(__DIR__, 2) . '/src/Entity/Update.php');
if (in_array($options['phase'] ?? 'preflight', ['contract', 'comments'], true)) {
    file_put_contents($output . '/run.json', json_encode([
        'transport' => $transport, 'stand' => 'joomla.local', 'tls_verification' => !$insecure,
        'tls_override' => $insecure ? 'explicit_operator_instruction' : null,
        'sdk_candidate' => '0.4.0', 'update_source_sha256' => $source,
        'php_version' => PHP_VERSION, 'started_at' => gmdate(DATE_ATOM),
    ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    try {
        $runner = new ContractRunner($sdk, $client, $params, $transport, $output);
        if ($options['phase'] === 'comments') {
            $cases = $runner->runComments();
        } else {
            $cases = $runner->run();
        }
        foreach ($cases as $case) {
            if ($case['outcome'] === 'unexpected_error') {
                throw new RuntimeException('An unexpected runner/SDK failure was recorded; inspect private evidence.');
            }
        }
    } catch (Throwable $error) {
        file_put_contents($output . '/runner-error.json', json_encode([
            'class' => $error::class, 'message' => $error->getMessage(),
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        fwrite(STDERR, 'Runner failed: ' . $error::class . PHP_EOL);
        exit(1);
    }
    exit(0);
}
$preflight = ['transport' => $transport, 'stand' => 'joomla.local', 'tls_verification' => !$insecure,
    'update_source_sha256' => $source, 'cases' => []];
foreach ([
    'bots.me' => static fn () => $sdk->bots()->me(),
    'chats.getById' => static fn () => $sdk->chats()->getById($chatId),
    'chats.getPinnedMessage' => static fn () => $sdk->chats()->getPinnedMessage($chatId),
    'subscriptions.list' => static fn () => $sdk->subscriptions()->list(),
] as $name => $invoke) {
    try {
        $result = $invoke();
        $preflight['cases'][$name] = ['outcome' => 'success', 'result' => $result?->toArray()];
    } catch (Throwable $error) {
        $preflight['cases'][$name] = ['outcome' => 'error', 'error_class' => $error::class, 'error' => $error->getMessage()];
    }
    $preflight['cases'][$name]['http'] = $client->flush();
    file_put_contents($output . '/preflight.json', json_encode($preflight, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    $case = $preflight['cases'][$name];
    echo $transport . ' ' . $name . ' ' . $case['outcome'] . PHP_EOL;
}
exit(count(array_filter($preflight['cases'], static fn (array $case): bool => $case['outcome'] !== 'success')) > 0 ? 1 : 0);
