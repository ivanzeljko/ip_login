<?php

namespace Drupal\ip_login;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Entity\User;
use Drupal\Core\Url;

class LoginController extends ControllerBase {
  
  /**
   * Menu callback for IP based login
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions for Views tags.
   */
  public function loginQuery(Request $request) {
    if ($uid = $this->checkIpLoginExists($request)) {
      \Drupal::logger('ip_login')->notice('User @uid matched for IP login from @ip (ajax call).',
        [
          '@uid' => $uid,
          '@ip' => $request->getClientIp(),
        ]);
      return new JsonResponse(1);
    }
    else {
      return new JsonResponse(0);
    }
  }

  /**
   * Menu callback for IP based login: do the actual login
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions for Views tags.
   */
  public function loginProcess(Request $request) {
    $uid = $this->checkIpLoginExists($request);

    if (empty($uid)) {
      \Drupal::logger('ip_login')->warning('IP login processing accessed without any matches from @ip.',
        [
          '@ip' => $request->getClientIp(),
        ]);

    }
    else {
      $user = User::load($uid);

      \Drupal::logger('ip_login')
        ->notice('Logging in user @uid through IP login from @ip.',
          [
            '@uid' => $uid,
            '@ip' => $request->getClientIp(),
          ]);

      user_login_finalize($user);

      drupal_set_message(t('You have been logged in automatically using IP login.'));
    }

    $destination = Url::fromUserInput(\Drupal::destination()->get());

    if ($destination->isRouted()) {
      // Valid internal path.
      return $this->redirect(
        $destination->getRouteName(),
        $destination->getRouteParameters()
      );
    }
    else {
      return $this->redirect('<front>');
    }
  }

  /**
   * Lookup if current request IP matches an IP login
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request
   * @return int|boolean uid
   *   The user ID or FALSE if not
   */
  public static function checkIpLoginExists(Request $request) {
    $field_name = \Drupal::config('ip_login.settings')->get('address_field');

    $ip = inet_pton($request->getClientIp());

    $query = \Drupal::entityQuery('user')
      ->condition($field_name . '.ip_from', $ip, '<=')
      ->condition($field_name . '.ip_to', $ip, '>=');

    $uids = $query->execute();

    if (empty($uids)) {
      return FALSE;
    }

    return current($uids);
  }
}