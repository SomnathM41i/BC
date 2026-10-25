<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class InstallerController extends Controller
{
    public function index(): Response
    {
        if (! $this->installerEnabled()) {
            abort(404);
        }

        $checks = $this->environmentChecks();

        return $this->page('Setup', $this->renderChecks($checks).$this->renderForm());
    }

    public function run(Request $request): Response
    {
        if (! $this->installerEnabled()) {
            abort(404);
        }

        $token = (string) $request->input('token', '');
        $expected = $this->expectedToken();

        if ($expected === '' || ! hash_equals($expected, $token)) {
            return $this->page('Wrong token', $this->alert(
                'Authentication failed',
                'The deployment token you entered is incorrect. Check the DEPLOY_TOKEN value in your .env file.'
            ).$this->renderForm(), 403);
        }

        $action = (string) $request->input('action', '');

        try {
            $output = $this->perform($request, $action);
        } catch (Throwable $e) {
            $output = 'ERROR: '.$e->getMessage();
        }

        $label = $this->actions()[$action]['label'] ?? $action;

        return $this->page(
            'Result',
            $this->alert('Ran: '.$label, 'Scroll down to see the command output.').
            '<pre class="output">'.e($this->stripAnsi($output)).'</pre>'.
            '<p><a class="btn" href="'.url('deploy').'">&larr; Back to setup</a></p>'
        );
    }

    /**
     * Only allow the installer to run when a DEPLOY_TOKEN is configured.
     */
    private function installerEnabled(): bool
    {
        return $this->expectedToken() !== '';
    }

    private function expectedToken(): string
    {
        return trim((string) config('deploy.token', ''));
    }

    /**
     * @return array<string, array{label: string, hint: string, commands: array<int, array{0: string, 1: array<string, mixed>}>}>
     */
    private function actions(): array
    {
        return [
            'key' => [
                'label' => 'Generate application key',
                'hint' => 'Run this first so logins and sessions work.',
                'commands' => [['key:generate', ['--force' => true]]],
            ],
            'install' => [
                'label' => 'Install (migrate + seed)',
                'hint' => 'First-time setup: create all tables and the starter accounts.',
                'commands' => [
                    ['migrate', ['--force' => true]],
                    ['db:seed', ['--force' => true]],
                ],
            ],
            'migrate' => [
                'label' => 'Run migrations',
                'hint' => 'Create or update database tables.',
                'commands' => [['migrate', ['--force' => true]]],
            ],
            'seed' => [
                'label' => 'Run seeders',
                'hint' => 'Add the default accounts and categories. Safe to run again.',
                'commands' => [['db:seed', ['--force' => true]]],
            ],
            'fresh' => [
                'label' => 'Reset database (fresh + seed)',
                'hint' => 'WARNING: deletes ALL data and recreates the tables.',
                'commands' => [['migrate:fresh', ['--force' => true, '--seed' => true]]],
            ],
            'storage_link' => [
                'label' => 'Create storage link',
                'hint' => 'Only needed if you upload public files.',
                'commands' => [['storage:link', []]],
            ],
            'clear' => [
                'label' => 'Clear all caches',
                'hint' => 'Use after changing .env or views.',
                'commands' => [
                    ['config:clear', []],
                    ['route:clear', []],
                    ['view:clear', []],
                    ['cache:clear', []],
                ],
            ],
            'optimize' => [
                'label' => 'Optimize for production',
                'hint' => 'Caches config, routes and views for speed. Run last.',
                'commands' => [
                    ['config:cache', []],
                    ['route:cache', []],
                    ['view:cache', []],
                ],
            ],
        ];
    }

    /**
     * @param  array<int, array{0: string, 1: array<string, mixed>}>  $commands
     */
    private function perform(Request $request, string $action): string
    {
        $action = Str::lower($action);

        if ($action === 'status') {
            Artisan::call('migrate:status');

            return (string) Artisan::output();
        }

        if ($action === 'owner') {
            return $this->createOwner($request);
        }

        if (! isset($this->actions()[$action])) {
            return 'Unknown action.';
        }

        $output = '';

        foreach ($this->actions()[$action]['commands'] as [$command, $arguments]) {
            Artisan::call($command, $arguments);
            $output .= '$ php artisan '.$command."\n".Artisan::output()."\n";
        }

        return $output;
    }

    private function createOwner(Request $request): string
    {
        $validator = Validator::make($request->all(), [
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255'],
            'owner_password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            throw new RuntimeException(implode(' ', $validator->errors()->all()));
        }

        $validated = $validator->validated();

        $user = User::query()->updateOrCreate(
            ['email' => $validated['owner_email']],
            [
                'name' => $validated['owner_name'],
                'password' => Hash::make($validated['owner_password']),
                'role' => UserRole::Owner,
                'is_active' => true,
            ]
        );

        return 'Owner account ready.'."\n"
            .'Email: '.$user->email."\n"
            .'You can now sign in with the password you just set.';
    }

    /**
     * @return array<int, array{label: string, ok: bool, detail: string}>
     */
    private function environmentChecks(): array
    {
        $php = PHP_VERSION;
        $checks = [];

        $checks[] = [
            'label' => 'PHP version',
            'ok' => version_compare($php, '8.2.0', '>='),
            'detail' => $php.' (needs 8.2 or newer)',
        ];

        foreach (['bcmath', 'pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo'] as $extension) {
            $checks[] = [
                'label' => 'Extension: '.$extension,
                'ok' => extension_loaded($extension),
                'detail' => extension_loaded($extension) ? 'loaded' : 'MISSING - ask your host to enable it',
            ];
        }

        $checks[] = [
            'label' => '.env file',
            'ok' => file_exists(base_path('.env')),
            'detail' => file_exists(base_path('.env')) ? base_path('.env') : 'not found',
        ];

        $checks[] = [
            'label' => 'APP_KEY',
            'ok' => filled(config('app.key')),
            'detail' => filled(config('app.key')) ? 'set' : 'empty - run "Generate application key"',
        ];

        $checks[] = [
            'label' => 'storage/ writable',
            'ok' => is_writable(storage_path()),
            'detail' => is_writable(storage_path()) ? 'writable' : 'NOT writable - fix permissions (755/775)',
        ];

        [$dbOk, $dbDetail] = $this->databaseCheck();
        $checks[] = ['label' => 'Database connection', 'ok' => $dbOk, 'detail' => $dbDetail];

        return $checks;
    }

    /**
     * @return array{0: bool, 1: string}
     */
    private function databaseCheck(): array
    {
        try {
            DB::connection()->getPdo();

            return [true, 'connected to '.config('database.default')];
        } catch (Throwable $e) {
            return [false, 'could not connect: '.$e->getMessage()];
        }
    }

    /**
     * @param  array<int, array{label: string, ok: bool, detail: string}>  $checks
     */
    private function renderChecks(array $checks): string
    {
        $rows = '';

        foreach ($checks as $check) {
            $badge = $check['ok']
                ? '<span class="ok">OK</span>'
                : '<span class="bad">FAIL</span>';

            $rows .= '<tr><td>'.$badge.'</td><td>'.e($check['label']).'</td><td>'.e($check['detail']).'</td></tr>';
        }

        return '<h2>Environment</h2><table>'.$rows.'</table>';
    }

    private function renderForm(): string
    {
        $buttons = '';

        foreach ($this->actions() as $key => $action) {
            $class = in_array($key, ['fresh'], true) ? 'btn danger' : 'btn';
            $buttons .= '<button class="'.$class.'" type="submit" name="action" value="'.e($key).'">'
                .e($action['label']).'</button>';
        }

        $ownerFields = '<div class="owner-box">'
            .'<h3>Create your owner login</h3>'
            .'<p class="hint">Run the migrations first. This creates or resets the owner/admin account with a password you choose.</p>'
            .'<p><label>Name<br><input type="text" name="owner_name" value="" autocomplete="off"></label></p>'
            .'<p><label>Email<br><input type="text" name="owner_email" value="" autocomplete="off"></label></p>'
            .'<p><label>Password (min 8 characters)<br><input type="text" name="owner_password" value="" autocomplete="off"></label></p>'
            .'<button class="btn" type="submit" name="action" value="owner">Create / reset owner login</button>'
            .'</div>';

        return '<h2>Run a step</h2>
            <form method="POST" action="'.url('deploy/run').'">
                <input type="hidden" name="_token" value="'.csrf_token().'">
                <p><label>Deployment token<br>
                    <input type="text" name="token" placeholder="Paste your DEPLOY_TOKEN here" autocomplete="off" required>
                </label></p>
                <p class="hint">Recommended order: Generate application key &rarr; Install (migrate + seed) &rarr; Create your owner login &rarr; Optimize for production.</p>
                <div class="buttons">'.$buttons.'</div>
                '.$ownerFields.'
                <p class="hint">After the site works, remove DEPLOY_TOKEN from .env to disable this page.</p>
            </form>';
    }

    private function alert(string $title, string $message): string
    {
        return '<div class="notice"><strong>'.e($title).'</strong><br>'.e($message).'</div>';
    }

    private function page(string $title, string $body, int $status = 200): Response
    {
        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
            .'<meta name="viewport" content="width=device-width, initial-scale=1">'
            .'<title>'.e($title).' &middot; Bandgar Setup</title>'
            .'<style>'
            .'body{font-family:system-ui,Segoe UI,Arial,sans-serif;background:#f1f5f9;color:#0f172a;margin:0;padding:24px}'
            .'.wrap{max-width:760px;margin:0 auto;background:#fff;border-radius:12px;padding:28px;box-shadow:0 1px 3px rgba(0,0,0,.1)}'
            .'h1{margin:0 0 4px;font-size:22px}h2{font-size:16px;margin:24px 0 8px}'
            .'table{width:100%;border-collapse:collapse;font-size:14px}td{padding:6px 8px;border-bottom:1px solid #e2e8f0;vertical-align:top}'
            .'.ok{color:#047857;font-weight:700}.bad{color:#be123c;font-weight:700}'
            .'input[type=text]{width:100%;max-width:420px;padding:8px;border:1px solid #cbd5e1;border-radius:6px}'
            .'.buttons{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0}'
            .'.owner-box{margin-top:20px;padding:16px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc}'
            .'.owner-box h3{margin:0 0 4px;font-size:15px}'
            .'.btn{display:inline-block;background:#0f172a;color:#fff;border:0;border-radius:6px;padding:9px 14px;font-size:14px;cursor:pointer;text-decoration:none}'
            .'.btn.danger{background:#be123c}.hint{color:#64748b;font-size:13px}'
            .'.notice{background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:12px;margin:12px 0;font-size:14px}'
            .'.output{background:#0f172a;color:#e2e8f0;padding:14px;border-radius:8px;overflow:auto;font-size:13px;white-space:pre-wrap}'
            .'</style></head><body><div class="wrap"><h1>Bandgar Account Manager</h1>'
            .'<p class="hint">Browser setup &mdash; for shared hosting without a terminal.</p>'
            .$body.'</div></body></html>';

        return response($html, $status)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function stripAnsi(string $text): string
    {
        return (string) preg_replace('/\x1b\[[0-9;]*m/', '', $text);
    }
}
