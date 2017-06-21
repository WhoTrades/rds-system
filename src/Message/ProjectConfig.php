<?php
/**
 * @author Artem Naumenko
 *
 * Сообщение-задача на обновление локальных конфигов у проекта. Сообщение генерируется RDS и обрабатывается сервисом deploy
 */
namespace RdsSystem\Message;

class ProjectConfig extends AbstractMultiServerTask
{
    public $project;
    public $configs = [];
    public $scriptUploadConfigLocal = '';

    /**
     * ProjectConfig constructor.
     *
     * @param string $project - имя проекта, в котором мы будем обновлять конфигурацию
     * @param array  $configs - список конфиг-файлов с содержимым. Формат: array(
     *    'config.local.php' => '<?php ...',
     *    'config2.local.php' => '<?php ...',
     *    'config3.local.php' => '<?php ...',
     * )
     * @param string $scriptUploadConfigLocal - sh скрипт по заливке конфигурации на сервера
     * @param array $projectServers - массив серверов для релиза
     */
    public function __construct($project, array $configs, $scriptUploadConfigLocal, array $projectServers)
    {
        $this->project  = $project;
        $this->configs   = $configs;
        $this->scriptUploadConfigLocal   = $scriptUploadConfigLocal;

        parent::__construct($projectServers);
    }
}
