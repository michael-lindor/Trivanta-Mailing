<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryServerLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_server_id',
        'event',
        'to_email',
        'status',
        'error_code',
        'error_message',
        'diagnostic',
        'error_category',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function deliveryServer(): BelongsTo
    {
        return $this->belongsTo(DeliveryServer::class);
    }

    /**
     * Categorize an error message and return a structured error_category.
     */
    public static function categorizeError(string $errorMessage): string
    {
        $msg = strtolower($errorMessage);

        if (str_contains($msg, 'spf') && (str_contains($msg, 'fail') || str_contains($msg, 'did not pass'))) {
            return 'spf_fail';
        }

        if (str_contains($msg, 'dkim') && (str_contains($msg, 'fail') || str_contains($msg, 'did not pass'))) {
            return 'dkim_fail';
        }

        if (str_contains($msg, 'dmarc')) {
            return 'dmarc_fail';
        }

        if (str_contains($msg, 'unauthenticated') || str_contains($msg, '5.7.26') || str_contains($msg, '5.7.1')) {
            return 'auth_fail';
        }

        if (str_contains($msg, 'certificate verify') || str_contains($msg, 'ssl operation')) {
            return 'tls_error';
        }

        if (str_contains($msg, 'connection') || str_contains($msg, 'connect failed') || str_contains($msg, 'timed out')) {
            return 'connection';
        }

        if (str_contains($msg, 'quota') || str_contains($msg, 'rate limit') || str_contains($msg, 'too many')) {
            return 'quota';
        }

        if (str_contains($msg, 'blacklist') || str_contains($msg, 'blocklist') || str_contains($msg, 'reputation')) {
            return 'reputation';
        }

        if (str_contains($msg, 'spam') || str_contains($msg, 'content')) {
            return 'content';
        }

        return 'unknown';
    }

    /**
     * Get human-readable fix suggestion based on error category.
     */
    public static function getFixSuggestion(string $category, ?string $sendingDomain = null, ?string $serverHostname = null): string
    {
        return match ($category) {
            'spf_fail' => 'Your SPF record does not authorize the sending server\'s IP. Add an SPF TXT record to your DNS for ' . ($sendingDomain ?? 'your domain') . ': v=spf1 ' . ($serverHostname ? 'include:' . $serverHostname . ' ' : '') . 'a:' . ($sendingDomain ?? 'yourdomain.com') . ' ~all',
            'dkim_fail' => 'DKIM signing is not configured or the DNS public key doesn\'t match. Contact your SMTP provider' . ($serverHostname ? ' (' . $serverHostname . ')' : '') . ' for the correct DKIM TXT record to add to ' . ($sendingDomain ?? 'your domain') . '\'s DNS.',
            'dmarc_fail' => 'Add a DMARC TXT record: _dmarc.' . ($sendingDomain ?? 'yourdomain.com') . ' → v=DMARC1; p=none; rua=mailto:dmarc-reports@' . ($sendingDomain ?? 'yourdomain.com'),
            'auth_fail' => 'The receiving server rejected your email because the sender is unauthenticated. You need to set up both SPF and DKIM records for ' . ($sendingDomain ?? 'your domain') . ' in your DNS settings.',
            'tls_error' => 'TLS/SSL certificate verification failed. If using a self-signed certificate, set MAIL_VERIFY_PEER=false in your .env file. For production, install a valid SSL certificate on your SMTP server.',
            'connection' => 'Could not connect to the SMTP server. Check that the hostname, port, and encryption settings are correct. Verify the server is reachable and not blocked by a firewall.',
            'quota' => 'Sending quota or rate limit exceeded. Wait and try again later, or increase your sending limits with your SMTP provider.',
            'reputation' => 'The sending IP or domain has a poor reputation. Use the Email Warmup feature to gradually build sender reputation, or contact your SMTP provider.',
            'content' => 'The email content triggered spam filters. Review your email content, avoid spam trigger words, and ensure proper HTML formatting.',
            default => 'An unknown delivery error occurred. Check the diagnostic details below and contact your SMTP provider if the issue persists.',
        };
    }
}
