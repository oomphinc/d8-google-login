<?php


/**
 * @file
 * Describe hooks provided by the Google OAuth module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter created user object, before save.
 */
function hook_google_oauth_create_user_alter(Drupal\user\Entity\User $user, Google_Service_Oauth2_Userinfoplus $userinfo) {
  $user->name->value = $userinfo->getEmail();
}

/**
 * @} End of "addtogroup hooks".
 */
