<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\basic\User;
use Yii;

class UserController extends Controller {

	public function actionIndex() {
		echo 'command form: user/[command]' . PHP_EOL;
		return ExitCode::OK;
	}
	public function actionCreateUser($userId = '', $password = '') {
		if ($userId == '') {
			$userId = readline('Enter user name: ');
		}

		if (!User::find()->where(['user_id' => $userId])->one()) {
			$user = new User();
			if ('' == $password) $password = readline('Enter password: ');
			$pwd_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
			$access_token = Yii::$app->getSecurity()->generateRandomString();
			$user->user_id = $userId;
			$user->pwd_hash = $pwd_hash;
			$user->access_token = $access_token;
			echo $user->save() == true ?  'User created, OK' : 'User creation error, NG';
			echo PHP_EOL;
			return ExitCode::OK;
		} else {
			echo 'User: ' . $userId . ' already exists!' . PHP_EOL;
			return ExitCode::OK;
		}
	}

	public function actionChangePassword($userId = '') {
		if ($userId == '') {
			$userId = readline('Enter user name: ');
		}

		$user = User::find()->where(['user_id' => $userId])->one();
		if ($user) {
			$passwordOld = readline('Enter old password: ');
			$match = Yii::$app->getSecurity()->validatePassword($passwordOld, $user->pwd_hash);
			if ($match) {
				$passwordNew = readline('Enter new password: ');
				if ($passwordNew == '') return ExitCode::OK;

				$pwd_hash = Yii::$app->getSecurity()->generatePasswordHash($passwordNew);
				$user->pwd_hash = $pwd_hash;
				echo $user->save() == true ?  'User updated, OK' : 'User update error, NG';
				echo PHP_EOL;
				return ExitCode::OK;
			} else {
				echo 'Password incorrect' . PHP_EOL;
				return ExitCode::OK;
			}
		} else {
			echo 'User: ' . $userId . ' does not exist!' . PHP_EOL;
			return ExitCode::OK;
		}
	}

	public function actionShowUser($userId = '') {

		if ($userId == '') {
			$userId = readline('Enter user name: ');
		}

		$user = User::find()->where(['user_id' => $userId])->one();
		if ($user) {
			echo 'User Id: ' . $user->user_id . PHP_EOL . 'pwd_hash: ' . $user->pwd_hash . PHP_EOL . 'access_token: ' . $user->access_token . PHP_EOL;
			return ExitCode::OK;
		}
		echo 'User: ' . $userId . ' not found!' . PHP_EOL;
		return ExitCode::OK;
	}

	public function actionDeleteUser($userId = '') {
		if ($userId == '') {
			$userId = readline('Enter user name: ');
		}

		$user = User::find()->where(['user_id' => $userId])->one();
		if ($user) {
			$confirmation = readline('Really to delete: ' . $userId . ' ? ');
			if ($confirmation == 'yes') {
				if ($user->delete()) {
					echo 'User: ' . $userId . ' deleted !' . PHP_EOL;
					return ExitCode::OK;
				} else {
					echo 'User: ' . $userId . ' delete FAILED!' . PHP_EOL;
					return ExitCode::OK;
				}
			}
		} else {
			echo 'User:' . $userId . ' does not exist!' . PHP_EOL;
			return ExitCode::OK;
		}
	}
}
