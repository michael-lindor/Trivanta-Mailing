<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Str;
use ZipArchive;

class WordPressPluginBrandingService
{
    public function settings(): array
    {
        $defaults = [
            'white_label_enabled' => false,
            'plugin_name' => 'MailPurse Integration',
            'plugin_slug' => 'mailpurse-integration',
            'plugin_author' => 'MailPurse',
            'plugin_description' => 'Send WordPress and WooCommerce events to MailPurse Automations.',
            'plugin_menu_label' => 'MailPurse',
            'plugin_settings_title' => 'MailPurse Integration',
            'app_label' => 'MailPurse',
        ];

        $settings = [];
        foreach ($defaults as $key => $default) {
            $value = Setting::get('wordpress_plugin_' . $key, $default);
            $settings[$key] = is_string($default)
                ? (is_string($value) ? trim($value) : (string) $default)
                : (bool) $value;
        }

        $settings['plugin_slug'] = $this->sanitizeSlug($settings['plugin_slug'] ?? $defaults['plugin_slug']);
        $settings['plugin_name'] = $this->sanitizeText($settings['plugin_name'] ?? $defaults['plugin_name'], $defaults['plugin_name']);
        $settings['plugin_author'] = $this->sanitizeText($settings['plugin_author'] ?? $defaults['plugin_author'], $defaults['plugin_author']);
        $settings['plugin_description'] = $this->sanitizeText($settings['plugin_description'] ?? $defaults['plugin_description'], $defaults['plugin_description']);
        $settings['plugin_menu_label'] = $this->sanitizeText($settings['plugin_menu_label'] ?? $defaults['plugin_menu_label'], $defaults['plugin_menu_label']);
        $settings['plugin_settings_title'] = $this->sanitizeText($settings['plugin_settings_title'] ?? $defaults['plugin_settings_title'], $defaults['plugin_settings_title']);
        $settings['app_label'] = $this->sanitizeText($settings['app_label'] ?? $defaults['app_label'], $defaults['app_label']);
        $settings['download_filename'] = 'wp-' . $settings['plugin_slug'] . '.zip';
        $settings['zip_root'] = $settings['plugin_slug'];
        $settings['main_file'] = $settings['plugin_slug'] . '.php';
        $settings['class_prefix'] = $this->classPrefix($settings['plugin_slug']);
        $settings['namespace'] = $settings['class_prefix'];
        $settings['constant_prefix'] = strtoupper(str_replace('-', '_', $settings['plugin_slug']));
        $settings['option_key'] = str_replace('-', '_', $settings['plugin_slug']) . '_settings';
        $settings['table_suffix'] = str_replace('-', '_', $settings['plugin_slug']) . '_event_queue';
        $settings['cron_hook'] = str_replace('-', '_', $settings['plugin_slug']) . '_process_queue';
        $settings['cron_schedule'] = str_replace('-', '_', $settings['plugin_slug']) . '_minute';
        $settings['settings_group'] = str_replace('-', '_', $settings['plugin_slug']);
        $settings['admin_post_action'] = str_replace('-', '_', $settings['plugin_slug']) . '_test';
        $settings['query_flag'] = str_replace('-', '_', $settings['plugin_slug']) . '_test';
        $settings['header_prefix'] = $this->headerPrefix($settings);
        $settings['header_timestamp'] = 'X-' . $settings['header_prefix'] . '-Timestamp';
        $settings['header_signature'] = 'X-' . $settings['header_prefix'] . '-Signature';

        return $settings;
    }

    public function save(array $input): void
    {
        $current = $this->settings();
        $whiteLabelEnabled = (bool) ($input['white_label_enabled'] ?? false);

        $values = [
            'white_label_enabled' => $whiteLabelEnabled,
            'plugin_name' => $this->sanitizeText($input['plugin_name'] ?? $current['plugin_name'], $current['plugin_name']),
            'plugin_slug' => $this->sanitizeSlug($input['plugin_slug'] ?? $current['plugin_slug']),
            'plugin_author' => $this->sanitizeText($input['plugin_author'] ?? $current['plugin_author'], $current['plugin_author']),
            'plugin_description' => $this->sanitizeText($input['plugin_description'] ?? $current['plugin_description'], $current['plugin_description']),
            'plugin_menu_label' => $this->sanitizeText($input['plugin_menu_label'] ?? $current['plugin_menu_label'], $current['plugin_menu_label']),
            'plugin_settings_title' => $this->sanitizeText($input['plugin_settings_title'] ?? $current['plugin_settings_title'], $current['plugin_settings_title']),
            'app_label' => $this->sanitizeText($input['app_label'] ?? $current['app_label'], $current['app_label']),
        ];

        foreach ($values as $key => $value) {
            Setting::set('wordpress_plugin_' . $key, $value, 'integrations', is_bool($value) ? 'boolean' : 'string');
        }
    }

