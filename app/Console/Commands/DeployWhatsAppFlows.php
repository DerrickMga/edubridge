<?php

namespace App\Console\Commands;

use App\Models\WhatsAppFlow;
use App\Services\WhatsAppFlowDefinitions;
use App\Services\WhatsAppFlowService;
use Illuminate\Console\Command;

/**
 * DeployWhatsAppFlows
 *
 * Orchestrates the full WhatsApp Flows deployment:
 *   1. Generate RSA-2048 key pair (if not already in .env)
 *   2. Upload public key to Meta WABA
 *   3. Create / update each flow on Meta
 *   4. Upload flow JSON assets
 *   5. Set endpoint URIs for data-exchange flows
 *   6. Publish all flows
 *   7. Write flow IDs to .env + whatsapp_flows table
 *
 * Usage:
 *   php artisan whatsapp:deploy-flows           — full deploy
 *   php artisan whatsapp:deploy-flows --list    — list existing flows
 *   php artisan whatsapp:deploy-flows --keys    — only (re)generate RSA keys
 */
class DeployWhatsAppFlows extends Command
{
    protected $signature = 'whatsapp:deploy-flows
                            {--list   : List all flows in the WABA}
                            {--keys   : Only generate RSA key pair and upload public key}
                            {--no-publish : Deploy without publishing (leave in DRAFT)}
                            {--force  : Re-deploy flows that already exist}';

    protected $description = 'Deploy interactive WhatsApp Flows for EduBridge (ChiedzaByKMG WABA)';

    private WhatsAppFlowService $flowService;

    // Flows to deploy: key => [name, categories, json_method, needsEndpoint]
    private const FLOWS = [
        'chiedza_register' => [
            'name'         => 'EduBridge Registration',
            'categories'   => ['SIGN_UP'],
            'json_method'  => 'registerFlow',
            'needs_endpoint' => false,
        ],
        'chiedza_student' => [
            'name'           => 'EduBridge Student Hub',
            'categories'     => ['OTHER'],
            'json_method'    => 'studentHubFlow',
            'needs_endpoint' => false,
        ],
        'chiedza_teacher' => [
            'name'           => 'EduBridge Teacher Hub',
            'categories'     => ['OTHER'],
            'json_method'    => 'teacherHubFlow',
            'needs_endpoint' => false,
        ],
        'chiedza_announce' => [
            'name'           => 'EduBridge Announcement',
            'categories'     => ['OTHER'],
            'json_method'    => 'announceFlow',
            'needs_endpoint' => false,
        ],
    ];

    private const ENV_KEY_MAP = [
        'chiedza_register' => 'WHATSAPP_REGISTER_FLOW_ID',
        'chiedza_student'  => 'WHATSAPP_STUDENT_HUB_FLOW_ID',
        'chiedza_teacher'  => 'WHATSAPP_TEACHER_HUB_FLOW_ID',
        'chiedza_announce' => 'WHATSAPP_ANNOUNCE_FLOW_ID',
    ];

    public function handle(): int
    {
        $this->flowService = app(WhatsAppFlowService::class);

        if ($this->option('list')) {
            return $this->listFlows();
        }

        if ($this->option('keys')) {
            return $this->generateAndUploadKeys();
        }

        return $this->deployAll();
    }

    // ─── Full deployment ──────────────────────────────────────────────────────

    private function deployAll(): int
    {
        $this->info('=== EduBridge WhatsApp Flows Deployment ===');
        $this->newLine();

        // Step 1: RSA keys — only regenerate if explicitly requested via --keys
        // or if no key exists yet.  --force alone only re-deploys flow JSONs.
        $this->comment('Step 1/4 — RSA key pair…');
        $existingKey = config('services.whatsapp.flow_private_key', '');
        if (empty($existingKey)) {
            if ($this->generateAndUploadKeys() !== 0) {
                return 1;
            }
        } else {
            $this->line('  ✓ RSA key already in .env — skipping keygen (use --keys to regenerate)');
        }

        $this->newLine();

        // Steps 2–4: Each flow
        $this->comment('Steps 2–4 — Deploying flows…');

        foreach (self::FLOWS as $key => $config) {
            if ($this->deployFlow($key, $config) !== 0) {
                $this->error("Deployment failed for: {$key}");
                return 1;
            }
        }

        $this->newLine();
        $this->info('✅ All flows deployed successfully!');
        $this->newLine();
        $this->comment('Next steps:');
        $this->line('  1. Run: php artisan optimize');
        $this->line('  2. Test each flow in the WhatsApp Business Manager → Flows');
        $this->line('  3. Send a test message to +263 8612 166754 and type "hi"');
        $this->newLine();

        return 0;
    }

