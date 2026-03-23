<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\SdkLlmClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Exceptions\RateLimitedException;
use Tests\TestCase;

class SdkLlmClientSemanticTest extends TestCase
{
    use RefreshDatabase;

    public function test_sdk_llm_client_attaches_semantic_context_to_prompt(): void
    {
        // Ensure deterministic local DB embeddings regardless of provider config
        config([
            'ai.default_for_embeddings' => 'openai',
            'ai.providers.openai.embedding_deployment' => 'database',
        ]);

        // Import fixture so resume_embeddings are populated
        $fixturePath = base_path('tests/Feature/Console/Fixtures/cv.json');
        Storage::fake('local');
        $this->artisan('data:import', ['source' => $fixturePath, '--disk' => 'local', '--path' => 'imports/cv.json'])->assertExitCode(0);

        // Create a test provider that captures the messages passed to generateText
        $captor = new class
        {
            public $messages = null;

            public $model = null;

            public function name()
            {
                return 'openai';
            }

            public function defaultTextModel()
            {
                return 'test-model';
            }

            public function textGateway()
            {
                $self = $this;

                return new class($self)
                {
                    private $captor;

                    public function __construct($captor)
                    {
                        $this->captor = $captor;
                    }

                    public function generateText($provider, $model, $a, $messages, $b, $c, $options, $d)
                    {
                        $this->captor->model = $model;

                        $converted = [];
                        foreach ($messages as $m) {
                            if (is_string($m)) {
                                $converted[] = $m;

                                continue;
                            }

                            if (is_object($m)) {
                                if (method_exists($m, 'toArray')) {
                                    $converted[] = json_encode($m->toArray());

                                    continue;
                                }

                                // try common properties
                                if (isset($m->content)) {
                                    $converted[] = (string) $m->content;

                                    continue;
                                }

                                $converted[] = get_class($m);

                                continue;
                            }

                            $converted[] = (string) $m;
                        }

                        $this->captor->messages = $converted;

                        return new class
                        {
                            public function __toString()
                            {
                                return 'ok';
                            }
                        };
                    }
                };
            }
        };

        config(['ai.providers.openai.deployment' => 'deployment-from-config']);

        $client = new SdkLlmClient($captor);

        $resp = $client->generateReply('', '¿Qué versiones de php ha trabajado?');

        // Ensure the provider was called and the first message includes semantic context
        $this->assertNotNull($captor->messages, 'Provider generateText was not called');

        $text = (string) ($captor->messages[0] ?? '');

        $this->assertStringContainsString('Contexto del CV', $text, 'Semantic context header missing');
        $this->assertStringContainsString('PHP', $text, 'Expected technological keywords in context');
        $this->assertSame('deployment-from-config', $captor->model);

        $this->assertSame('ok', $resp['reply']);
    }

    public function test_sdk_llm_client_uses_alternative_deployment_after_rate_limit(): void
    {
        $provider = new class
        {
            public array $attemptedModels = [];

            public function name(): string
            {
                return 'openai';
            }

            public function defaultTextModel(): string
            {
                return 'provider-default-model';
            }

            public function textGateway(): object
            {
                $self = $this;

                return new class($self)
                {
                    public function __construct(private object $provider) {}

                    public function generateText($provider, $model, $a, $messages, $b, $c, $options, $d)
                    {
                        $this->provider->attemptedModels[] = $model;

                        if ($model === 'primary-model') {
                            throw RateLimitedException::forProvider('openai');
                        }

                        return new class
                        {
                            public function __toString(): string
                            {
                                return 'fallback-ok';
                            }
                        };
                    }
                };
            }
        };

        config([
            'ai.providers.openai.deployment' => 'primary-model',
            'ai.providers.openai.alternative_deployment' => ['secondary-model', 'tertiary-model'],
        ]);

        $response = (new SdkLlmClient($provider))->generateReply('', 'test message');

        $this->assertSame(['primary-model', 'secondary-model'], $provider->attemptedModels);
        $this->assertSame('fallback-ok', $response['reply']);
    }
}
