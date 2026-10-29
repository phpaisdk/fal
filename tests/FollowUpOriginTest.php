<?php

declare(strict_types=1);

use AiSdk\Fal\FalOptions;
use AiSdk\Fal\Models\FalVideoModel;
use AiSdk\Responses\VideoJob;
use AiSdk\Responses\VideoJobStatus;
use AiSdk\Exceptions\InvalidResponseException;
use AiSdk\Fal\Tests\Fakes\SequenceHttpClient;
use AiSdk\Support\Sdk;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;

require_once __DIR__ . '/Fakes/SequenceHttpClient.php';

function followUp(string $url, string $baseUrl = 'https://api.example.com/v1'): array
{
    $client = new SequenceHttpClient([new Response(200, [], json_encode(['video' => ['url' => 'https://media.example/video.mp4']], JSON_THROW_ON_ERROR))]);
    $factory = new Psr17Factory();
    $model = new FalVideoModel('video', new FalOptions(apiKey: 'secret', baseUrl: $baseUrl, sdk: new Sdk($client, $factory, $factory)));
    $run = fn() => $model->poll(new VideoJob('job', 'fal', 'video', VideoJobStatus::Queued, providerMetadata: ['fal' => ['response_url' => $url]]));

    return [$run, $client];
}

it('permits follow-ups only on the configured origin', function (string $url) {
    [$run, $client] = followUp($url);
    $run();
    expect($client->requests)->toHaveCount(1);
    expect(end($client->requests)->getHeaderLine('Authorization'))->toBe('Key secret');
})->with([
    'https://api.example.com/result?token=signed',
    'https://API.EXAMPLE.COM:443/result',
]);

it('rejects untrusted follow-up origins before sending credentials', function (string $url) {
    [$run, $client] = followUp($url);
    try {
        $run();
        test()->fail('Expected origin rejection.');
    } catch (InvalidResponseException $error) {
        expect($error->getMessage())->toBe('Provider follow-up URL has an untrusted origin.');
        expect((string) $error)->not->toContain('signed-secret');
    }
    expect($client->requests)->toHaveCount(0);
})->with([
    'https://evil.test/result?token=signed-secret',
    'http://api.example.com/result',
    'https://api.example.com:444/result',
    'https://api.example.com@evil.test/result',
    'https://user:password@api.example.com/result',
    'https://api.example.com.evil.test/result',
    'https://api.example.com\\@evil.test/result',
    '/relative/result',
    '//api.example.com/result',
    'https://api.example.com:99999/result',
]);

it('honors an explicitly configured non-default port', function () {
    [$run, $client] = followUp('https://custom.example:8443/result', 'https://custom.example:8443/v1');
    $run();
    expect($client->requests)->toHaveCount(1);
});

it('permits only the documented queue sibling on the default fal origin', function () {
    [$run, $client] = followUp('https://queue.fal.run/result', 'https://fal.run');
    $run();
    expect($client->requests)->toHaveCount(1);
    foreach (['http://queue.fal.run/result', 'https://queue.fal.run:444/result', 'https://queue.fal.run.evil.test/result'] as $url) {
        [$run, $client] = followUp($url, 'https://fal.run');
        expect($run)->toThrow(InvalidResponseException::class);
        expect($client->requests)->toBe([]);
    }
    [$run, $client] = followUp('https://queue.fal.run/result', 'https://custom.example');
    expect($run)->toThrow(InvalidResponseException::class);
    expect($client->requests)->toBe([]);
});
