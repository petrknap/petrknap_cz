<?php

namespace PetrKnap\Web\Test\Presenter;

use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use PetrKnap\Web\Presenter\ApiPresenter;
use PetrKnap\Web\Test\NetteTestCase;

class ApiPresenterTest extends NetteTestCase
{
    /**
     * @dataProvider dataAuthorizationBySecretKeyWorks
     * @param string $secretKey
     * @param bool $isValid
     */
    public function testAuthorizationBySecretKeyWorks($secretKey, $isValid)
    {
        if (!$isValid) {
            $this->setExpectedException(
                BadRequestException::class,
                ApiPresenter::MESSAGE_WRONG_KEY,
                IResponse::S403_FORBIDDEN
            );
        } else {
            $this->expectOutputString(ApiPresenter::MESSAGE_OK);
        }
        $this->runPresenter("Api", "default", [
            "secret_key" => $secretKey
        ]);
    }

    public function dataAuthorizationBySecretKeyWorks()
    {
        return [
            ["test", true],
            ["wrong key", false],
            [null, false]
        ];
    }
}
