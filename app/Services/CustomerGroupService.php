<?php

namespace App\Services;

use App\Models\CustomerGroup;
use App\Models\DeliveryServer;
use App\Models\Addon;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerGroupService
{
    private function defaultRolePermissionKeys(): array
    {
        return [
            'campaigns.permissions.can_access_campaigns',
            'campaigns.permissions.can_create_campaigns',
            'campaigns.permissions.can_edit_campaigns',
            'campaigns.permissions.can_delete_campaigns',
            'ai_tools.permissions.can_access_ai_tools',
            'ai_tools.permissions.can_use_email_text_generator',
            'templates.permissions.can_access_templates',
            'templates.permissions.can_create_templates',
            'templates.permissions.can_edit_templates',
            'templates.permissions.can_delete_templates',
            'lists.permissions.can_access_lists',
            'lists.permissions.can_create_lists',
            'lists.permissions.can_edit_lists',
            'lists.permissions.can_delete_lists',
            'servers.permissions.can_access_bounce_servers',
            'servers.permissions.can_add_bounce_servers',
            'servers.permissions.can_edit_bounce_servers',
            'servers.permissions.can_delete_bounce_servers',
            'servers.permissions.can_access_reply_servers',
            'servers.permissions.can_add_reply_servers',
            'servers.permissions.can_edit_reply_servers',
            'servers.permissions.can_delete_reply_servers',
            'servers.permissions.can_access_delivery_servers',
            'servers.permissions.can_create_delivery_servers',
            'servers.permissions.can_edit_delivery_servers',
            'servers.permissions.can_delete_delivery_servers',
            'domains.tracking_domains.permissions.can_access_tracking_domains',
            'domains.tracking_domains.permissions.can_create_tracking_domains',
            'domains.tracking_domains.permissions.can_edit_tracking_domains',
            'domains.tracking_domains.permissions.can_delete_tracking_domains',
            'domains.sending_domains.permissions.can_access_sending_domains',
            'domains.sending_domains.permissions.can_create_sending_domains',
            'domains.sending_domains.permissions.can_edit_sending_domains',
            'domains.sending_domains.permissions.can_delete_sending_domains',
            'bounced_emails.access',
            'autoresponders.enabled',
            'automations.enabled',
            'email_validation.access',
            'email_validation.permissions.can_create_tools',
            'email_validation.permissions.can_edit_tools',
            'email_validation.permissions.can_delete_tools',
            'profile.permissions.can_access_profile',
            'profile.permissions.can_edit_profile',
            'support.permissions.can_access_support',
            'support.permissions.can_create_tickets',
            'support.permissions.can_reply_tickets',
            'support.permissions.can_close_tickets',
            'settings.permissions.can_access_settings',
            'settings.permissions.can_edit_settings',
            'api.permissions.can_access_api',
            'api.permissions.can_create_api_keys',
            'api.permissions.can_delete_api_keys',
            'api.permissions.can_access_api_docs',
            'campaigns.permissions.can_start_campaigns',
            'campaigns.features.ab_testing',
            'templates.permissions.can_import_templates',
            'templates.permissions.can_use_ai_creator',
            'outreach.access',
            'scraper.permissions.can_access_scraper',
            'scraper.google.can_access',
            'scraper.instagram.can_access',
            'scraper.linkedin.can_access',
            'scraper.tiktok.can_access',
            'scraper.facebook.can_access',
            'scraper.x.can_access',
        ];
    }

    private function applyDefaultRoleAccess(array $settings): array
    {
        foreach ($this->defaultRolePermissionKeys() as $key) {
            data_set($settings, $key, true);
        }

        return $settings;
    }

    /**
     * Get paginated list of customer groups.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CustomerGroup::query()->withCount('customers');

        // Apply filters
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new customer group.
     */
    public function create(array $data): CustomerGroup
    {
        $settings = $this->buildSettings($data);
        $settings = $this->applyDefaultRoleAccess($settings);
        $permissions = $this->defaultRolePermissionKeys();
        
        $customerGroup = CustomerGroup::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'permissions' => $permissions,
            'settings' => $settings,
            'is_system' => $data['is_system'] ?? false,
        ]);

        if (isset($data['allocated_delivery_server_ids']) && is_array($data['allocated_delivery_server_ids'])) {
            $allowedServerIds = DeliveryServer::query()
                ->whereNull('customer_id')
                ->whereIn('id', $data['allocated_delivery_server_ids'])
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $customerGroup->allocatedDeliveryServers()->sync($allowedServerIds);
        }

        return $customerGroup;
    }

    /**
     * Update an existing customer group.
     */
    public function update(CustomerGroup $customerGroup, array $data): CustomerGroup
    {
        $settings = $this->buildSettings($data, $customerGroup->settings ?? []);
        
        $customerGroup->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'settings' => $settings,
        ]);

        if (isset($data['allocated_delivery_server_ids']) && is_array($data['allocated_delivery_server_ids'])) {
            $allowedServerIds = DeliveryServer::query()
                ->whereNull('customer_id')
                ->whereIn('id', $data['allocated_delivery_server_ids'])
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $customerGroup->allocatedDeliveryServers()->sync($allowedServerIds);
        }

        return $customerGroup->fresh();
    }

    /**
     * Delete a customer group.
     */
    public function delete(CustomerGroup $customerGroup): bool
    {
        if ($customerGroup->is_system) {
            throw new \Exception('Cannot delete system customer group.');
        }

        return $customerGroup->delete();
    }

    /**
     * Build settings array from form data.
     */
    protected function buildSettings(array $data, array $existingSettings = []): array
    {
        $settings = array_replace_recursive($this->getDefaultSettings(), $existingSettings);

        $settings['general']['group_name'] = $data['name'] ?? ($settings['general']['group_name'] ?? '');

        // General
        if (isset($data['general'])) {
            $settings['general'] = [
                'group_name' => $data['general']['group_name'] ?? '',
                'show_articles_menu' => $data['general']['show_articles_menu'] ?? false,
                'mask_email_addresses' => $data['general']['mask_email_addresses'] ?? false,
                'notification_frequency' => $data['general']['notification_frequency'] ?? 'disabled',
                'notification_message' => $data['general']['notification_message'] ?? '',
            ];
        }

        // Servers
        if (isset($data['servers'])) {
            $settings['servers'] = [
                'limits' => [
                    'max_delivery_servers' => $data['servers']['limits']['max_delivery_servers'] ?? 0,
                    'max_bounce_servers' => $data['servers']['limits']['max_bounce_servers'] ?? 0,
                    'max_reply_servers' => $data['servers']['limits']['max_reply_servers'] ?? 0,
                    'max_feedback_loop_servers' => $data['servers']['limits']['max_feedback_loop_servers'] ?? 0,
                    'max_email_box_monitors' => $data['servers']['limits']['max_email_box_monitors'] ?? 0,
                ],
                'permissions' => [
                    'must_add_bounce_server' => $data['servers']['permissions']['must_add_bounce_server'] ?? false,
                    'must_add_reply_server' => $data['servers']['permissions']['must_add_reply_server'] ?? false,
                    'must_add_delivery_server' => $data['servers']['permissions']['must_add_delivery_server'] ?? false,
                    'can_add_delivery_servers' => $data['servers']['permissions']['can_add_delivery_servers'] ?? false,
                    'can_access_bounce_servers' => $data['servers']['permissions']['can_access_bounce_servers'] ?? false,
                    'can_add_bounce_servers' => $data['servers']['permissions']['can_add_bounce_servers'] ?? false,
                    'can_edit_bounce_servers' => $data['servers']['permissions']['can_edit_bounce_servers'] ?? false,
                    'can_delete_bounce_servers' => $data['servers']['permissions']['can_delete_bounce_servers'] ?? false,
                    'can_access_reply_servers' => $data['servers']['permissions']['can_access_reply_servers'] ?? false,
                    'can_add_reply_servers' => $data['servers']['permissions']['can_add_reply_servers'] ?? false,
                    'can_edit_reply_servers' => $data['servers']['permissions']['can_edit_reply_servers'] ?? false,
                    'can_delete_reply_servers' => $data['servers']['permissions']['can_delete_reply_servers'] ?? false,
                    'can_select_delivery_servers_for_campaigns' => $data['servers']['permissions']['can_select_delivery_servers_for_campaigns'] ?? false,
                    'can_use_system_servers' => $data['servers']['permissions']['can_use_system_servers'] ?? false,
                    'can_use_extended_mailbox_providers' => $data['servers']['permissions']['can_use_extended_mailbox_providers'] ?? false,
                ],
                'server_types' => $data['servers']['server_types'] ?? [],
                'custom_headers' => $data['servers']['custom_headers'] ?? '',
            ];
        }

        // Domains
        if (isset($data['domains'])) {
            $settings['domains']['tracking_domains'] = array_merge($settings['domains']['tracking_domains'] ?? [], [
                'must_add' => (bool) data_get($data, 'domains.tracking_domains.must_add', data_get($settings, 'domains.tracking_domains.must_add', false)),
                'can_manage' => (bool) data_get($data, 'domains.tracking_domains.can_manage', data_get($settings, 'domains.tracking_domains.can_manage', false)),
                'max_tracking_domains' => (int) data_get($data, 'domains.tracking_domains.max_tracking_domains', data_get($settings, 'domains.tracking_domains.max_tracking_domains', 0)),
            ]);

            $settings['domains']['sending_domains'] = array_merge($settings['domains']['sending_domains'] ?? [], [
                'must_add' => (bool) data_get($data, 'domains.sending_domains.must_add', data_get($settings, 'domains.sending_domains.must_add', false)),
                'can_manage' => (bool) data_get($data, 'domains.sending_domains.can_manage', data_get($settings, 'domains.sending_domains.can_manage', false)),
                'max_sending_domains' => (int) data_get($data, 'domains.sending_domains.max_sending_domains', data_get($settings, 'domains.sending_domains.max_sending_domains', 0)),
            ]);
        }

        // Lists
        if (isset($data['lists'])) {
            $settings['lists']['limits'] = array_merge($settings['lists']['limits'] ?? [], [
                'max_lists' => (int) data_get($data, 'lists.limits.max_lists', data_get($settings, 'lists.limits.max_lists', 0)),
                'max_subscribers' => (int) data_get($data, 'lists.limits.max_subscribers', data_get($settings, 'lists.limits.max_subscribers', 0)),
                'max_subscribers_per_list' => (int) data_get($data, 'lists.limits.max_subscribers_per_list', data_get($settings, 'lists.limits.max_subscribers_per_list', 0)),
                'max_forms_per_list' => (int) data_get($data, 'lists.limits.max_forms_per_list', data_get($settings, 'lists.limits.max_forms_per_list', 0)),
            ]);
        }

        // Campaigns
        if (isset($data['campaigns'])) {
            $settings['campaigns']['limits'] = array_merge($settings['campaigns']['limits'] ?? [], [
                'max_campaigns' => (int) data_get($data, 'campaigns.limits.max_campaigns', data_get($settings, 'campaigns.limits.max_campaigns', 0)),
                'max_active_campaigns' => (int) data_get($data, 'campaigns.limits.max_active_campaigns', data_get($settings, 'campaigns.limits.max_active_campaigns', 0)),
            ]);

            $settings['campaigns']['features'] = array_merge($settings['campaigns']['features'] ?? [], [
                'ab_testing' => (bool) data_get($data, 'campaigns.features.ab_testing', data_get($settings, 'campaigns.features.ab_testing', false)),
            ]);

            $incomingPermissions = (array) data_get($data, 'campaigns.permissions', []);
            if (!empty($incomingPermissions)) {
                $settings['campaigns']['permissions'] = array_merge(
                    $settings['campaigns']['permissions'] ?? [],
                    $incomingPermissions
                );
            }
        }

        // Auto Responders
        if (isset($data['autoresponders'])) {
            $settings['autoresponders'] = [
                'enabled' => (bool) data_get($data, 'autoresponders.enabled', false),
                'max_autoresponders' => (int) data_get($data, 'autoresponders.max_autoresponders', 0),
            ];
        }

        if (isset($data['automations'])) {
            $settings['automations'] = [
                'enabled' => (bool) data_get($data, 'automations.enabled', false),
            ];
        }

        if (isset($data['outreach'])) {
            $settings['outreach'] = array_merge($settings['outreach'] ?? [], [
                'access' => (bool) data_get($data, 'outreach.access', data_get($settings, 'outreach.access', false)),
                'max_campaigns' => (int) data_get($data, 'outreach.max_campaigns', data_get($settings, 'outreach.max_campaigns', 0)),
                'max_sequences_per_campaign' => (int) data_get($data, 'outreach.max_sequences_per_campaign', data_get($settings, 'outreach.max_sequences_per_campaign', 0)),
                'max_leads_per_campaign' => (int) data_get($data, 'outreach.max_leads_per_campaign', data_get($settings, 'outreach.max_leads_per_campaign', 0)),
            ]);
        }

        // Surveys
        if (isset($data['surveys'])) {
            $settings['surveys'] = [
                'limits' => [
                    'max_surveys' => $data['surveys']['limits']['max_surveys'] ?? 0,
                    'max_responders' => $data['surveys']['limits']['max_responders'] ?? 0,
                    'max_responders_per_survey' => $data['surveys']['limits']['max_responders_per_survey'] ?? 0,
                ],
                'permissions' => [
                    'delete_surveys' => $data['surveys']['permissions']['delete_surveys'] ?? false,
                    'delete_responders' => $data['surveys']['permissions']['delete_responders'] ?? false,
                    'edit_responders' => $data['surveys']['permissions']['edit_responders'] ?? false,
                    'segment_surveys' => $data['surveys']['permissions']['segment_surveys'] ?? false,
                    'export_responders' => $data['surveys']['permissions']['export_responders'] ?? false,
                    'show_7_day_activity' => $data['surveys']['permissions']['show_7_day_activity'] ?? false,
                ],
            ];
        }

        // Sending Quota
        if (isset($data['sending_quota'])) {
            $settings['sending_quota'] = array_merge($settings['sending_quota'] ?? [], [
                'daily_quota' => (int) data_get($data, 'sending_quota.daily_quota', data_get($settings, 'sending_quota.daily_quota', 0)),
                'weekly_quota' => (int) data_get($data, 'sending_quota.weekly_quota', data_get($settings, 'sending_quota.weekly_quota', 0)),
                'monthly_quota' => (int) data_get($data, 'sending_quota.monthly_quota', data_get($settings, 'sending_quota.monthly_quota', 0)),
            ]);
        }

        // Usage Counters
        if (isset($data['usage_counters'])) {
            $settings['usage_counters'] = [
                'count_campaign_emails' => $data['usage_counters']['count_campaign_emails'] ?? true,
                'count_test_emails' => $data['usage_counters']['count_test_emails'] ?? false,
                'count_transactional_emails' => $data['usage_counters']['count_transactional_emails'] ?? false,
                'count_list_emails' => $data['usage_counters']['count_list_emails'] ?? false,
                'count_template_test_emails' => $data['usage_counters']['count_template_test_emails'] ?? false,
                'count_campaign_giveup_emails' => $data['usage_counters']['count_campaign_giveup_emails'] ?? false,
            ];
        }

        // CDN
        if (isset($data['cdn'])) {
            $settings['cdn'] = [
                'enabled' => $data['cdn']['enabled'] ?? false,
                'subdomain' => $data['cdn']['subdomain'] ?? '',
                'use_for_assets' => $data['cdn']['use_for_assets'] ?? false,
            ];
        }

        // API
        if (isset($data['api'])) {
            $settings['api'] = [
                'enabled' => $data['api']['enabled'] ?? false,
            ];
        }

        // Subaccounts
        if (isset($data['subaccounts'])) {
            $settings['subaccounts'] = [
                'enabled' => $data['subaccounts']['enabled'] ?? false,
                'max_subaccounts' => $data['subaccounts']['max_subaccounts'] ?? 0,
            ];
        }

        // Landing Pages
        if (isset($data['landing_pages'])) {
            $settings['landing_pages'] = [
                'max_landing_pages' => $data['landing_pages']['max_landing_pages'] ?? 0,
            ];
        }

        // Email Validation
        if (isset($data['email_validation'])) {
            $settings['email_validation'] = array_merge($settings['email_validation'] ?? [], [
                'access' => (bool) data_get($data, 'email_validation.access', data_get($settings, 'email_validation.access', false)),
                'must_add' => (bool) data_get($data, 'email_validation.must_add', data_get($settings, 'email_validation.must_add', false)),
                'max_tools' => (int) data_get($data, 'email_validation.max_tools', data_get($settings, 'email_validation.max_tools', 0)),
                'monthly_limit' => (int) data_get($data, 'email_validation.monthly_limit', data_get($settings, 'email_validation.monthly_limit', 0)),
            ]);
        }

        if (isset($data['messages'])) {
            $settings['messages'] = array_replace_recursive(
                $settings['messages'] ?? [],
                (array) $data['messages']
            );
        }

        if (isset($data['ai'])) {
            $settings['ai'] = array_merge($settings['ai'] ?? [], [
                'must_use_own_keys' => (bool) data_get($data, 'ai.must_use_own_keys', data_get($settings, 'ai.must_use_own_keys', false)),
                'token_limit' => (int) data_get($data, 'ai.token_limit', data_get($settings, 'ai.token_limit', 0)),
                'image_credits' => (int) data_get($data, 'ai.image_credits', data_get($settings, 'ai.image_credits', 0)),
            ]);
        }

        if (isset($data['integrations'])) {
            $settings['integrations'] = array_merge($settings['integrations'] ?? [], [
                'permissions' => array_merge($settings['integrations']['permissions'] ?? [], [
                    'can_access_google' => (bool) data_get($data, 'integrations.permissions.can_access_google', data_get($settings, 'integrations.permissions.can_access_google', false)),
                ]),
            ]);
        }

        if (isset($data['super_scrape'])) {
            $settings['super_scrape'] = array_merge($settings['super_scrape'] ?? [], [
                'access'          => (bool) data_get($data, 'super_scrape.access', data_get($settings, 'super_scrape.access', true)),
                'google_access'   => (bool) data_get($data, 'super_scrape.google_access', data_get($settings, 'super_scrape.google_access', true)),
                'instagram_access'=> (bool) data_get($data, 'super_scrape.instagram_access', data_get($settings, 'super_scrape.instagram_access', false)),
                'linkedin_access' => (bool) data_get($data, 'super_scrape.linkedin_access', data_get($settings, 'super_scrape.linkedin_access', false)),
                'tiktok_access'   => (bool) data_get($data, 'super_scrape.tiktok_access', data_get($settings, 'super_scrape.tiktok_access', false)),
                'facebook_access' => (bool) data_get($data, 'super_scrape.facebook_access', data_get($settings, 'super_scrape.facebook_access', false)),
                'x_access'        => (bool) data_get($data, 'super_scrape.x_access', data_get($settings, 'super_scrape.x_access', false)),
                'monthly_credits' => (int) data_get($data, 'super_scrape.monthly_credits', data_get($settings, 'super_scrape.monthly_credits', 0)),
                'max_jobs'        => (int) data_get($data, 'super_scrape.max_jobs', data_get($settings, 'super_scrape.max_jobs', 0)),
            ]);
        }

        return $settings;
    }

    /**
     * Get default settings structure.
     */
    public function getDefaultSettings(): array
    {
        return [
            'messages' => [
                'access' => [
                    'default' => '',
                ],
                'limits' => [],
            ],
            'ai' => [
                'must_use_own_keys' => false,
                'token_limit' => 0,
                'image_credits' => 0,
            ],
            'integrations' => [
                'permissions' => [
                    'can_access_google' => false,
                ],
            ],
            'general' => [
                'group_name' => '',
                'show_articles_menu' => false,
                'mask_email_addresses' => false,
                'notification_frequency' => 'disabled',
                'notification_message' => '',
            ],
            'servers' => [
                'limits' => [
                    'max_delivery_servers' => 0,
                    'max_bounce_servers' => 0,
                    'max_reply_servers' => 0,
                    'max_feedback_loop_servers' => 0,
                    'max_email_box_monitors' => 0,
                ],
                'permissions' => [
                    'must_add_bounce_server' => false,
                    'must_add_reply_server' => false,
                    'must_add_delivery_server' => false,
                    'can_add_delivery_servers' => false,
                    'can_access_bounce_servers' => false,
                    'can_add_bounce_servers' => false,
                    'can_edit_bounce_servers' => false,
                    'can_delete_bounce_servers' => false,
                    'can_access_reply_servers' => false,
                    'can_add_reply_servers' => false,
                    'can_edit_reply_servers' => false,
                    'can_delete_reply_servers' => false,
                    'can_select_delivery_servers_for_campaigns' => false,
                    'can_use_system_servers' => false,
                    'can_use_extended_mailbox_providers' => false,
                ],
                'server_types' => [],
                'custom_headers' => '',
            ],
            'domains' => [
                'tracking_domains' => [
                    'must_add' => false,
                    'can_manage' => false,
                    'select_for_servers' => false,
                    'select_for_campaigns' => false,
                    'max_tracking_domains' => 0,
                ],
                'sending_domains' => [
                    'must_add' => false,
                    'can_manage' => false,
                    'max_sending_domains' => 0,
                ],
            ],
            'lists' => [
                'permissions' => [
                    'import_subscribers' => false,
                    'export_subscribers' => false,
                    'copy_subscribers' => false,
                    'edit_subscribers' => false,
                    'delete_lists' => false,
                    'delete_subscribers' => false,
                    'segment_lists' => false,
                    'create_list_from_filter' => false,
                    'show_7_day_activity' => false,
                ],
                'limits' => [
                    'max_lists' => 0,
                    'max_subscribers' => 0,
                    'max_subscribers_per_list' => 0,
                    'max_forms_per_list' => 0,
                    'max_custom_fields' => 0,
                    'max_segment_conditions' => 0,
                    'segment_wait_timeout' => 0,
                ],
                'optin' => [
                    'force_optin' => '',
                    'force_optout' => '',
                    'force_double_optin_confirmation' => false,
                ],
                'blacklist_behavior' => [
                    'mark_blacklisted_as_confirmed' => false,
                    'use_own_blacklist' => false,
                ],
            ],
            'campaigns' => [
                'limits' => [
                    'max_campaigns' => 0,
                    'max_active_campaigns' => 0,
                ],
                'features' => [
                    'autoresponders' => false,
                    'recurring_campaigns' => false,
                    'timewarp' => false,
                    'embed_images' => false,
                    'ab_testing' => false,
                ],
                'permissions' => [
                    'can_create_campaigns' => false,
                    'can_edit_campaigns' => false,
                    'can_start_campaigns' => false,
                    'can_delete_campaigns' => false,
                    'delete_own_campaigns' => false,
                    'require_approval' => false,
                    'verify_sending_domains' => false,
                ],
                'analytics' => [
                    'show_geo_opens' => false,
                    'show_24h_graph' => false,
                    'show_top_domain_graph' => false,
                ],
                'complaint_limits' => [
                    'max_bounce_rate' => 0,
                    'max_complaint_rate' => 0,
                ],
                'headers' => [
                    'feedback_id_format' => '',
                    'unsubscribe_email' => '',
                    'email_header_html' => '',
                    'email_footer_html' => '',
                ],
            ],
            'surveys' => [
                'limits' => [
                    'max_surveys' => 0,
                    'max_responders' => 0,
                    'max_responders_per_survey' => 0,
                ],
                'permissions' => [
                    'delete_surveys' => false,
                    'delete_responders' => false,
                    'edit_responders' => false,
                    'segment_surveys' => false,
                    'export_responders' => false,
                    'show_7_day_activity' => false,
                ],
            ],
            'sending_quota' => [
                'quota' => 0,
                'time_value' => 1,
                'time_unit' => 'day',
                'wait_for_expire' => false,
                'action_on_reach' => 'none',
                'move_to_group_id' => null,
                'hourly_quota' => 0,
                'daily_quota' => 0,
                'weekly_quota' => 0,
                'monthly_quota' => 0,
                'notifications' => [
                    'enable' => false,
                    'percent_threshold' => 80,
                    'email_template' => '',
                ],
            ],
            'autoresponders' => [
                'enabled' => false,
                'max_autoresponders' => 0,
            ],
            'automations' => [
                'enabled' => false,
            ],
            'outreach' => [
                'access' => Addon::isInstalled('cold-email-outreach') && Addon::isActive('cold-email-outreach'),
                'max_campaigns' => 0,
                'max_sequences_per_campaign' => 0,
                'max_leads_per_campaign' => 0,
            ],
            'usage_counters' => [
                'count_campaign_emails' => true,
                'count_test_emails' => false,
                'count_transactional_emails' => false,
                'count_list_emails' => false,
                'count_template_test_emails' => false,
                'count_campaign_giveup_emails' => false,
            ],
            'cdn' => [
                'enabled' => false,
                'subdomain' => '',
                'use_for_assets' => false,
            ],
            'api' => [
                'enabled' => false,
            ],
            'subaccounts' => [
                'enabled' => false,
                'max_subaccounts' => 0,
            ],
            'landing_pages' => [
                'max_landing_pages' => 0,
            ],
            'email_validation' => [
                'access' => false,
                'must_add' => false,
                'max_tools' => 0,
                'monthly_limit' => 0,
            ],
        ];
    }

    /**
     * Get effective settings for a customer group.
     */
    public function getEffectiveSettings(CustomerGroup $customerGroup): array
    {
        return array_replace_recursive(
            $this->getDefaultSettings(),
            $customerGroup->settings ?? []
        );
    }
}

