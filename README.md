# PHP AI SDK — fal

Image generation with fal for the framework-agnostic [PHP AI SDK](https://phpaisdk.com). Also supports video, speech generation, and transcription.

## Installation

```bash
composer require aisdk/fal
```

Set `FAL_API_KEY` in your environment.

## Usage

```php
use AiSdk\Fal;
use AiSdk\Generate;

$result = Generate::image('A mountain lake at sunrise, soft natural light.')
    ->model(Fal::model('fal-ai/flux-2-pro'))
    ->run();

$result->output->save(__DIR__.'/image.png');
```

## Documentation

[Read the documentation](https://phpaisdk.com/docs/0.x/providers/fal) for configuration, supported features, and more examples.

[Contributing](https://github.com/phpaisdk/.github/blob/main/CONTRIBUTING.md) · [Support](https://github.com/phpaisdk/.github/blob/main/SUPPORT.md)

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
