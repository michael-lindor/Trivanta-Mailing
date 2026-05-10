<?php

namespace App\Mail;

use App\Mail\Transport\SendGridTransport;
use App\Mail\Transport\SesReplyToTransport;
use App\Mail\Transport\SparkPostTransport;
use Aws\Ses\SesClient;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle7\Client;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Arr;
use SparkPost\SparkPost;
use Symfony\Component\Mailer\Bridge\Mailgun\Transport\MailgunTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class CustomMailManager extends MailManager
{
    /**
     * Create an instance of the Symfony Amazon SES ReplyTo Transport driver.
     *
     * @param  array  $config
     * @return \App\Mail\Transport\SesReplyToTransport
     */
    protected function createSesReplyToTransport(array $config)
    {
        $config = array_merge(
            $this->app['config']->get('services.ses', []),
            $config
        );

        $config = Arr::except($config, ['transport']);

        return new SesReplyToTransport(
            new SesClient($this->addSesCredentials($config)),
            $config['options'] ?? []
        );
    }

    /**
     * Add AWS SES credentials to the configuration array.
     *
     * @param  array  $config
     * @return array
     */
    protected function addSesCredentials(array $config): array
    {
        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        unset($config['key'], $config['secret'], $config['token']);

        return $config;
    }

    /**
     * Create an instance of the Symfony SparkPost Transport driver.
     *
     * @param  array  $config
     * @return \App\Mail\Transport\SparkPostTransport
     */
    protected function createSparkpostTransport(array $config)
    {
        $config = array_merge(
            $this->app['config']->get('services.sparkpost', []),
            $config
        );

        $config = Arr::except($config, ['transport']);

        if (empty($config['secret'])) {
            throw new \InvalidArgumentException('SparkPost API key is required.');
        }

        $httpClient = new Client();
        $sparkpost = new SparkPost($httpClient, [
            'key' => $config['secret'],
            'endpoint' => $config['endpoint'] ?? 'https://api.sparkpost.com',
        ]);

        return new SparkPostTransport(
            $sparkpost,
            $config['options'] ?? []
        );
    }

    /**
     * Create an instance of the SendGrid Transport driver.
     *
     * @param  array  $config
     * @return \App\Mail\Transport\SendGridTransport
     */
    protected function createSendgridTransport(array $config)
    {
        $config = array_merge(
            $this->app['config']->get('services.sendgrid', []),
            $config
        );

        $config = Arr::except($config, ['transport']);

        if (empty($config['api_key'])) {
            throw new \InvalidArgumentException('SendGrid API key is required.');
        }

        return new SendGridTransport($config['api_key']);
    }

    /**
     * Create an instance of the Symfony Mailgun Transport driver.
     *
     * @param  array  $config
     * @return \Symfony\Component\Mailer\Transport\TransportInterface
     */
    protected function createMailgunTransport(array $config)
    {
        $factory = new MailgunTransportFactory(null, $this->getHttpClient($config));

        if (! isset($config['secret'])) {
            $config = $this->app['config']->get('services.mailgun', []);
        }

        // Use the correct endpoint format for Mailgun API
        $endpoint = $config['endpoint'] ?? 'api.mailgun.net';
        $scheme = $config['scheme'] ?? 'https';
        
        return $factory->create(new Dsn(
            'mailgun+' . $scheme,
            $endpoint,
            $config['secret'],
            $config['domain']
        ));
    }
}
