<?php

namespace App\Services;

use GuzzleHttp\Client;

class GraphSharePointService
{
    private Client $http;
    private string $token;

    public function __construct()
    {
        $this->http = new Client(['base_uri' => 'https://graph.microsoft.com/v1.0/']);
        $this->token = $this->getAppToken();
    }

    private function headers(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    private function getAppToken(): string
    {
        $resp = (new Client())->post(
            'https://login.microsoftonline.com/'.env('GRAPH_TENANT_ID').'/oauth2/v2.0/token',
            ['form_params' => [
                'client_id' => env('GRAPH_CLIENT_ID'),
                'client_secret' => env('GRAPH_CLIENT_SECRET'),
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ]]
        );
        return json_decode($resp->getBody(), true)['access_token'];
    }

    public function createFolder(string $driveId, string $parentPath, string $folderName): array
    {
        $url = "drives/{$driveId}/root:/".trim($parentPath,'/').":/children";
        $resp = $this->http->post($url, [
            'headers' => $this->headers(),
            'json' => [
                'name' => $this->sanitize($folderName),
                'folder' => new \stdClass(),
                '@microsoft.graph.conflictBehavior' => 'rename',
            ],
        ]);
        return json_decode($resp->getBody(), true);
    }

    public function createFolderLink(string $driveId, string $itemId, string $scope = 'organization'): ?string
    {
        $resp = $this->http->post("drives/{$driveId}/items/{$itemId}/createLink", [
            'headers' => $this->headers(),
            'json' => ['type' => 'view', 'scope' => $scope],
        ]);
        return json_decode($resp->getBody(), true)['link']['webUrl'] ?? null;
    }

    public function upload(string $driveId, string $folderPath, string $filename, string $tmpPath): array
    {
        $size = filesize($tmpPath);
        $filename = $this->sanitize($filename);

        if ($size <= 4 * 1024 * 1024) {
            $url = "drives/{$driveId}/root:/".trim($folderPath,'/')."/{$filename}:/content";
            $resp = $this->http->put($url, [
                'headers' => $this->headers(),
                'body' => fopen($tmpPath, 'r'),
            ]);
            return json_decode($resp->getBody(), true);
        }

        // Upload session (>4MB)
        $create = $this->http->post(
            "drives/{$driveId}/root:/".trim($folderPath,'/')."/{$filename}:/createUploadSession",
            ['headers' => $this->headers()]
        );
        $uploadUrl = json_decode($create->getBody(), true)['uploadUrl'];

        $fh = fopen($tmpPath, 'rb'); $chunk = 3276800; $start = 0;
        while (!feof($fh)) {
            $data = fread($fh, $chunk);
            $end = $start + strlen($data) - 1;
            $this->http->put($uploadUrl, [
                'headers' => [
                    'Content-Length' => strlen($data),
                    'Content-Range'  => "bytes {$start}-{$end}/{$size}",
                ],
                'body' => $data,
            ]);
            $start = $end + 1;
        }
        fclose($fh);

        $get = $this->http->get("drives/{$driveId}/root:/".trim($folderPath,'/')."/{$filename}", [
            'headers' => $this->headers()
        ]);
        return json_decode($get->getBody(), true);
    }

    private function sanitize(string $name): string
    {
        // Quita caracteres no permitidos por SharePoint/OneDrive
        $name = preg_replace('/["*:<>?\/\\\\|#%]/', '-', $name);
        return trim($name, ". ");
    }
}
