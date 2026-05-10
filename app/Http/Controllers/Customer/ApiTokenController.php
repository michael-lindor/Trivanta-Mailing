<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    private function availableAbilities(): array
    {
        return [
            [
                'label' => 'Full Access',
                'options' => [
                    '*' => 'Full access (*)',
                ],
            ],
            [
                'label' => 'Campaigns',
                'options' => [
                    'campaigns.permissions.can_access_campaigns' => 'Campaigns: Access',
                    'campaigns.permissions.can_create_campaigns' => 'Campaigns: Create',
                    'campaigns.permissions.can_edit_campaigns' => 'Campaigns: Edit',
                    'campaigns.permissions.can_delete_campaigns' => 'Campaigns: Delete',
                    'campaigns.permissions.can_start_campaigns' => 'Campaigns: Start / Pause / Resume / Rerun',
                ],
            ],
            [
                'label' => 'Automation',
                'options' => [
                    'autoresponders.enabled' => 'Auto Responders',
                    'automations.enabled' => 'Automations',
                ],
            ],
            [
                'label' => 'Templates and Lists',
                'options' => [
                    'templates.permissions.can_access_templates' => 'Templates: Access',
                    'templates.permissions.can_create_templates' => 'Templates: Create',
                    'templates.permissions.can_edit_templates' => 'Templates: Edit',
                    'templates.permissions.can_delete_templates' => 'Templates: Delete',
                    'templates.permissions.can_import_templates' => 'Templates: Import',
                    'templates.permissions.can_use_ai_creator' => 'Templates: AI Creator',
                    'lists.permissions.can_access_lists' => 'Lists: Access',
                    'lists.permissions.can_create_lists' => 'Lists: Create',
                    'lists.permissions.can_edit_lists' => 'Lists: Edit',
                    'lists.permissions.can_delete_lists' => 'Lists: Delete',
                ],
            ],
            [
                'label' => 'Servers and Domains',
                'options' => [
                    'servers.permissions.can_access_delivery_servers' => 'Delivery Servers: Access',
                    'servers.permissions.can_create_delivery_servers' => 'Delivery Servers: Create',
                    'servers.permissions.can_edit_delivery_servers' => 'Delivery Servers: Edit',
                    'servers.permissions.can_delete_delivery_servers' => 'Delivery Servers: Delete',
                    'servers.permissions.can_access_bounce_servers' => 'Bounce Servers: Access',
                    'servers.permissions.can_add_bounce_servers' => 'Bounce Servers: Create',
                    'servers.permissions.can_edit_bounce_servers' => 'Bounce Servers: Edit',
                    'servers.permissions.can_delete_bounce_servers' => 'Bounce Servers: Delete',
                    'servers.permissions.can_access_reply_servers' => 'Reply Servers: Access',
                    'servers.permissions.can_add_reply_servers' => 'Reply Servers: Create',
                    'servers.permissions.can_edit_reply_servers' => 'Reply Servers: Edit',
                    'servers.permissions.can_delete_reply_servers' => 'Reply Servers: Delete',
                    'domains.sending_domains.permissions.can_access_sending_domains' => 'Sending Domains: Access',
                    'domains.sending_domains.permissions.can_create_sending_domains' => 'Sending Domains: Create',
                    'domains.sending_domains.permissions.can_edit_sending_domains' => 'Sending Domains: Edit',
                    'domains.sending_domains.permissions.can_delete_sending_domains' => 'Sending Domains: Delete',
                    'domains.tracking_domains.permissions.can_access_tracking_domains' => 'Tracking Domains: Access',
                    'domains.tracking_domains.permissions.can_create_tracking_domains' => 'Tracking Domains: Create',
                    'domains.tracking_domains.permissions.can_edit_tracking_domains' => 'Tracking Domains: Edit',
                    'domains.tracking_domains.permissions.can_delete_tracking_domains' => 'Tracking Domains: Delete',
                ],
            ],
            [
                'label' => 'Other',
                'options' => [
                    'bounced_emails.access' => 'Bounced Emails',
                    'email_validation.access' => 'Email Validation: Access',
                    'email_validation.permissions.can_create_tools' => 'Email Validation: Create',
                    'email_validation.permissions.can_edit_tools' => 'Email Validation: Edit',
                    'email_validation.permissions.can_delete_tools' => 'Email Validation: Delete',
                    'support.permissions.can_access_support' => 'Support: Access',
                    'support.permissions.can_create_tickets' => 'Support: Create Tickets',
                    'support.permissions.can_reply_tickets' => 'Support: Reply Tickets',
                    'support.permissions.can_close_tickets' => 'Support: Close Tickets',
                    'profile.permissions.can_access_profile' => 'Profile: Access',
                    'profile.permissions.can_edit_profile' => 'Profile: Edit',
                    'settings.permissions.can_access_settings' => 'Settings: Access',
                    'settings.permissions.can_edit_settings' => 'Settings: Edit',
                    'api.permissions.can_access_api' => 'API: Access',
                    'api.permissions.can_create_api_keys' => 'API: Create API Keys',
                    'api.permissions.can_delete_api_keys' => 'API: Delete API Keys',
                    'api.permissions.can_access_api_docs' => 'API Docs: Access',
                    'ai_tools.permissions.can_access_ai_tools' => 'AI Tools: Access',
                    'ai_tools.permissions.can_use_email_text_generator' => 'AI Tools: Email Text Generator',
                ],
            ],
        ];
    }

    public function __construct()
    {
        $this->middleware('customer.access:api.permissions.can_access_api')->only(['index']);
        $this->middleware('customer.access:api.permissions.can_create_api_keys')->only(['store']);
        $this->middleware('customer.access:api.permissions.can_delete_api_keys')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        abort_if(!$customer, 403);

        $tokens = $customer->tokens()->latest()->get();
        $availableAbilityGroups = $this->availableAbilities();

        return view('customer.api.index', compact('tokens', 'availableAbilityGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = auth('customer')->user();
        abort_if(!$customer, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', 'max:255'],
        ]);

        $allowedAbilities = collect($this->availableAbilities())
            ->pluck('options')
            ->flatMap(fn (array $options) => array_keys($options))
            ->all();

        $abilities = array_values(array_unique(array_filter(
            (array) ($validated['abilities'] ?? ['*']),
            fn ($v) => is_string($v) && trim($v) !== '' && in_array(trim($v), $allowedAbilities, true)
        )));

        if ($abilities === []) {
            $abilities = ['*'];
        }

        $token = $customer->createToken($validated['name'], $abilities);

        return redirect()
            ->route('customer.api.index')
            ->with('success', 'API key created. Copy it now — it will not be shown again.')
            ->with('plain_text_token', $token->plainTextToken);
    }

    public function destroy(Request $request, int $tokenId): RedirectResponse
    {
        $customer = auth('customer')->user();
        abort_if(!$customer, 403);

        $token = $customer->tokens()->where('id', $tokenId)->firstOrFail();
        $token->delete();

        return redirect()
            ->route('customer.api.index')
            ->with('success', 'API key revoked.');
    }
}
