<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;

class AccessibilityControlController extends Controller
{
    private function customerPermissionMatrix(): array
    {
        $actions = [
            ['key' => 'access', 'label' => 'Access'],
            ['key' => 'create', 'label' => 'Create'],
            ['key' => 'edit', 'label' => 'Edit'],
            ['key' => 'delete', 'label' => 'Delete'],
        ];

        $modules = [
            [
                'label' => 'Campaigns',
                'perms' => [
                    'access' => 'campaigns.permissions.can_access_campaigns',
                    'create' => 'campaigns.permissions.can_create_campaigns',
                    'edit' => 'campaigns.permissions.can_edit_campaigns',
                    'delete' => 'campaigns.permissions.can_delete_campaigns',
                ],
            ],
            [
                'label' => 'AI Tools',
                'perms' => [
                    'access' => 'ai_tools.permissions.can_access_ai_tools',
                    'create' => 'ai_tools.permissions.can_use_email_text_generator',
                ],
            ],
            [
                'label' => 'Templates',
                'perms' => [
                    'access' => 'templates.permissions.can_access_templates',
                    'create' => 'templates.permissions.can_create_templates',
                    'edit' => 'templates.permissions.can_edit_templates',
                    'delete' => 'templates.permissions.can_delete_templates',
                ],
            ],
            [
                'label' => 'Email Lists',
                'perms' => [
                    'access' => 'lists.permissions.can_access_lists',
                    'create' => 'lists.permissions.can_create_lists',
                    'edit' => 'lists.permissions.can_edit_lists',
                    'delete' => 'lists.permissions.can_delete_lists',
                ],
            ],
            [
                'label' => 'Bounce Servers',
                'perms' => [
                    'access' => 'servers.permissions.can_access_bounce_servers',
                    'create' => 'servers.permissions.can_add_bounce_servers',
                    'edit' => 'servers.permissions.can_edit_bounce_servers',
                    'delete' => 'servers.permissions.can_delete_bounce_servers',
                ],
            ],
            [
                'label' => 'Reply Servers',
                'perms' => [
                    'access' => 'servers.permissions.can_access_reply_servers',
                    'create' => 'servers.permissions.can_add_reply_servers',
                    'edit' => 'servers.permissions.can_edit_reply_servers',
                    'delete' => 'servers.permissions.can_delete_reply_servers',
                ],
            ],
            [
                'label' => 'Delivery Servers',
                'perms' => [
                    'access' => 'servers.permissions.can_access_delivery_servers',
                    'create' => 'servers.permissions.can_create_delivery_servers',
                    'edit' => 'servers.permissions.can_edit_delivery_servers',
                    'delete' => 'servers.permissions.can_delete_delivery_servers',
                ],
            ],
            [
                'label' => 'Tracking Domains',
                'perms' => [
                    'access' => 'domains.tracking_domains.permissions.can_access_tracking_domains',
                    'create' => 'domains.tracking_domains.permissions.can_create_tracking_domains',
                    'edit' => 'domains.tracking_domains.permissions.can_edit_tracking_domains',
                    'delete' => 'domains.tracking_domains.permissions.can_delete_tracking_domains',
                ],
            ],
            [
                'label' => 'Sending Domains',
                'perms' => [
                    'access' => 'domains.sending_domains.permissions.can_access_sending_domains',
                    'create' => 'domains.sending_domains.permissions.can_create_sending_domains',
                    'edit' => 'domains.sending_domains.permissions.can_edit_sending_domains',
                    'delete' => 'domains.sending_domains.permissions.can_delete_sending_domains',
                ],
            ],
            [
                'label' => 'Bounced Emails',
                'perms' => [
                    'access' => 'bounced_emails.access',
                ],
            ],
            [
                'label' => 'Auto Responders',
                'perms' => [
                    'access' => 'autoresponders.enabled',
                ],
            ],
            [
                'label' => 'Automations',
                'perms' => [
                    'access' => 'automations.enabled',
                ],
            ],
            [
                'label' => 'Email Validation',
                'perms' => [
                    'access' => 'email_validation.access',
                    'create' => 'email_validation.permissions.can_create_tools',
                    'edit' => 'email_validation.permissions.can_edit_tools',
                    'delete' => 'email_validation.permissions.can_delete_tools',
                ],
            ],
            [
                'label' => 'Profile',
                'perms' => [
                    'access' => 'profile.permissions.can_access_profile',
                    'edit' => 'profile.permissions.can_edit_profile',
                ],
            ],
            [
                'label' => 'Support',
                'perms' => [
                    'access' => 'support.permissions.can_access_support',
                    'create' => 'support.permissions.can_create_tickets',
                    'edit' => 'support.permissions.can_reply_tickets',
                    'delete' => 'support.permissions.can_close_tickets',
                ],
            ],
            [
                'label' => 'Settings',
                'perms' => [
                    'access' => 'settings.permissions.can_access_settings',
                    'edit' => 'settings.permissions.can_edit_settings',
                ],
            ],
            [
                'label' => 'API',
                'perms' => [
                    'access' => 'api.permissions.can_access_api',
                    'create' => 'api.permissions.can_create_api_keys',
                    'delete' => 'api.permissions.can_delete_api_keys',
                ],
            ],
            [
                'label' => 'API Docs',
                'perms' => [
                    'access' => 'api.permissions.can_access_api_docs',
                ],
            ],
            [
                'label' => 'Lead Scraper (SuperScrape)',
                'perms' => [
                    'access' => 'scraper.permissions.can_access_scraper',
                ],
            ],
        ];

        $special = [
            [
                'label' => 'Campaigns: Start / Pause / Resume / Rerun',
                'perms' => [
                    'access' => 'campaigns.permissions.can_start_campaigns',
                ],
            ],
            [
                'label' => 'Campaigns: AB Testing',
                'perms' => [
                    'access' => 'campaigns.features.ab_testing',
                ],
            ],
            [
                'label' => 'Templates: Import Templates',
                'perms' => [
                    'access' => 'templates.permissions.can_import_templates',
                ],
            ],
            [
                'label' => 'Templates: AI Creator',
                'perms' => [
                    'access' => 'templates.permissions.can_use_ai_creator',
                ],
            ],
            [
                'label' => 'Lead Scraper: Google',
                'perms' => [
                    'access' => 'scraper.google.can_access',
                ],
            ],
            [
                'label' => 'Lead Scraper: Instagram',
                'perms' => [
                    'access' => 'scraper.instagram.can_access',
                ],
            ],
            [
                'label' => 'Lead Scraper: LinkedIn',
                'perms' => [
                    'access' => 'scraper.linkedin.can_access',
                ],
            ],
            [
                'label' => 'Lead Scraper: TikTok',
                'perms' => [
                    'access' => 'scraper.tiktok.can_access',
                ],
            ],
            [
                'label' => 'Lead Scraper: Facebook',
                'perms' => [
                    'access' => 'scraper.facebook.can_access',
                ],
            ],
            [
                'label' => 'Lead Scraper: X (Twitter)',
                'perms' => [
                    'access' => 'scraper.x.can_access',
                ],
            ],
        ];

        $keys = [];
        foreach (array_merge($modules, $special) as $row) {
            foreach (($row['perms'] ?? []) as $key) {
                if (is_string($key) && trim($key) !== '') {
                    $keys[] = $key;
                }
            }
        }
        $keys = array_values(array_unique($keys));

        return [
            'actions' => $actions,
            'modules' => $modules,
            'special' => $special,
            'keys' => $keys,
        ];
    }

