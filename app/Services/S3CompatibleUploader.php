<?php

namespace App\Services;

/**
 * Minimal S3-compatible PUT uploader (AWS S3, Backblaze B2, iDrive e2, etc.).
 * Uses Signature Version 4 — no AWS SDK required.
 */
class S3CompatibleUploader
{
    public function __construct(
        protected string $key,
        protected string $secret,
        protected string $region,
        protected string $bucket,
        protected string $endpoint = '',
        protected bool $pathStyle = true,
        protected string $publicBaseUrl = '',
    ) {}

    public static function fromConfig(array $c): self
    {
        return new self(
            key: (string) ($c['key'] ?? ''),
            secret: (string) ($c['secret'] ?? ''),
            region: (string) ($c['region'] ?? 'us-east-1'),
            bucket: (string) ($c['bucket'] ?? ''),
            endpoint: rtrim((string) ($c['endpoint'] ?? ''), '/'),
            pathStyle: (bool) ($c['path_style'] ?? true),
            publicBaseUrl: rtrim((string) ($c['public_url'] ?? ''), '/'),
        );
    }

    public function upload(string $objectKey, string $body, string $contentType): string
    {
        $objectKey = ltrim($objectKey, '/');
        $host = $this->requestHost();
        $url = $this->requestUrl($objectKey);

        $amzDate = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');
        $payloadHash = hash('sha256', $body);

        $canonicalHeaders = "host:{$host}\n"
            ."x-amz-content-sha256:{$payloadHash}\n"
            ."x-amz-date:{$amzDate}\n";
        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';

        $canonicalRequest = "PUT\n/{$this->canonicalUri($objectKey)}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";

        $algorithm = 'AWS4-HMAC-SHA256';
        $credentialScope = "{$dateStamp}/{$this->region}/s3/aws4_request";
        $stringToSign = "{$algorithm}\n{$amzDate}\n{$credentialScope}\n".hash('sha256', $canonicalRequest);

        $signingKey = $this->signingKey($dateStamp);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authorization = "{$algorithm} Credential={$this->key}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $headers = [
            "Host: {$host}",
            "x-amz-content-sha256: {$payloadHash}",
            "x-amz-date: {$amzDate}",
            "Authorization: {$authorization}",
            "Content-Type: {$contentType}",
            'Content-Length: '.strlen($body),
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 120,
        ]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($response === false || $status < 200 || $status >= 300) {
            throw new \RuntimeException('S3 upload failed (HTTP '.$status.'): '.($err ?: substr((string) $response, 0, 300)));
        }

        return $this->publicUrl($objectKey);
    }

    public function uploadFile(string $objectKey, string $localPath, ?string $contentType = null): string
    {
        $body = file_get_contents($localPath);
        if ($body === false) {
            throw new \RuntimeException('Cannot read upload file.');
        }
        $contentType = $contentType ?: (mime_content_type($localPath) ?: 'application/octet-stream');

        return $this->upload($objectKey, $body, $contentType);
    }

    protected function signingKey(string $dateStamp): string
    {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4'.$this->secret, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);

        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }

    protected function requestHost(): string
    {
        if ($this->endpoint !== '') {
            return parse_url($this->endpoint, PHP_URL_HOST) ?: $this->endpoint;
        }

        return "{$this->bucket}.s3.{$this->region}.amazonaws.com";
    }

    protected function requestUrl(string $objectKey): string
    {
        $key = str_replace('%2F', '/', rawurlencode($objectKey));
        if ($this->endpoint !== '') {
            if ($this->pathStyle) {
                return "{$this->endpoint}/{$this->bucket}/{$key}";
            }

            $host = parse_url($this->endpoint, PHP_URL_HOST);
            $scheme = parse_url($this->endpoint, PHP_URL_SCHEME) ?: 'https';

            return "{$scheme}://{$this->bucket}.{$host}/{$key}";
        }

        return "https://{$this->bucket}.s3.{$this->region}.amazonaws.com/{$key}";
    }

    protected function canonicalUri(string $objectKey): string
    {
        $encoded = str_replace('%2F', '/', rawurlencode($objectKey));
        if ($this->endpoint !== '' && $this->pathStyle) {
            return $this->bucket.'/'.$encoded;
        }

        return $encoded;
    }

    protected function publicUrl(string $objectKey): string
    {
        $key = str_replace('%2F', '/', rawurlencode($objectKey));
        if ($this->publicBaseUrl !== '') {
            return $this->publicBaseUrl.'/'.$key;
        }
        if ($this->endpoint !== '') {
            if ($this->pathStyle) {
                return "{$this->endpoint}/{$this->bucket}/{$key}";
            }
            $host = parse_url($this->endpoint, PHP_URL_HOST);
            $scheme = parse_url($this->endpoint, PHP_URL_SCHEME) ?: 'https';

            return "{$scheme}://{$this->bucket}.{$host}/{$key}";
        }

        return "https://{$this->bucket}.s3.{$this->region}.amazonaws.com/{$key}";
    }
}
