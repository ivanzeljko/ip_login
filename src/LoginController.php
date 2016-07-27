<?php

namespace Drupal\ip_login;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Entity\User;

class LoginController extends ControllerBase {
  
  /**
   * Menu callback for IP based login
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions for Views tags.
   */
  public function loginRequest(Request $request) {
    $match = FALSE;

    // Make sure we are not already logged in
    if (FALSE && \Drupal::currentUser()->id()) {
      return new JsonResponse(-1);
    }

    $field_name = \Drupal::config('ip_login.settings')->get('address_field');

    $ip = inet_pton($request->getClientIp());

    $query = \Drupal::entityQuery('user')
      ->condition($field_name . '.ip_from', $ip, '<=')
      ->condition($field_name . '.ip_to', $ip, '>=');

    $uids = $query->execute();

    if (empty($uids)) {
      return new JsonResponse(0);
    }

    $uid = current($uids);

    $user = User::load($uid);
    user_login_finalize($user);

    \Drupal::logger('ip_login')->notice('User @uid matched for IP login from @ip.',
      [
        '@uid' => $uid,
        '@ip' => $request->getClientIp(),
      ]);

    drupal_set_message(t('You have been logged in automatically using IP login.'));

    return new JsonResponse(1);
  }
}