    public function index(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user) {
            abort(403, 'You do not have access to this page.');
        }

        if (!(method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            if (!method_exists($user, 'hasAdminAbility') || !$user->hasAdminAbility('admin.accessibility_control.access')) {
                abort(403, 'You do not have access to this page.');
            }
        }

        $customerGroups = CustomerGroup::query()
            ->orderBy('name')
            ->get();

        return view('admin.accessibility-control.index', compact('customerGroups'));
    }

    public function create(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user) {
            abort(403, 'You do not have access to this page.');
        }

        if (!(method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            if (!method_exists($user, 'hasAdminAbility') || !$user->hasAdminAbility('admin.accessibility_control.access')) {
                abort(403, 'You do not have access to this page.');
            }
        }

        $customerGroups = CustomerGroup::query()->orderBy('name')->get();

        $customerMatrix = $this->customerPermissionMatrix();
        $customerActions = $customerMatrix['actions'];
        $customerModules = $customerMatrix['modules'];
        $customerSpecial = $customerMatrix['special'];
        $customerKeys = $customerMatrix['keys'];

        $selectedRoleId = $request->query('target_role_id');
        $selectedRoleId = is_null($selectedRoleId) ? null : (int) $selectedRoleId;

        $selectedPermissions = [];

        if ($selectedRoleId) {
            $targetGroup = CustomerGroup::query()->find($selectedRoleId);
            if ($targetGroup) {
                $selectedPermissions = (array) ($targetGroup->permissions ?? []);

                $settings = (array) ($targetGroup->settings ?? []);
                foreach ($customerKeys as $key) {
                    if ((bool) data_get($settings, $key, false)) {
                        $selectedPermissions[] = $key;
                    }
                }
                $selectedPermissions = array_values(array_unique($selectedPermissions));
            }
        }

        $modules = [
            ['key' => 'admin.dashboard', 'label' => 'Dashboard'],
            ['key' => 'admin.activities', 'label' => 'Activities'],
            ['key' => 'admin.notifications', 'label' => 'Notifications'],
            ['key' => 'admin.invoices', 'label' => 'Invoices'],
            ['key' => 'admin.users', 'label' => 'Admin Users'],
            ['key' => 'admin.customers', 'label' => 'Customers'],
            ['key' => 'admin.support_tickets', 'label' => 'Support Tickets'],
            ['key' => 'admin.blog_posts', 'label' => 'Blog Posts'],
            ['key' => 'admin.customer_groups', 'label' => 'Customer Groups'],
            ['key' => 'admin.settings', 'label' => 'Settings'],
            ['key' => 'admin.ai_tools', 'label' => 'AI Tools'],
            ['key' => 'admin.api', 'label' => 'API'],
            ['key' => 'admin.api_docs', 'label' => 'API Docs'],
            ['key' => 'admin.translations', 'label' => 'Translations'],
            ['key' => 'admin.campaigns', 'label' => 'Campaigns'],
            ['key' => 'admin.lists', 'label' => 'Email Lists'],
            ['key' => 'admin.email_validation', 'label' => 'Email Validation'],
            ['key' => 'admin.delivery_servers', 'label' => 'Delivery Servers'],
            ['key' => 'admin.sending_domains', 'label' => 'Sending Domains'],
            ['key' => 'admin.tracking_domains', 'label' => 'Tracking Domains'],
            ['key' => 'admin.bounce_servers', 'label' => 'Bounce Servers'],
            ['key' => 'admin.reply_servers', 'label' => 'Reply Servers'],
            ['key' => 'admin.bounced_emails', 'label' => 'Bounced Emails'],
            ['key' => 'admin.search', 'label' => 'Search'],
            ['key' => 'admin.profile', 'label' => 'Profile'],
            ['key' => 'admin.payment_methods', 'label' => 'Payment Methods'],
            ['key' => 'admin.vat_tax', 'label' => 'VAT/Tax'],
            ['key' => 'admin.plans', 'label' => 'Plans'],
            ['key' => 'admin.coupons', 'label' => 'Coupons'],
            ['key' => 'admin.accessibility_control', 'label' => 'Accessibility Control'],
        ];

        $actions = [
            ['key' => 'access', 'label' => 'Access'],
            ['key' => 'create', 'label' => 'Create'],
            ['key' => 'edit', 'label' => 'Edit'],
            ['key' => 'delete', 'label' => 'Delete'],
        ];

        $special = [
            ['key' => 'admin.delivery_servers.make_primary', 'label' => 'Delivery Servers: Make Primary'],
            ['key' => 'admin.delivery_servers.test', 'label' => 'Delivery Servers: Test Connection'],
            ['key' => 'admin.delivery_servers.resend_verification', 'label' => 'Delivery Servers: Resend Verification'],
        ];

        return view('admin.accessibility-control.create', compact(
            'customerGroups',
            'modules',
            'actions',
            'special',
            'customerActions',
            'customerModules',
            'customerSpecial',
            'selectedRoleId',
            'selectedPermissions'
        ));
    }

    public function update(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user) {
            abort(403, 'You do not have access to this page.');
        }

        if (!(method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            if (!method_exists($user, 'hasAdminAbility') || !$user->hasAdminAbility('admin.accessibility_control.edit')) {
                abort(403, 'You do not have access to this page.');
            }
        }

        $validated = $request->validate([
            'target_role_id' => ['required', 'integer', 'exists:customer_groups,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $permissions = (array) ($validated['permissions'] ?? []);
        $permissions = array_values(array_unique(array_filter($permissions, fn ($p) => is_string($p) && trim($p) !== '')));

        $customerGroup = CustomerGroup::query()->findOrFail((int) $validated['target_role_id']);

        $matrix = $this->customerPermissionMatrix();
        $keys = $matrix['keys'];

        $settings = (array) ($customerGroup->settings ?? []);
        foreach ($keys as $key) {
            data_set($settings, $key, in_array($key, $permissions, true));
        }

        $customerGroup->settings = $settings;
        $customerGroup->permissions = $permissions;
        $customerGroup->save();

        return redirect()
            ->route('admin.accessibility-control.index')
            ->with('success', 'RBAC permissions saved successfully.');
    }
}
