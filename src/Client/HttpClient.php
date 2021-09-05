<?php

declare(strict_types=1);

namespace Sukhoykin\App\Client;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Sukhoykin\App\Util\Profiler;

use Exception;

class HttpClient implements LoggerAwareInterface
{
    const OPTION_AUTH = 'auth';
    const OPTION_HEADERS = 'headers';
    const OPTION_BODY = 'body';
    const OPTION_FILE = 'file';
    const OPTION_PROXY = 'proxy';

    private $profiler;
    private $proxy;
    private $log;

    public $name = 'http';
    public $timeout = 3;

    public $contentType;

    public $insecure = false;
    public $debug = false;

    public function __construct()
    {
        $this->profiler = new Profiler();
    }

    public function setLogger(LoggerInterface $log)
    {
        $this->log = $log;
    }

    public function setProxy(string $proxy)
    {
        $this->proxy = $proxy;
    }

    public function request($method, $url, $options = [])
    {
        $ch = curl_init();

        $method = strtoupper($method);

        $auth = $options[self::OPTION_AUTH] ?? null;
        $headers = $options[self::OPTION_HEADERS] ?? [];
        $body = $options[self::OPTION_BODY] ?? null;
        $file = $options[self::OPTION_FILE] ?? null;
        $proxy = $options[self::OPTION_PROXY] ?? $this->proxy;

        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'HEAD'])) {
            throw new Exception('METHOD_UNSUPPORTED');
        }

        $this->profiler->start('http');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        if ($auth) {
            $headers[] = 'Authorization: ' . $auth;
        }

        if ($this->contentType) {
            $headers[] = 'Content-Type: ' . $this->contentType;
        }

        if (count($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        if ($file) {

            if ($method == 'GET') {

                $fp = fopen($file, "w");

                if (!$fp) {
                    throw new Exception("Could not write file: {$file}");
                }

                curl_setopt($ch, CURLOPT_FILE, $fp);
            }

            if ($method == 'POST' || $method == 'PUT') {

                if (!file_exists($file)) {
                    throw new Exception("File '{$file}' does not exist");
                }

                $fp = fopen($file, "rb");

                if (!$fp) {
                    throw new Exception("Could not read file: {$file}");
                }

                curl_setopt($ch, CURLOPT_UPLOAD, true);
                curl_setopt($ch, CURLOPT_INFILE, $fp);
                curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
            }
        }

        if ($this->debug) {

            $debugHeaders = null;
            $debugData = null;

            if (count($headers)) {
                $debugHeaders = json_encode($headers, JSON_PRETTY_PRINT);
            }

            if ($body) {
                $debugData = is_string($body) ? $body : json_encode($body);
            }

            if ($file) {
                $debugData = 'file://' . $file;
            }

            $this->log->debug(
                sprintf(
                    "[%s] %s %s %s %s%s%s%s",
                    $this->name,
                    $method,
                    $url,
                    $proxy ? 'via (' . $proxy . ')' : '',
                    $debugHeaders ? "\n" : '',
                    $debugHeaders ? $debugHeaders : '',
                    $debugData ? "\n\n" : '',
                    $debugData ? $debugData : ''
                )
            );
        }

        if ($this->insecure) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $response = new \stdClass();
        $response->headers = [];

        curl_setopt(
            $ch,
            CURLOPT_HEADERFUNCTION,
            function ($ch, $header) use ($response) {

                $a = explode(':', $header, 2);

                if (count($a) == 2) {

                    $name = trim($a[0]);
                    $value = trim($a[1]);

                    $response->headers[$name][] = $value;
                }

                return strlen($header);
            }
        );

        $response->content = curl_exec($ch);
        $response->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {

            $response->status = curl_errno($ch);
            $response->error = curl_error($ch);

            $this->log->debug(sprintf("[%s] ERROR: %s", $this->name, $response->error));
        }

        if ($file && isset($fp)) {
            fclose($fp);
        }

        if ($this->debug && $response->content) {
            $this->log->debug(sprintf("[%s] %s", $this->name, $response->content));
        }

        $this->log->info(
            sprintf(
                '[%s] %s %s %d %02.3fs',
                $this->name,
                $method,
                $url,
                $response->status,
                $this->profiler->took('http')
            )
        );

        return $response;
    }
}
