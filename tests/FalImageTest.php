<?php

declare(strict_types=1);

use AiSdk\Fal;
use AiSdk\Fal\Tests\Fakes\FakeHttpClient;
use AiSdk\Generate;
use AiSdk\Support\Sdk;

afterEach(function (): void {
    Generate::reset();
    Fal::reset();
});

it('generates image URLs through fal.run', function (): void {
    $client = new FakeHttpClient(200, json_encode(['images' => [['url' => 'https://cdn.fal.ai/image.png', 'content_type' => 'image/png']]]));
    $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
    Generate::configure(new Sdk($client, $factory, $factory));
    Fal::create(['apiKey' => 'fal-test']);
    $result = Generate::image('Sunset')->model(Fal::model('fal-ai/flux/schnell'))->run();
    expect($result->images[0]->url)->toBe('https://cdn.fal.ai/image.png')
        ->and($client->sentBody()['prompt'])->toBe('Sunset')
        ->and($client->lastRequest->getHeaderLine('Authorization'))->toBe('Key fal-test');
});
