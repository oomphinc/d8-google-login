<?php
/**
 * Our first Drupal 8 controller.
 */
namespace Drupal\google_oauth\Controller;

use Drupal\Core\StreamWrapper\PrivateStream;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\Entity\User;

use Google_Client;
use Google_Service_Oauth2;

class GoogleOAuthController extends ControllerBase {
	private $client;

	public function __construct() {
		$private_path = PrivateStream::basePath();
		$config_file = $private_path . '/google-oauth-secret.json';

		if(!is_readable($config_file)) {
			// Nag ?
			return;
		}

		$this->client = new Google_Client();
		$this->client->setAuthConfigFile($config_file);
		$this->client->setScopes(array('email'));
		$this->client->setState('offline');

		// Set the redirect URL which is used when redirecting and verifying
		// the one-time oauth code.
		$uri = \Drupal::url('google_oauth.authenticate', array(), array('absolute' => TRUE));

		$this->client->setRedirectUri($uri);
	}

	public function login() {
		if(!$this->client) {
			return;
		}

		return new TrustedRedirectResponse($this->client->createAuthUrl(), 301);
	}

	/**
	 *
	/**
	 * Authenticate, save user details, return access token
	 */
	public function authenticate() {
		$code = filter_input(INPUT_GET, 'code');

		if(empty($code) || !$this->client) {
			return new RedirectResponse('/');
		}

		try {
			$this->client->authenticate($code);
		} catch(\Exception $e) {
			return new RedirectResponse('/');
		}

		$token = json_decode($this->client->getAccessToken());

		$plus = new Google_Service_Oauth2($this->client);
		$userinfo = $plus->userinfo->get();

		$user_email = $userinfo['email'];

		$user = user_load_by_mail($user_email);

		if(!$user) {
			$user_name = $userinfo['name'];
			$user_picture = $userinfo['picture'];

			try {
				$user = User::create([
					'name' => $user_name,
					'mail' => $user_email,
					'status' => 'active',
					'picture' => $user_picture,
				]);

				$user->save();
			} catch(\Exception $e) {
				return new RedirectResponse('/');
			}
		}

		user_login_finalize($user);

		return $this->redirect('user.page');
	}
}
