<?php

namespace Drupal\tpedu\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Utility;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class tpeduController extends ControllerBase {

    public function handle(Request $request) {
        global $base_url;
        $config = \Drupal::config('tpedu.settings');
        if (!($config->get('enable'))) throw new AccessDeniedHttpException();
        $auth_code = $request->query->get('code');
        $user_email = $request->query->get('user');
        if ($auth_code) {
            get_tokens($auth_code);
            $uuid = who();
            $user = get_user($uuid);
            if ($user) {
                $account = \Drupal::database()->query("select * from users where uuid='$uuid'")->fetchObject();
                if (!$account) {
                    $new_user = [
                        'uuid' => $uuid,
                        'name' => $user->account,
                        'mail' => $user->email,
                        'init' => 'tpedu',
                        'pass' => substr($user->idno, -6),
                        'status' => 1,
                    ];
                    $account = User::create($new_user);
                    $account->save();
                } else {
                    $account = User::load($account->uid);
                }
                user_login_finalize($account);
                if (!empty($config->get('login_goto_url')))
                    $nextUrl = $config->get('login_goto_url');
                else
                    $nextUrl = $base_url;
                return new RedirectResponse($nextUrl);
            } else {
                drupal_set_message('您的帳號並非隸屬於本校，因此無法登入！', 'status', TRUE);
                return new RedirectResponse('/');
            }
        } elseif ($user_email) {
            $users = find_user([ 'email' => $user_email ]);
            if ($users && $user = $users[0]) {
                $account = \Drupal::database()->query("select * from users where uuid='$user->uuid'")->fetchObject();
                if (!$account) {
                    $new_user = [
                        'uuid' => $user->uuid,
                        'name' => $user->account,
                        'mail' => $user->email,
                        'init' => 'tpedu',
                        'pass' => substr($user->idno, -6),
                        'status' => 1,
                    ];
                    $account = User::create($new_user);
                    $account->save();
                } else {
                    $account = User::load($account->uid);
                }
                user_login_finalize($account);
                if (!empty($config->get('login_goto_url')))
                    $nextUrl = $config->get('login_goto_url');
                else
                    $nextUrl = $base_url;
                return new RedirectResponse($nextUrl);
            } else {
                drupal_set_message('很抱歉, 您的電郵信箱：'.$user_email.' 並未登錄於臺北市單一身份驗證服務，因此無法確認您的身份！請連線到 ldap.tp.edu.tw 變更您的電郵信箱！', 'status', TRUE);
                return new RedirectResponse('/');
            }
        } else {
            refresh_tokens();
            return new Response();
        }
    }

    public function purge(Request $request) {
        $database = \Drupal::database();
        $database->delete('tpedu_units')->execute();
        $database->delete('tpedu_roles')->execute();
        $database->delete('tpedu_classes')->execute();
        $database->delete('tpedu_subjects')->execute();
        $database->delete('tpedu_people')->execute();
        $database->delete('tpedu_jobs')->execute();
        $database->delete('tpedu_assignment')->execute();
        $build = [
            '#markup' => '所有的快取資料都已經移除，快取資料將會在下一次存取時自動取得並保留在資料庫中！',
        ];
        return $build;
    }

    public function notice(Request $request) {
        return array(
            '#theme' => 'tpedu_personal_data_notice',
        );
    }

}