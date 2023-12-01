<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

class DashboardController extends Controller
{
    public function __invoke()
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

        $data = [
            'me' => $api->me(),
        ];

        Auth::user()->update([
            'spotify_access_token' => $session->getAccessToken(),
            'spotify_refresh_token' => $session->getRefreshToken(),
        ]);

        return view('dashboard', $data);
    }
}
