<?php

namespace PetrKnap\Web\Presenter;

use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use PetrKnap\Web\Service\CronService;
use PetrKnap\Web\Service\MigrationService;

class ApiPresenter extends Presenter
{
    const MESSAGE_OK = "OK";
    const MESSAGE_WRONG_KEY = "Wrong secret key.";

    /**
     * @inject
     * @var CronService
     */
    public $cronService;

    /**
     * @inject
     * @var MigrationService
     */
    public $migrationService;

    public function startup()
    {
        if ($this->getParameter("secret_key") != $this->context->getParameters()["secretKey"]) {
            throw new BadRequestException(self::MESSAGE_WRONG_KEY, IResponse::S403_FORBIDDEN);
        }
        parent::startup();
    }

    public function beforeRender()
    {
        parent::beforeRender();

        $this->getHttpResponse()->setContentType("text/plain");
        echo self::MESSAGE_OK;
        $this->terminate();
    }

    public function actionCron()
    {
        $this->cronService->run(new \DateTime());
    }

    public function actionMigrate()
    {
        $this->migrationService->migrate();
    }
}
