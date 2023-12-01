<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

class Dashboard extends Component
{
    #[Url(history: true, except: '')]
    public string $search = '';

    public function addToQueue(string $id)
    {
        $user = Auth::user();

        $session = new Session(
            clientId: config('services.spotify.client_id'),
            clientSecret: config('services.spotify.client_secret'),
        );
        $session->setAccessToken($user->spotify_access_token);
        $session->setRefreshToken($user->spotify_refresh_token);

        $api = new SpotifyWebAPI(
            options: [
                'auto_refresh' => true,
            ],
            session: $session,
        );

        $api->queue($id);

        $user->update([
            'spotify_access_token' => $session->getAccessToken(),
            'spotify_refresh_token' => $session->getRefreshToken(),
        ]);
    }

    public function render()
    {
        $user = Auth::user();

        $session = new Session(
            clientId: config('services.spotify.client_id'),
            clientSecret: config('services.spotify.client_secret'),
        );
        $session->setAccessToken($user->spotify_access_token);
        $session->setRefreshToken($user->spotify_refresh_token);

        $api = new SpotifyWebAPI(
            options: [
                'auto_refresh' => true,
            ],
            session: $session,
        );

        $recent_songs = collect(data_get($api->getMyRecentTracks(), 'items.*.track', []))
            ->map(fn ($song) => [
                'id' => data_get($song, 'id'),
                'cover' => data_get($song, 'album.images.0.url'),
                'name' => data_get($song, 'name'),
                'artists' => data_get($song, 'artists.*.name'),
            ])
            ->toArray();
        $queue = $api->getMyQueue();
        $playback_info = $api->getMyCurrentPlaybackInfo();

        $queue_songs = [];
        $remaining_ms = data_get($playback_info, 'item.duration_ms') - data_get($playback_info, 'progress_ms');
        $previous_duration_ms = $remaining_ms;

        foreach (data_get($queue, 'queue') as $song) {
            $queue_songs[] = [
                'id' => data_get($song, 'id'),
                'cover' => data_get($song, 'album.images.0.url'),
                'name' => data_get($song, 'name'),
                'playing_in_ms' => $previous_duration_ms,
                'artists' => data_get($song, 'artists.*.name'),
            ];
            $previous_duration_ms = $previous_duration_ms + data_get($song, 'duration_ms');
        }

        if (blank($this->search)) {
            $results = [];
        } else {
            $search = $api->search($this->search, 'track', [
                'market' => 'DE',
                'limit' => 50,
                'offset' => 0,
            ]);
            $results = collect(data_get($search, 'tracks.items', []))
                ->map(fn ($song) => [
                    'id' => data_get($song, 'id'),
                    'cover' => data_get($song, 'album.images.0.url'),
                    'name' => data_get($song, 'name'),
                    'artists' => data_get($song, 'artists.*.name'),
                    'playing' => $playing = data_get($song, 'id') === data_get($playback_info, 'item.id'),
                    'in_queue' => $in_queue = collect($queue_songs)
                        ->filter(fn ($queue_song) => data_get($queue_song, 'id') === data_get($song, 'id'))
                        ->isNotEmpty(),
                    'recently_played' => $recently_played = collect($recent_songs)
                        ->filter(fn ($recent_song) => data_get($recent_song, 'id') === data_get($song, 'id'))
                        ->isNotEmpty(),
                    'playable' => data_get($song, 'is_playable') && ! $playing && ! $in_queue && ! $recently_played,
                ])
                ->toArray();
        }

        $data = [
            'results' => $results,
            'currently_playing' => [
                'id' => data_get($playback_info, 'item.id'),
                'cover' => data_get($playback_info, 'item.album.images.0.url'),
                'name' => data_get($playback_info, 'item.name'),
                'remaining_ms' => $remaining_ms,
                'progress_percent' => round((data_get($playback_info, 'progress_ms') / data_get($playback_info, 'item.duration_ms')) * 100),
                'artists' => data_get($playback_info, 'item.artists.*.name'),
                'is_playing' => data_get($playback_info, 'is_playing'),
            ],
            'queue' => $queue_songs,
            'playback_info' => $playback_info,
        ];

        $user->update([
            'spotify_access_token' => $session->getAccessToken(),
            'spotify_refresh_token' => $session->getRefreshToken(),
        ]);

        return view('livewire.dashboard', $data);
    }
}
