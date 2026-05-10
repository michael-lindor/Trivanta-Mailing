<?php

namespace App\Mail\Transport;

use SparkPost\SparkPost;
use Http\Adapter\Guzzle7\Client;
use Illuminate\Support\Arr;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;

class SparkPostTransport extends AbstractTransport
{
    /**
     * The SparkPost instance.
     *
     * @var \SparkPost\SparkPost
     */
    protected $sparkpost;

    /**
     * The SparkPost transmission options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Create a new SparkPost transport instance.
     *
     * @param  \SparkPost\SparkPost  $sparkpost
     * @param  array  $options
     */
    public function __construct(SparkPost $sparkpost, $options = [])
    {
        $this->sparkpost = $sparkpost;
        $this->options = $options;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $originalMessage = $message->getOriginalMessage();
        
        if (!$originalMessage instanceof Message) {
            throw new TransportException('SparkPostTransport only supports Symfony\Component\Mime\Message instances.');
        }

        $options = $this->options;

        // Extract email details
        $from = $originalMessage->getFrom();
        $toAddresses = [];
        $ccAddresses = [];
        $bccAddresses = [];
        $replyToAddresses = [];

        // Get recipients
        foreach ($originalMessage->getTo() as $address) {
            $toAddresses[] = ['address' => ['email' => $address->getAddress(), 'name' => $address->getName()]];
        }

        foreach ($originalMessage->getCc() as $address) {
            $ccAddresses[] = ['address' => ['email' => $address->getAddress(), 'name' => $address->getName()]];
        }

        foreach ($originalMessage->getBcc() as $address) {
            $bccAddresses[] = ['address' => ['email' => $address->getAddress(), 'name' => $address->getName()]];
        }

        // Get reply-to addresses
        foreach ($originalMessage->getReplyTo() as $address) {
            $replyToAddresses[] = ['address' => ['email' => $address->getAddress(), 'name' => $address->getName()]];
        }

        // Build transmission parameters
        $params = array_merge($options, [
            'content' => [
                'from' => [
                    'email' => $from[0]->getAddress(),
                    'name' => $from[0]->getName() ?? '',
                ],
                'subject' => $originalMessage->getSubject(),
                'text' => $originalMessage->getTextBody() ?? strip_tags($originalMessage->getHtmlBody() ?? ''),
            ],
            'recipients' => array_merge($toAddresses, $ccAddresses, $bccAddresses),
        ]);

        // Add HTML body if present
        if ($originalMessage->getHtmlBody()) {
            $params['content']['html'] = $originalMessage->getHtmlBody();
        }

        // Add ReplyTo if present
        if (!empty($replyToAddresses)) {
            $params['content']['reply_to'] = $replyToAddresses[0]['address']['email'];
        }

        // Add return path if present
        if ($originalMessage instanceof Email) {
            $returnPath = $originalMessage->getReturnPath();
            if ($returnPath) {
                $returnPathAddress = trim((string) $returnPath->getAddress());
                if ($returnPathAddress !== '') {
                    $params['return_path'] = $returnPathAddress;
                }
            }
        }

        try {
            $result = $this->sparkpost->transmissions->post($params);
        } catch (\Exception $e) {
            throw new TransportException(
                sprintf('Request to SparkPost API failed. Reason: %s.', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        $messageId = $result['results']['id'] ?? null;

        if ($messageId) {
            $originalMessage->getHeaders()->addHeader('X-Message-ID', $messageId);
            $originalMessage->getHeaders()->addHeader('X-SparkPost-Message-ID', $messageId);
        }
    }

    /**
     * Get the SparkPost client for the SparkPostTransport instance.
     *
     * @return \SparkPost\SparkPost
     */
    public function sparkpost()
    {
        return $this->sparkpost;
    }

    /**
     * Get the transmission options being used by the transport.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the transmission options being used by the transport.
     *
     * @param  array  $options
     * @return array
     */
    public function setOptions(array $options)
    {
        return $this->options = $options;
    }

    /**
     * Get the string representation of the transport.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'sparkpost';
    }
}