    private function deployFlow(string $key, array $config): int
    {
        $this->line("  → {$config['name']} ({$key})");

        // Check if already deployed
        $existing = WhatsAppFlow::findByKey($key);
        $force    = $this->option('force');

        if ($existing && $existing->isPublished() && !$force) {
            $this->line("     ✓ Already published (ID: {$existing->meta_flow_id}) — skip (use --force to redeploy)");
            return 0;
        }

        // Build JSON
        $jsonMethod = $config['json_method'];
        $flowJson   = WhatsAppFlowDefinitions::$jsonMethod();

        // Create or reuse existing draft
        if ($existing && $existing->meta_flow_id) {
            $flowId = $existing->meta_flow_id;
            $this->line("     Reusing existing flow ID: {$flowId}");
        } else {
            $createResult = $this->flowService->createFlow($config['name'], $config['categories']);
            if (isset($createResult['error'])) {
                $this->error("     Failed to create flow: " . json_encode($createResult['error']));
                return 1;
            }
            $flowId = $createResult['id'] ?? null;
            if (!$flowId) {
                $this->error("     No flow ID in response: " . json_encode($createResult));
                return 1;
            }
            $this->line("     Created flow ID: {$flowId}");
        }

        // Upload JSON
        $uploadResult = $this->flowService->uploadFlowJson($flowId, $flowJson);
        if (!($uploadResult['success'] ?? false)) {
            $this->error("     JSON upload failed: " . json_encode($uploadResult['error'] ?? $uploadResult));
            if (!empty($uploadResult['detail'])) {
                $this->error("     Validation errors: " . json_encode($uploadResult['detail']));
            }
            return 1;
        }
        $this->line("     JSON uploaded");

        // Set endpoint for data-exchange flows
        if ($config['needs_endpoint']) {
            $endpointUri = config('app.url') . '/api/whatsapp/flow';
            $epResult    = $this->flowService->setEndpointUri($flowId, $endpointUri);
            if (isset($epResult['error'])) {
                $this->error("     Endpoint set failed: " . json_encode($epResult['error']));
                return 1;
            }
            $this->line("     Endpoint: {$endpointUri}");
        }

        // Publish (unless --no-publish)
        $status = 'DRAFT';
        if (!$this->option('no-publish')) {
            $pubResult = $this->flowService->publishFlow($flowId);
            if (isset($pubResult['error'])) {
                $this->warn("     Publish failed (left as DRAFT): " . json_encode($pubResult['error']));
            } else {
                $status = 'PUBLISHED';
                $this->line("     Published ✓");
            }
        } else {
            $this->line("     Left as DRAFT (--no-publish)");
        }

        // Save to DB
        WhatsAppFlow::updateOrCreate(
            ['flow_key' => $key],
            [
                'meta_flow_id' => $flowId,
                'name'         => $config['name'],
                'status'       => $status,
                'categories'   => $config['categories'],
                'endpoint_uri' => $config['needs_endpoint'] ? (config('app.url') . '/api/whatsapp/flow') : null,
            ]
        );

        // Write to .env
        $envKey = self::ENV_KEY_MAP[$key] ?? null;
        if ($envKey) {
            $this->writeEnvValue($envKey, $flowId);
            $this->line("     .env: {$envKey}={$flowId}");
        }

        return 0;
    }

    // ─── RSA key generation ───────────────────────────────────────────────────

    private function generateAndUploadKeys(): int
    {
        $existingKey = config('services.whatsapp.flow_private_key', '');

        if (!empty($existingKey) && !$this->option('force')) {
            $this->line('  ✓ RSA private key already configured — skip (use --force to regenerate)');
            return 0;
        }

        $this->line('  Generating RSA-2048 key pair…');

        // On Windows, openssl_pkey_new() needs OPENSSL_CONF set
        if (PHP_OS_FAMILY === 'Windows' && empty(getenv('OPENSSL_CONF'))) {
            // Find openssl.cnf relative to the PHP executable
            $phpDir   = dirname(PHP_BINARY);
            $cnfPaths = [
                $phpDir . '\extras\ssl\openssl.cnf',
                $phpDir . '\..\ssl\openssl.cnf',
                'C:\Windows\System32\openssl.cnf',
            ];
            foreach ($cnfPaths as $cnfPath) {
                if (file_exists($cnfPath)) {
                    putenv("OPENSSL_CONF={$cnfPath}");
                    $this->line("  Set OPENSSL_CONF={$cnfPath}");
                    break;
                }
            }
        }

        $res = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if (!$res) {
            $this->error('  openssl_pkey_new() failed: ' . openssl_error_string());
            return 1;
        }

        openssl_pkey_export($res, $privateKeyPem);
        $details      = openssl_pkey_get_details($res);
        $publicKeyPem = $details['key'];

        // Write to .env (newlines as \n literals)
        $privateSingleLine = str_replace(["\r\n", "\n"], '\\n', $privateKeyPem);
        $publicSingleLine  = str_replace(["\r\n", "\n"], '\\n', $publicKeyPem);

        $this->writeEnvValue('WHATSAPP_FLOW_PRIVATE_KEY', '"' . $privateSingleLine . '"');
        $this->writeEnvValue('WHATSAPP_FLOW_PUBLIC_KEY',  '"' . $publicSingleLine  . '"');

        $this->line('  Keys written to .env');

        // Upload public key to Meta
        $this->line('  Uploading public key to Meta…');
        $result = $this->flowService->uploadPublicKey($publicKeyPem);

        if (isset($result['error'])) {
            $this->error('  Public key upload failed: ' . json_encode($result['error']));
            $this->warn('  Private key still saved to .env — fix the error and rerun with --keys --force');
            return 1;
        }

        $this->line('  Public key uploaded to Meta ✓');
        return 0;
    }

    // ─── List flows ───────────────────────────────────────────────────────────

    private function listFlows(): int
    {
        $result = $this->flowService->listFlows();
        $flows  = $result['data'] ?? [];

        if (empty($flows)) {
            $this->info('No flows found for this WABA.');
            return 0;
        }

        $this->table(
            ['ID', 'Name', 'Status', 'Categories'],
            array_map(fn ($f) => [
                $f['id']         ?? '-',
                $f['name']       ?? '-',
                $f['status']     ?? '-',
                implode(', ', $f['categories'] ?? []),
            ], $flows)
        );

        return 0;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Write a KEY=VALUE line to the .env file.
     * Updates existing key or appends a new one.
     */
    private function writeEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        $escaped = preg_quote($key, '/');

        if (preg_match("/^{$escaped}=/m", $content)) {
            // Replace existing
            $content = preg_replace("/^{$escaped}=.*/m", "{$key}={$value}", $content);
        } else {
            // Append
            $content .= PHP_EOL . "{$key}={$value}";
        }

        file_put_contents($envPath, $content);
    }
}
