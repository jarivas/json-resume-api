<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\SdkLlmClient;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SdkLlmClientSemanticTest extends TestCase
{
    use RefreshDatabase;

    public function test_sdk_llm_client_attaches_semantic_context_to_prompt(): void
    {
        // Import fixture so resume_embeddings are populated
        $fixturePath = base_path('tests/Feature/Console/Fixtures/cv.json');
        Storage::fake('local');
        $this->artisan('data:import', ['source' => $fixturePath, '--disk' => 'local', '--path' => 'imports/cv.json'])->assertExitCode(0);

        // Create a test provider that captures the messages passed to generateText
        $captor = new class {
            public $messages = null;
            public function defaultTextModel()
            {
                return 'test-model';
            }
            public function textGateway()
            {
                $self = $this;
                return new class($self) {
                    private $captor;
                    public function __construct($captor) { $this->captor = $captor; }
                    public function generateText($provider, $model, $a, $messages, $b, $c, $options, $d)
                    {
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

                        return new class { public function __toString() { return 'ok'; } };
                    }
                };
            }
        };

        $client = new SdkLlmClient($captor);

        $resp = $client->generateReply('', '¿Qué versiones de php ha trabajado?');

        // Ensure the provider was called and the first message includes semantic context
        $this->assertNotNull($captor->messages, 'Provider generateText was not called');

        $text = (string) ($captor->messages[0] ?? '');

        $this->assertStringContainsString('Contexto del CV', $text, 'Semantic context header missing');
        $this->assertStringContainsString('PHP', $text, 'Expected technological keywords in context');

        $this->assertSame('ok', $resp['reply']);
    }
}
