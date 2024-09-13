<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class CheckWebsiteSecurity extends Command
{
    protected $signature = 'security:scan {url : The URL to scan}';
    protected $description = 'Perform a security scan on the given URL';

    private $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client([
            'verify' => true,
            'allow_redirects' => ['strict' => true],
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]);
    }

    public function handle()
    {
        $url = $this->argument('url');
        $this->info("Starting security scan for: $url");

        $results = [
            'HTTPS' => $this->checkHTTPS($url),
            'HSTS' => $this->checkHSTS($url),
            'CSRF' => $this->checkCSRF($url),
            'CORS' => $this->checkCORS($url),
            'Form Validation' => $this->checkFormValidation($url),
            'Security Headers' => $this->checkSecurityHeaders($url),
            'XSS Protection' => $this->checkXSSProtection($url),
            'SSL' => $this->checkSSL($url)
        ];

        $this->outputResults($results);
    }

    private function checkHTTPS($url)
    {
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['scheme']) && $parsedUrl['scheme'] === 'https') {
            return "HTTPS is enabled.";
        } else {
            $httpsUrl = 'https://' . $parsedUrl['host'] . (isset($parsedUrl['path']) ? $parsedUrl['path'] : '');
            try {
                $response = $this->client->get($httpsUrl);
                return [
                    "HTTPS is available, but not used by default.",
                    "Improvement: Redirect HTTP traffic to HTTPS by setting up a 301 redirect in your server configuration."
                ];
            } catch (\Exception $e) {
                return [
                    "HTTPS is not available.",
                    "Improvement: Purchase and install an SSL certificate for your website to enable HTTPS."
                ];
            }
        }
    }

    private function checkHSTS($url)
    {
        try {
            $response = $this->client->get($url);
            $headers = $response->getHeaders();

            if (isset($headers['Strict-Transport-Security'])) {
                $hsts = $headers['Strict-Transport-Security'][0];
                return "HSTS is enabled: $hsts";
            } else {
                return [
                    "HSTS is not enabled.",
                    "Improvement: Enable HSTS by adding the `Strict-Transport-Security` header in your server configuration."
                ];
            }
        } catch (\Exception $e) {
            return "Error checking HSTS: " . $e->getMessage();
        }
    }

    private function checkSSL($url)
    {
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'];
        $port = isset($parsedUrl['port']) ? $parsedUrl['port'] : 443;

        $context = stream_context_create([
            "ssl" => [
                "capture_peer_cert" => true,
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ]);

        try {
            $result = stream_socket_client(
                "ssl://{$host}:{$port}",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if ($result === false) {
                return ["SSL" => "Connection failed: $errstr ($errno)"];
            }

            $params = stream_context_get_params($result);
            $cert = openssl_x509_parse($params["options"]["ssl"]["peer_certificate"]);

            $validFrom = new \DateTime('@' . $cert['validFrom_time_t']);
            $validTo = new \DateTime('@' . $cert['validTo_time_t']);

            $issuedTo = $cert['subject']['CN'] ?? 'N/A';
            $issuedBy = [
                'countryName' => $cert['issuer']['C'] ?? 'N/A',
                'organizationName' => $cert['issuer']['O'] ?? 'N/A',
                'commonName' => $cert['issuer']['CN'] ?? 'N/A',
            ];

            $cryptoInfo = stream_get_meta_data($result)['crypto'] ?? [];
            $cipherSuite = $cryptoInfo['cipher_name'] ?? 'N/A';

            fclose($result);

            return [
                "SSL/TLS" => "Enabled",
                "Details" => sprintf(
                    "Issued to: %s, Issued by: %s, Valid from: %s, Valid until: %s. Cipher: %s",
                    $issuedTo,
                    json_encode($issuedBy),
                    $validFrom->format('Y-m-d H:i:s T'),
                    $validTo->format('Y-m-d H:i:s T'),
                    $cipherSuite
                ),
                "Improvement: Renew SSL certificates before expiry, and ensure the server supports modern, secure cipher suites."
            ];
        } catch (\Exception $e) {
            return ["SSL" => "Check failed: " . $e->getMessage()];
        }
    }

    private function checkCSRF($url)
    {
        try {
            $response = $this->client->get($url);
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);

            $csrfFields = $crawler->filter('input[name^=csrf], input[name$=token], input[name$=Token], meta[name^=csrf]');

            if ($csrfFields->count() > 0) {
                return "CSRF protection found: " . $csrfFields->attr('name');
            }

            $cookies = $response->getHeaders()['Set-Cookie'] ?? [];
            foreach ($cookies as $cookie) {
                if (preg_match('/(csrf|xsrf|token)/i', $cookie)) {
                    return "CSRF protection found in cookies.";
                }
            }

            return [
                "No visible CSRF protection found.",
                "Improvement: Consider adding CSRF tokens to all forms. Use `@csrf` in Laravel Blade forms or include `<input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token() }}\">` manually."
            ];
        } catch (\Exception $e) {
            return "Error checking CSRF: " . $e->getMessage();
        }
    }

    private function checkCORS($url)
    {
        try {
            $response = $this->client->options($url, [
                'headers' => [
                    'Origin' => 'https://example.com',
                    'Access-Control-Request-Method' => 'GET'
                ]
            ]);
            $headers = $response->getHeaders();

            if (isset($headers['Access-Control-Allow-Origin'])) {
                return "CORS is enabled: " . implode(', ', $headers['Access-Control-Allow-Origin']);
            }
            return [
                "No CORS headers found in OPTIONS response.",
                "Improvement: Add CORS headers (`Access-Control-Allow-Origin`, `Access-Control-Allow-Methods`, `Access-Control-Allow-Headers`) in your server configuration to control cross-origin requests."
            ];
        } catch (\Exception $e) {
            return "Error checking CORS: " . $e->getMessage();
        }
    }

    private function checkFormValidation($url)
    {
        try {
            $response = $this->client->get($url);
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);
            $fields = $crawler->filter('form input, form select, form textarea');
            $validations = [];
            $fields->each(function (Crawler $node) use (&$validations) {
                $name = $node->attr('name') ?? 'unnamed';
                if ($node->attr('required') !== null) {
                    $validations[] = "Field '$name' is required";
                }
                if ($node->attr('pattern') !== null) {
                    $validations[] = "Field '$name' has a pattern";
                }
                if ($node->attr('minlength') !== null) {
                    $validations[] = "Field '$name' has a minlength of " . $node->attr('minlength');
                }
                if ($node->attr('maxlength') !== null) {
                    $validations[] = "Field '$name' has a maxlength of " . $node->attr('maxlength');
                }
                if ($node->attr('type') === 'email') {
                    $validations[] = "Field '$name' requires email format";
                }
            });

            return $validations ?: [
                "No client-side validations found.",
                "Improvement: Add proper validation attributes like `required`, `pattern`, `minlength`, and `maxlength` for better form validation."
            ];
        } catch (\Exception $e) {
            return ["Error checking form validation: " . $e->getMessage()];
        }
    }

    private function checkSecurityHeaders($url)
    {
        try {
            $response = $this->client->get($url);
            $headers = $response->getHeaders();

            $securityHeaders = [
                'Content-Security-Policy',
                'X-Frame-Options',
                'X-Content-Type-Options',
                'Strict-Transport-Security',
                'Referrer-Policy',
                'Permissions-Policy',
                'X-XSS-Protection'
            ];

            $results = [];
            foreach ($securityHeaders as $header) {
                if (isset($headers[$header])) {
                    $results[$header] = implode(', ', $headers[$header]);
                } else {
                    $results[$header] = "Not Set";
                }
            }

            $results['Improvement'] = "Set the following headers to improve security: Content-Security-Policy, X-Frame-Options, X-Content-Type-Options, Strict-Transport-Security, Referrer-Policy, Permissions-Policy, and X-XSS-Protection.";

            return $results;
        } catch (\Exception $e) {
            return ["Error checking security headers: " . $e->getMessage()];
        }
    }

    private function checkXSSProtection($url)
    {
        try {
            $response = $this->client->get($url);
            $headers = $response->getHeaders();

            $xssProtection = isset($headers['X-XSS-Protection']) ? implode(', ', $headers['X-XSS-Protection']) : 'Not Set';
            $contentSecurityPolicy = isset($headers['Content-Security-Policy']) ? implode(', ', $headers['Content-Security-Policy']) : 'Not Set';

            return [
                'X-XSS-Protection' => $xssProtection,
                'Content-Security-Policy' => $contentSecurityPolicy,
                'Improvement' => "Enable XSS protection headers (`X-XSS-Protection`) and set up a strong Content-Security-Policy to prevent XSS attacks."
            ];
        } catch (\Exception $e) {
            return ["Error checking XSS protection: " . $e->getMessage()];
        }
    }

    private function outputResults($results)
    {
        $this->output->newLine();
        foreach ($results as $key => $value) {
            $this->output->writeln("<comment>$key:</comment>");
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $this->output->writeln("  <info>$subKey:</info> $subValue");
                }
            } else {
                $this->output->writeln("  <info>$value</info>");
            }
            $this->output->newLine();
        }
        $this->info('Security scan completed.');
    }
}
