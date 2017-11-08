<?php
/**
 * Our first Drupal 8 controller.
 */
namespace Drupal\google_oauth\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\StreamWrapper\PrivateStream;
use Drupal\user\Entity\User;
use Google_Client;
use Google_Service_Oauth2;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GoogleOAuthController extends ControllerBase {
    
  private $client;
  protected $config_factory;

  public function __construct(ConfigFactoryInterface $config_factory) {
    $private_path = PrivateStream::basePath();
    $config_file = $private_path . '/google-oauth-secret.json';
    $this->config_factory = $config_factory;

    if (!is_readable($config_file)) {
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

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  public function login() {
    if (!$this->client) {
      return;
    }

    return new TrustedRedirectResponse($this->client->createAuthUrl(), 301);
  }

  /**
   * Authenticate, save user details, return access token
   */
  public function authenticate() {
    $code = filter_input(INPUT_GET, 'code');

    if (empty($code) || !$this->client) {
      return new RedirectResponse('/');
    }

    try {
      $this->client->authenticate($code);
    }
    catch (\Exception $e) {
      return new RedirectResponse('/');
    }

    $plus = new Google_Service_Oauth2($this->client);
    $userinfo = $plus->userinfo->get();

    $user_email = $userinfo['email'];

    $user = user_load_by_mail($user_email);

    if (!$user) {
      $user_settings = $this->config_factory->get('user.settings');
      if ($user_settings->get('register') !== 'visitors') {
          // If settings don't allow to create a new account, then don't.
          drupal_set_message(
            t('You can\'t login using this account. Please use another e-mail address or ask an administrator to setup an account.'),
            'error'
          );
          return new RedirectResponse('/');
      }
      
      $user_name = $userinfo['name'];
      $user_picture = $userinfo['picture'];

      try {
        $user = User::create([
          'name' => $user_name,
          'mail' => $user_email,
          'status' => 1,
          'picture' => $user_picture,
        ]);

        // hook_google_oauth_create_user_alter($user, $userinfo);
        \Drupal::moduleHandler()->alter('google_oauth_create_user', $user, $userinfo);
        $user->save();
      }
      catch (\Exception $e) {
        return new RedirectResponse('/');
      }
    }

    user_login_finalize($user);

    return $this->redirect('<front>');
  }
}
