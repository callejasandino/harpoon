<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'verify' => true,
            'allow_redirects' => ['strict' => true],
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]);
    }

    public function index()
    {
        return view('home');
    }

    public function checkUrl(Request $request)
    {
        $url = $request->input('url');

        $improvements = [
            'improvements' => [
                'url' => $url,
                'https' => $this->checkHTTPS($url),
                'hsts' => $this->checkHSTS($url),
                'csrf' => $this->checkCSRF($url),
                'cors' => $this->checkCORS($url),
                'form_validation' => $this->checkFormValidation($url),
                'security_headers' => $this->checkSecurityHeaders($url),
                'xss' => $this->checkXSSProtection($url),
                'ssl' => $this->checkSSL($url)
            ]
        ];

        return view('result', compact('improvements'));
    }

    private function checkHTTPS(string $url): array
    {
        $parsedUrl = parse_url($url);

        if (isset($parsedUrl['scheme']) && $parsedUrl['scheme'] === 'https') {
            return [
                'description' => "HTTPS is enabled.",
                'improvement' => "None."
            ];
        }

        $httpsUrl = 'https://' . $parsedUrl['host'] . ($parsedUrl['path'] ?? '');

        try {
            $this->client->get($httpsUrl);
            return [
                'description' => "HTTPS is available, but not used by default.",
                'improvement' => "Redirect HTTP traffic to HTTPS by setting up a 301 redirect in your server configuration."
            ];
        } catch (\Exception $e) {
            Log::error("HTTPS check failed: " . $e->getMessage());
            return [
                'description' => "HTTPS is not available.",
                'improvement' => "Install an SSL certificate to enable HTTPS."
            ];
        }
    }

    private function checkHSTS(string $url): array
    {
        return $this->fetchHeader($url, 'Strict-Transport-Security', "HSTS", "Enable HSTS by adding the `Strict-Transport-Security` header.");
    }

    private function checkSSL(string $url): array
    {
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'];
        $port = $parsedUrl['port'] ?? 443;

        $context = stream_context_create([
            "ssl" => [
                "capture_peer_cert" => true,
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ]);

        try {
            $result = stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

            if ($result === false) {
                return ["description" => "Connection failed: $errstr ($errno)", 'improvement' => 'None'];
            }

            $params = stream_context_get_params($result);
            $cert = openssl_x509_parse($params["options"]["ssl"]["peer_certificate"]);

            $validFrom = new \DateTime('@' . $cert['validFrom_time_t']);
            $validTo = new \DateTime('@' . $cert['validTo_time_t']);

            fclose($result);

            return [
                "ssl_tls" => "Enabled",
                "description" => sprintf(
                    "Issued to: %s, Valid from: %s, Valid until: %s",
                    $cert['subject']['CN'] ?? 'N/A',
                    $validFrom->format('Y-m-d'),
                    $validTo->format('Y-m-d')
                ),
                'improvement' => "Ensure SSL certificates are renewed before expiry."
            ];
        } catch (\Exception $e) {
            Log::error("SSL check failed: " . $e->getMessage());
            return ["description" => "Check failed: " . $e->getMessage(), 'improvement' => 'None'];
        }
    }

    private function checkCSRF(string $url): array
    {
        try {
            $response = $this->client->get($url);
            $crawler = new Crawler($response->getBody()->getContents());

            if ($crawler->filter('input[name^=csrf], meta[name^=csrf]')->count() > 0) {
                return ['description' => "CSRF protection is present.", 'improvement' => 'None'];
            }

            if (preg_grep('/(csrf|xsrf|token)/i', $response->getHeaders()['Set-Cookie'] ?? [])) {
                return ['description' => "CSRF protection found in cookies.", 'improvement' => 'None'];
            }

            return [
                'description' => "No CSRF protection found.",
                'improvement' => "Consider adding CSRF tokens to forms."
            ];
        } catch (\Exception $e) {
            Log::error("CSRF check failed: " . $e->getMessage());
            return ['description' => "Error checking CSRF: " . $e->getMessage(), 'improvement' => 'None'];
        }
    }

    private function checkCORS(string $url): array
    {
        return $this->fetchHeader($url, 'Access-Control-Allow-Origin', "CORS", "Add CORS headers to control cross-origin requests.");
    }

    private function checkFormValidation(string $url): array
    {
        try {
            $response = $this->client->get($url);
            $crawler = new Crawler($response->getBody()->getContents());
            $validations = [];

            $crawler->filter('form input, form select, form textarea')->each(function (Crawler $node) use (&$validations) {
                $name = $node->attr('name') ?? 'unnamed';
                if ($node->attr('required')) {
                    $validations[] = "Field '$name' is required.";
                }
                if ($node->attr('pattern')) {
                    $validations[] = "Field '$name' has a pattern.";
                }
                if ($node->attr('minlength')) {
                    $validations[] = "Field '$name' has a minlength of " . $node->attr('minlength');
                }
                if ($node->attr('maxlength')) {
                    $validations[] = "Field '$name' has a maxlength of " . $node->attr('maxlength');
                }
                if ($node->attr('type') === 'email') {
                    $validations[] = "Field '$name' requires an email format.";
                }
            });

            return $validations ? ['description' => implode(' ', $validations), 'improvement' => 'None'] : [
                'description' => "No client-side validations found.",
                'improvement' => "Add validation attributes like `required`, `pattern`, `minlength`, and `maxlength` for better form validation."
            ];
        } catch (\Exception $e) {
            Log::error("Form validation check failed: " . $e->getMessage());
            return [
                'description' => "Error checking form validation: " . $e->getMessage(),
                'improvement' => 'None'
            ];
        }
    }

    private function checkSecurityHeaders(string $url): array
    {
        return $this->fetchMultipleHeaders($url, [
            'Content-Security-Policy',
            'X-Frame-Options',
            'X-Content-Type-Options',
            'Strict-Transport-Security',
            'Referrer-Policy',
            'Permissions-Policy',
            'X-XSS-Protection',
        ], "Set the following headers to improve security.");
    }

    private function checkXSSProtection(string $url): array
    {
        return $this->fetchMultipleHeaders($url, ['X-XSS-Protection', 'Content-Security-Policy'], "Enable XSS protection and set up a strong Content-Security-Policy.");
    }

    private function fetchHeader(string $url, string $header, string $feature, string $improvement): array
    {
        try {
            $response = $this->client->get($url);
            $headers = $response->getHeaders();

            if (isset($headers[$header])) {
                return [
                    'description' => "$feature is enabled: " . implode(', ', $headers[$header]),
                    'improvement' => 'None.'
                ];
            }

            return [
                'description' => "$feature is not enabled.",
                'improvement' => $improvement
            ];
        } catch (\Exception $e) {
            Log::error("Header fetch failed for $feature: " . $e->getMessage());
            return [
                'description' => "Error checking $feature: " . $e->getMessage(),
                'improvement' => 'None'
            ];
        }
    }

    private function fetchMultipleHeaders(string $url, array $headers, string $improvement): array
    {
        try {
            $response = $this->client->get($url);
            $results = [];
            foreach ($headers as $header) {
                $results[$header] = $response->getHeader($header)[0] ?? 'Not Set';
            }
            $results['Improvement'] = $improvement;
            return $results;
        } catch (\Exception $e) {
            Log::error("Multiple headers fetch failed: " . $e->getMessage());
            return [
                'description' => "Error checking headers: " . $e->getMessage(),
                'improvement' => 'None'
            ];
        }
    }
}
