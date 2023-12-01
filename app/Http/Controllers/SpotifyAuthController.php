<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

class SpotifyAuthController extends Controller
{
    public function redirect()
    {
        $session = new Session(
            clientId: config('services.spotify.client_id'),
            clientSecret: config('services.spotify.client_secret'),
            redirectUri: config('services.spotify.redirect_uri'),
        );
        $state = $session->generateState();
        session()->put('spotify.state', $state);
        $redirect_url = $session->getAuthorizeUrl([
            'scope' => [
                'user-read-email',
                'user-read-recently-played',
                'user-read-playback-state',
                'user-modify-playback-state',
            ],
            'state' => $state,
        ]);

        return redirect($redirect_url);
    }

    public function callback()
    {
        $stored_state = session()->pull('spotify.state');
        $state = request()->get('state');

        if ($stored_state !== $state) {
            abort('403', 'State mismatch');
        }

        $session = new Session(
            clientId: config('services.spotify.client_id'),
            clientSecret: config('services.spotify.client_secret'),
            redirectUri: config('services.spotify.redirect_uri'),
        );
        $code = request()->get('code');
        $session->requestAccessToken($code);

        $api = new SpotifyWebAPI(
            options: [
                'auto_refresh' => true,
            ],
            session: $session,
        );

        $spotify_user = $api->me();

        $user = User::updateOrCreate([
            'spotify_id' => data_get($spotify_user, 'id'),
        ], [
            'name' => data_get($spotify_user, 'display_name'),
            'email' => data_get($spotify_user, 'email'),
            'password' => Str::random(),
            'spotify_access_token' => $session->getAccessToken(),
            'spotify_refresh_token' => $session->getRefreshToken(),
        ]);
        Auth::login($user);

        return to_route('dashboard');
    }
}
