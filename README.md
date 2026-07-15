# aisdk/fal

<a href="https://github.com/phpaisdk/fal/actions"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/phpaisdk/fal/tests.yml?branch=main&label=Tests"></a>
<a href="https://packagist.org/packages/aisdk/fal"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/aisdk/fal"></a>
<a href="https://packagist.org/packages/aisdk/fal"><img alt="Latest Version" src="https://img.shields.io/packagist/v/aisdk/fal"></a>
<a href="https://packagist.org/packages/aisdk/fal"><img alt="License" src="https://img.shields.io/packagist/l/aisdk/fal"></a>
<a href="https://whyphp.dev"><img src="https://img.shields.io/badge/Why_PHP-in_2026-7A86E8?style=flat-square&labelColor=18181b" alt="Why PHP in 2026"></a>

------

[fal.ai](https://fal.ai) provider for the PHP AI SDK — **image**, **speech**, **transcription**, and **video** generation via `fal.run` and `queue.fal.run`, aligned with `@ai-sdk/fal`.

## Installation

```bash
composer require aisdk/fal
```

## Configuration

| Variable | Purpose |
| -------- | ------- |
| `FAL_API_KEY` | Primary API key. |
| `FAL_KEY` | Fallback key name (matches upstream). |
| `FAL_BASE_URL` | Override default `https://fal.run`. |

Authorization header: `Authorization: Key <api-key>`.

## Usage

Model ids are fal route segments (for example `fal-ai/flux/schnell`).

```php
use AiSdk\Generate;
use AiSdk\Fal;

$result = Generate::image()
    ->model(Fal::image('fal-ai/flux/schnell'))
    ->prompt('A minimal desk lamp.')
    ->run();
```

```php
Generate::speech()
    ->model(Fal::speech('fal-ai/minimax/speech-02-hd'))
    ->input('Hello')
    ->run();
```

Video generation submits to `queue.fal.run` and polls until a `video.url` is available (timeouts configurable via `videoPollIntervalMs` / `videoPollTimeoutMs`).

Language and embedding modalities are not implemented on fal in this package and throw `NoSuchModelException`.

## License

MIT.
