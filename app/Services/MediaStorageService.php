<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStorageService
{
    public static function config(): array
    {
        return SiteSetting::getValue('file_storage', [
            'default_disk' => 'local',
            's3' => [
                'key' => '',
                'secret' => '',
                'region' => 'us-east-1',
                'bucket' => '',
                'endpoint' => '',
                'path_style' => false,
                'public_url' => '',
            ],
            'backblaze' => [
                'key' => '',
                'secret' => '',
                'region' => 'us-west-004',
                'bucket' => '',
                'endpoint' => 'https://s3.us-west-004.backblazeb2.com',
                'path_style' => true,
                'public_url' => '',
            ],
            'idrive' => [
                'key' => '',
                'secret' => '',
                'region' => 'us-east-1',
                'bucket' => '',
                'endpoint' => '',
                'path_style' => true,
                'public_url' => '',
            ],
        ]);
    }

    public static function disks(): array
    {
        return [
            'local' => 'Local server',
            's3' => 'Amazon S3',
            'backblaze' => 'Backblaze B2',
            'idrive' => 'iDrive e2',
            'drive' => 'Google Drive (URL)',
            'external' => 'External URL',
        ];
    }

    /** Normalize Google Drive share links when possible. */
    public static function normalizeDriveUrl(string $url): string
    {
        $url = trim($url);
        if (preg_match('~drive\.google\.com/file/d/([a-zA-Z0-9_-]+)~', $url, $m)) {
            return 'https://drive.google.com/uc?export=download&id='.$m[1];
        }
        if (preg_match('~drive\.google\.com/open\?id=([a-zA-Z0-9_-]+)~', $url, $m)) {
            return 'https://drive.google.com/uc?export=download&id='.$m[1];
        }

        return $url;
    }

    public function storeUpload(UploadedFile $file, string $disk, ?int $userId = null, ?string $alt = null): MediaAsset
    {
        $disk = $disk ?: (self::config()['default_disk'] ?? 'local');
        if (in_array($disk, ['external', 'drive'], true)) {
            throw new \InvalidArgumentException('Use URL registration for Drive / external.');
        }

        $dir = 'media/'.date('Y/m');
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $name = ($name ?: 'file').'-'.Str::random(6).'.'.$file->getClientOriginalExtension();
        $objectKey = $dir.'/'.$name;

        if ($disk === 'local') {
            $publicRoot = storage_path('app/public');
            if (! is_dir($publicRoot.'/media')) {
                @mkdir($publicRoot.'/media', 0755, true);
            }
            $path = $file->storeAs($dir, $name, 'public');
            $url = url('/media/file/'.$path);

            return MediaAsset::create([
                'user_id' => $userId,
                'url' => $url,
                'filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'alt' => $alt,
                'disk' => 'public',
                'path' => $path,
            ]);
        }

        // S3-compatible cloud disks
        $cfg = self::config();
        $providerCfg = $cfg[$disk] ?? null;
        if (! is_array($providerCfg) || empty($providerCfg['key']) || empty($providerCfg['secret']) || empty($providerCfg['bucket'])) {
            throw new \RuntimeException(ucfirst($disk).' is not configured. Go to Settings → File storage.');
        }

        $uploader = S3CompatibleUploader::fromConfig($providerCfg);
        $tmp = $file->getRealPath();
        $url = $uploader->uploadFile($objectKey, $tmp, $file->getMimeType() ?: 'application/octet-stream');

        return MediaAsset::create([
            'user_id' => $userId,
            'url' => $url,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt' => $alt,
            'disk' => $disk,
            'path' => $objectKey,
        ]);
    }

    public function storeExternalUrl(string $url, string $disk = 'external', ?int $userId = null, ?string $alt = null): MediaAsset
    {
        if ($disk === 'drive') {
            $url = self::normalizeDriveUrl($url);
        }

        return MediaAsset::create([
            'user_id' => $userId,
            'url' => $url,
            'filename' => basename(parse_url($url, PHP_URL_PATH) ?: 'external'),
            'mime_type' => null,
            'size' => null,
            'alt' => $alt,
            'disk' => $disk,
            'path' => null,
        ]);
    }
}
