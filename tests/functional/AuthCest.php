<?php

declare(strict_types=1);

/**
 * @noinspection PhpUnused
 */

namespace Hirtz\Cms\hotspot\tests\functional;

use Hirtz\Cms\hotspot\tests\support\FunctionalTester;
use Hirtz\Cms\Module;
use Hirtz\Cms\modules\admin\data\EntryActiveDataProvider;
use Hirtz\Cms\modules\admin\widgets\grids\EntryGridView;
use Hirtz\Skeleton\codeception\fixtures\UserFixtureTrait;
use Hirtz\Skeleton\codeception\functional\BaseCest;
use Hirtz\Skeleton\models\User;
use Hirtz\Skeleton\modules\admin\widgets\forms\LoginActiveForm;
use Yii;

class AuthCest extends BaseCest
{
    use UserFixtureTrait;

    public function checkIndexAsGuest(FunctionalTester $I): void
    {
        $I->amOnPage('/admin/entry/index');

        $widget = Yii::createObject(LoginActiveForm::class);
        $I->seeElement("#$widget->id");
    }

    public function checkIndexWithoutPermission(FunctionalTester $I): void
    {
        $this->getLoggedInUser();

        $I->amOnPage('/admin/file/index');
        $I->seeResponseCodeIs(403);
    }

    public function checkIndexWithPermission(FunctionalTester $I): void
    {
        $user = $this->getLoggedInUser();
        $auth = Yii::$app->getAuthManager()->getRole(Module::AUTH_ROLE_AUTHOR);
        Yii::$app->getAuthManager()->assign($auth, $user->id);

        $I->amOnPage('/admin/entry/index');

        $widget = Yii::$container->get(EntryGridView::class, [], [
            'dataProvider' => Yii::createObject(EntryActiveDataProvider::class),
        ]);

        $I->seeElement("#$widget->id");
    }

    protected function getLoggedInUser(): User
    {
        $user = User::find()->one();

        $webuser = Yii::$app->getUser();
        $webuser->loginType = 'test';
        $webuser->login($user);

        return $user;
    }
}
