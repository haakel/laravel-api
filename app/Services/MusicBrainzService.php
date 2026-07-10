<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MusicBrainzService
{
    protected string $baseUrl = 'https://musicbrainz.org/ws/2/recording/';
    protected string $userAgent = 'MusicApp/1.0 (contact@musicapp.com)';

    public function search(string $query, int $limit = 5): array
    {
        try {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'User-Agent' => $this->userAgent,
            ])
            ->timeout(10)
            ->get($this->baseUrl, [
                'query' => $query,
                'fmt' => 'json',
                'limit' => $limit,
            ]);

            if ($response->failed()) {
                Log::error('MusicBrainz API failed', [
                    'status' => $response->status(),
                    'query' => $query,
                ]);
                return [];
            }

            $data = $response->json();

            if (empty($data['recordings'])) {
                return [];
            }

            return collect($data['recordings'])->map(function ($rec) {
                return [
                    'title' => $rec['title'] ?? '',
                    'artist' => $rec['artist-credit'][0]['name'] ?? '',
                    'album' => $rec['releases'][0]['title'] ?? '',
                    'year' => substr($rec['releases'][0]['date'] ?? '', 0, 4),
                    'cover' => !empty($rec['releases'][0]['id'])
                        ? "https://coverartarchive.org/release/{$rec['releases'][0]['id']}/front"
                        : null,
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('MusicBrainz connection error', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);
            return [];
        }
    }

    public function searchByTitleAndArtist(string $title, ?string $artist = null): array
    {
        $query = trim(implode(' ', array_filter([$title, $artist])));
        return $this->search($query, 1);
    }
}