    public function packagePlugin(): array
    {
        $branding = $this->settings();
        $pluginDir = base_path('wordpress-plugin/mailpurse-integration');
        abort_unless(is_dir($pluginDir), 404);
        abort_unless(class_exists(ZipArchive::class), 500);

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $tmpZipPath = $tmpDir . '/' . $branding['plugin_slug'] . '-' . bin2hex(random_bytes(12)) . '.zip';
        $zip = new ZipArchive();
        $opened = $zip->open($tmpZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        abort_unless($opened === true, 500);

        $root = $branding['zip_root'] . '/';
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($pluginDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $sourcePath = $file->getPathname();
            $relativePath = substr($sourcePath, strlen($pluginDir) + 1);
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            $targetPath = $this->targetRelativePath($relativePath, $branding);
            $contents = file_get_contents($sourcePath);
            if ($contents === false) {
                continue;
            }

            $zip->addFromString($root . $targetPath, $this->transformContents($contents, $branding));
        }

        $zip->close();

        return [
            'path' => $tmpZipPath,
            'download_name' => $branding['download_filename'],
        ];
    }

    public function visibleCopy(): array
    {
        $branding = $this->settings();

        return [
            'plugin_name' => $branding['plugin_name'],
            'menu_label' => $branding['plugin_menu_label'],
            'app_label' => $branding['app_label'],
            'white_label_enabled' => (bool) $branding['white_label_enabled'],
        ];
    }

    public function acceptedHeaderNames(): array
    {
        $branding = $this->settings();

        return [
            'timestamp' => array_values(array_unique([$branding['header_timestamp'], 'X-MailPurse-Timestamp'])),
            'signature' => array_values(array_unique([$branding['header_signature'], 'X-MailPurse-Signature'])),
        ];
    }

    private function targetRelativePath(string $relativePath, array $branding): string
    {
        $map = [
            'mailpurse-integration.php' => $branding['main_file'],
            'src/MailPurseClient.php' => 'src/' . $branding['class_prefix'] . 'Client.php',
            'src/MailPurseQueue.php' => 'src/' . $branding['class_prefix'] . 'Queue.php',
            'src/MailPurseSettingsPage.php' => 'src/' . $branding['class_prefix'] . 'SettingsPage.php',
            'src/MailPurseHooks.php' => 'src/' . $branding['class_prefix'] . 'Hooks.php',
        ];

        return $map[$relativePath] ?? $relativePath;
    }

    private function transformContents(string $contents, array $branding): string
    {
        $replacements = [
            'mailpurse_integration_test' => $branding['admin_post_action'],
            'page=mailpurse-integration' => 'page=' . $branding['plugin_slug'],
            'X-MailPurse-Timestamp' => $branding['header_timestamp'],
            'X-MailPurse-Signature' => $branding['header_signature'],
            'namespace MailPurseIntegration;' => 'namespace ' . $branding['namespace'] . ';',
            'class MailPurseClient' => 'class ' . $branding['class_prefix'] . 'Client',
            'class MailPurseQueue' => 'class ' . $branding['class_prefix'] . 'Queue',
            'class MailPurseSettingsPage' => 'class ' . $branding['class_prefix'] . 'SettingsPage',
            'class MailPurseHooks' => 'class ' . $branding['class_prefix'] . 'Hooks',
            "'src/MailPurseClient.php'" => "'src/" . $branding['class_prefix'] . "Client.php'",
            "'src/MailPurseQueue.php'" => "'src/" . $branding['class_prefix'] . "Queue.php'",
            "'src/MailPurseSettingsPage.php'" => "'src/" . $branding['class_prefix'] . "SettingsPage.php'",
            "'src/MailPurseHooks.php'" => "'src/" . $branding['class_prefix'] . "Hooks.php'",
            'MailPurseIntegration\\MailPurseClient' => $branding['namespace'] . '\\' . $branding['class_prefix'] . 'Client',
            'MailPurseIntegration\\MailPurseQueue' => $branding['namespace'] . '\\' . $branding['class_prefix'] . 'Queue',
            'MailPurseIntegration\\MailPurseSettingsPage' => $branding['namespace'] . '\\' . $branding['class_prefix'] . 'SettingsPage',
            'MailPurseIntegration\\MailPurseHooks' => $branding['namespace'] . '\\' . $branding['class_prefix'] . 'Hooks',
            'MailPurseClient::' => $branding['class_prefix'] . 'Client::',
            'MailPurseQueue::' => $branding['class_prefix'] . 'Queue::',
            'MailPurseSettingsPage::' => $branding['class_prefix'] . 'SettingsPage::',
            'MailPurseHooks::' => $branding['class_prefix'] . 'Hooks::',
            'MAILPURSE_INTEGRATION_VERSION' => $branding['constant_prefix'] . '_VERSION',
            'MAILPURSE_INTEGRATION_PLUGIN_FILE' => $branding['constant_prefix'] . '_PLUGIN_FILE',
            'MAILPURSE_INTEGRATION_PLUGIN_DIR' => $branding['constant_prefix'] . '_PLUGIN_DIR',
            'MAILPURSE_INTEGRATION_OPTION_KEY' => $branding['constant_prefix'] . '_OPTION_KEY',
            'MAILPURSE_INTEGRATION_QUEUE_TABLE' => $branding['constant_prefix'] . '_QUEUE_TABLE',
            'MAILPURSE_INTEGRATION_CRON_HOOK' => $branding['constant_prefix'] . '_CRON_HOOK',
            'MAILPURSE_INTEGRATION_DEFAULT_EVENTS' => $branding['constant_prefix'] . '_DEFAULT_EVENTS',
            'mailpurse_integration' => $branding['settings_group'],
            "'mailpurse-integration'" => "'" . $branding['plugin_slug'] . "'",
            'mailpurse_lists_' => str_replace('-', '_', $branding['plugin_slug']) . '_lists_',
            'mailpurse_minute' => $branding['cron_schedule'],
            'mailpurse_test' => $branding['query_flag'],
            'MailPurse Integration' => $branding['plugin_settings_title'],
            'Send WordPress and WooCommerce events to MailPurse Automations.' => $branding['plugin_description'],
            'Author: MailPurse' => 'Author: ' . $branding['plugin_author'],
            'Plugin Name: MailPurse Integration' => 'Plugin Name: ' . $branding['plugin_name'],
            "'MailPurse'" => "'" . addslashes($branding['plugin_menu_label']) . "'",
            'your-mailpurse-domain.com' => 'your-domain.com',
            'MailPurse system list' => $branding['app_label'] . ' system list',
            'MailPurse when you click Test Connection.' => $branding['app_label'] . ' when you click Test Connection.',
            'MailPurse when you click Test Connection' => $branding['app_label'] . ' when you click Test Connection',
            'MailPurse' => $branding['app_label'],
            'Every minute (MailPurse)' => 'Every minute (' . $branding['plugin_name'] . ')',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $contents);
    }

    private function sanitizeSlug(mixed $value): string
    {
        $value = is_string($value) ? trim($value) : '';
        $value = Str::of($value)->lower()->replaceMatches('/[^a-z0-9\-_]+/', '-')->trim('-_')->toString();

        return $value !== '' ? $value : 'mailpurse-integration';
    }

    private function sanitizeText(mixed $value, string $fallback): string
    {
        $value = is_string($value) ? trim($value) : '';

        return $value !== '' ? str_replace(["\r", "\n"], ' ', $value) : $fallback;
    }

    private function classPrefix(string $slug): string
    {
        $prefix = Str::studly(str_replace(['-', '_'], ' ', $slug));

        return $prefix !== '' ? $prefix : 'MailPurseIntegration';
    }

    private function headerPrefix(array $settings): string
    {
        if (!empty($settings['white_label_enabled'])) {
            $label = preg_replace('/[^A-Za-z0-9]+/', '', (string) ($settings['app_label'] ?? ''));
            if (is_string($label) && $label !== '') {
                return $label;
            }

            return $this->classPrefix((string) ($settings['plugin_slug'] ?? 'plugin-integration'));
        }

        return 'MailPurse';
    }
}
