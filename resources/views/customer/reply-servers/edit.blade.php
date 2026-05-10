@extends('layouts.customer')

@section('title', 'Edit Reply Server')
@section('page-title', 'Edit Reply Server')

@section('content')
<div class="space-y-6">
    <nav aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('customer.dashboard') }}" class="font-medium text-gray-600 transition hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400">{{ __('Home') }}</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('customer.reply-servers.index') }}" class="font-medium text-gray-600 transition hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400">{{ __('Reply Servers') }}</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('customer.reply-servers.show', $replyServer) }}" class="font-medium text-gray-600 transition hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400">{{ $replyServer->name }}</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-gray-900 dark:text-gray-100">{{ __('Edit') }}</li>
        </ol>
    </nav>
    <x-card>
        <form method="POST" action="{{ route('customer.reply-servers.update', $replyServer) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
            <input name="name" value="{{ old('name', $replyServer->name) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reply Domain</label>
            <input name="reply_domain" value="{{ old('reply_domain', $replyServer->reply_domain) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="reply.yourdomain.com">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Protocol</label>
                <select name="protocol" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <option value="imap" {{ old('protocol', $replyServer->protocol) === 'imap' ? 'selected' : '' }}>IMAP</option>
                    <option value="pop3" {{ old('protocol', $replyServer->protocol) === 'pop3' ? 'selected' : '' }}>POP3</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Encryption</label>
                <select name="encryption" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    <option value="ssl" {{ old('encryption', $replyServer->encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="tls" {{ old('encryption', $replyServer->encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="none" {{ old('encryption', $replyServer->encryption) === 'none' ? 'selected' : '' }}>None</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hostname</label>
                <input name="hostname" value="{{ old('hostname', $replyServer->hostname) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Port</label>
                <input name="port" type="number" value="{{ old('port', $replyServer->port) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                <input name="username" value="{{ old('username', $replyServer->username) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password (leave blank to keep)</label>
                <input name="password" type="password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mailbox</label>
                <input name="mailbox" value="{{ old('mailbox', $replyServer->mailbox) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max emails per batch</label>
                <input name="max_emails_per_batch" type="number" value="{{ old('max_emails_per_batch', $replyServer->max_emails_per_batch) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
            </div>
        </div>

        <div class="flex items-center gap-6">
            <label class="inline-flex items-center">
                <input type="checkbox" name="active" value="1" {{ old('active', $replyServer->active) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="delete_after_processing" value="1" {{ old('delete_after_processing', $replyServer->delete_after_processing) ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Delete after processing</span>
            </label>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
            <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">{{ old('notes', $replyServer->notes) }}</textarea>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Also use this server as</div>
            <div class="mt-3 flex items-center gap-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="use_as_delivery_server" value="1" {{ old('use_as_delivery_server') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Use as delivery server</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="use_as_bounce_server" value="1" {{ old('use_as_bounce_server') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Use as bounce server</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <x-button href="{{ route('customer.reply-servers.index') }}" variant="secondary">Cancel</x-button>
            @customercan('servers.permissions.can_edit_reply_servers')
                <x-button type="submit" variant="primary">Save</x-button>
            @endcustomercan
        </div>
        </form>
    </x-card>
</div>
@endsection
