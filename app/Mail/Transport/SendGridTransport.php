<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;

class SendGridTransport extends AbstractTransport
{
    /**
     * The SendGrid API key.
     *
     * @var string
     */
    protected string $apiKey;

    /**
     * The SendGrid API endpoint.
     *
     * @var string
     */
    protected string $endpoint = 'https://api.sendgrid.com/v3/mail/send';

    /**
     * Create a new SendGrid transport instance.
     *
     * @param  string  $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $originalMessage = $message->getOriginalMessage();

        if (!$originalMessage instanceof Email) {
            throw new TransportException('SendGridTransport only supports Symfony\Component\Mime\Email instances.');
        }

        $payload = $this->buildPayload($originalMessage);

        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new TransportException(
                sprintf('Request to SendGrid API failed: %s', $error)
            );
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $body = json_decode($response, true);
            $errors = '';
            if (isset($body['errors']) && is_array($body['errors'])) {
                $errors = implode('; ', array_map(fn($e) => $e['message'] ?? '', $body['errors']));
            }
            throw new TransportException(
                sprintf('SendGrid API returned HTTP %d: %s', $statusCode, $errors ?: $response)
            );
        }

        // Extract message ID from response headers if available
        $messageId = null;
        if ($response) {
            $body = json_decode($response, true);
            if (isset($body['x-message-id'])) {
                $messageId = $body['x-message-id'];
            }
        }

        if ($messageId) {
            $originalMessage->getHeaders()->addHeader('X-Message-ID', $messageId);
        }
    }

    /**
     * Build the SendGrid v3 API payload from a Symfony Email message.
     *
     * @param  \Symfony\Component\Mime\Email  $email
     * @return array
     */
    protected function buildPayload(Email $email): array
    {
        $from = $email->getFrom()[0] ?? null;
        if (!$from) {
            throw new TransportException('No "From" address set.');
        }

        $personalizations = [];
        $to = array_map(fn(Address $a) => array_filter([
            'email' => $a->getAddress(),
            'name' => $a->getName() ?: null,
        ]), $email->getTo());

        $cc = array_map(fn(Address $a) => array_filter([
            'email' => $a->getAddress(),
            'name' => $a->getName() ?: null,
        ]), $email->getCc());

        $bcc = array_map(fn(Address $a) => array_filter([
            'email' => $a->getAddress(),
            'name' => $a->getName() ?: null,
        ]), $email->getBcc());

        $personalization = [];
        if (!empty($to)) {
            $personalization['to'] = array_values($to);
        }
        if (!empty($cc)) {
            $personalization['cc'] = array_values($cc);
        }
        if (!empty($bcc)) {
            $personalization['bcc'] = array_values($bcc);
        }

        if (!empty($personalization)) {
            $personalizations[] = $personalization;
        }

        $payload = [
            'personalizations' => $personalizations,
            'from' => array_filter([
                'email' => $from->getAddress(),
                'name' => $from->getName() ?: null,
            ]),
            'subject' => $email->getSubject() ?? '(no subject)',
        ];

        // Content
        $content = [];
        if ($email->getTextBody()) {
            $content[] = ['type' => 'text/plain', 'value' => $email->getTextBody()];
        }
        if ($email->getHtmlBody()) {
            $content[] = ['type' => 'text/html', 'value' => $email->getHtmlBody()];
        }
        if (empty($content)) {
            $content[] = ['type' => 'text/plain', 'value' => ' '];
        }
        $payload['content'] = $content;

        // Reply-To
        $replyTo = $email->getReplyTo();
        if (!empty($replyTo)) {
            $payload['reply_to'] = array_filter([
                'email' => $replyTo[0]->getAddress(),
                'name' => $replyTo[0]->getName() ?: null,
            ]);
        }

        return $payload;
    }

    /**
     * Get the string representation of the transport.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'sendgrid';
    }
}
