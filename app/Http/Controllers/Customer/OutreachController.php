<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\OutreachCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OutreachController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (!Addon::isActive('cold-email-outreach')) {
            return redirect()->route('customer.dashboard')
                ->with('error', __('The Cold Email Outreach addon is not active.'));
        }

        $customer = auth('customer')->user();
        if (!$customer || !$customer->groupAllows('outreach.access')) {
            $message = $customer?->groupSetting('messages.access.outreach.access', $customer?->groupSetting('messages.access.default'));

            return redirect()->route('customer.dashboard')
                ->with('error', is_string($message) && trim($message) !== '' ? $message : __('You do not have access to Outreach.'));
        }

        $campaigns = OutreachCampaign::where('customer_id', $customer->id)
            ->latest()
            ->get();

        return view('customer.outreach.index', compact('campaigns'));
    }
}
