<?php
/**
 * @author Artem Naumenko
 *
 * Сообщение-задача на обновление локальных конфигов у проекта. Сообщение генерируется RDS и обрабатывается сервисом deploy
 */
namespace RdsSystem\Message;

class ProjectConfig extends Base
{
    public $project;
    public $configs = [];

    /**
     * ProjectConfig constructor.
     *
     * @param string $project - имя проекта, в котором мы будем обновлять конфигурацию
     * @param array  $configs - список конфиг-файлов с содержимым. Формат: array(
     *    'config.local.php' => '<?php ...',
     *    'config2.local.php' => '<?php ...',
     *    'config3.local.php' => '<?php ...',
     * )
     */
    public function __construct($project, array $configs)
    {
        $this->project  = $project;
        $this->configs   = $configs;

        parent::__construct();
    }
}
