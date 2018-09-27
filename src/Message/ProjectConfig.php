<?php
/**
 * @author Artem Naumenko
 *
 * Сообщение-задача на обновление локальных конфигов у проекта. Сообщение генерируется RDS и обрабатывается сервисом deploy
 */
namespace whotrades\RdsSystem\Message;

class ProjectConfig extends AbstractMultiServerTask
{
    public $project;
    public $configs = [];
    public $scriptUploadConfigLocal = '';
    public $projectConfigHistoryId = null;

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
     * @param int | null $projectConfigHistoryId
     */
    public function __construct($project, array $configs, $scriptUploadConfigLocal, array $projectServers, $projectConfigHistoryId)
    {
        $this->project  = $project;
        $this->configs   = $configs;
        $this->scriptUploadConfigLocal   = $scriptUploadConfigLocal;
        $this->projectConfigHistoryId   = $projectConfigHistoryId;

        parent::__construct($projectServers);
    }
}
