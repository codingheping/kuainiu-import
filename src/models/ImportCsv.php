<?php

namespace import\models;

use import\event\OssEvent;

class ImportCsv extends BizOssFile
{

    /**
     * @var array $config
     */
    public static $config = [];

    public const SUCCESS_STATUS = 'success';
    public const TODO_STATUS = 'todo';
    public const FAIL_STATUS = 'fail';
    public const ING_STATUS = 'ing';
    public const CLEAN_STATUS = 'clean';
    public const REBUILD_STATUS = 'rebuild';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'biz_oss_file';
    }

    public static function statusList(): array
    {
        return [
            self::SUCCESS_STATUS => '成功',
            self::TODO_STATUS    => '待处理',
            self::FAIL_STATUS    => '失败',
            self::ING_STATUS     => '处理中',
            self::CLEAN_STATUS   => '清理中',
            self::REBUILD_STATUS => '重新导入中',
        ];
    }

    public static function config(): array
    {
        return self::$config;
    }

    /**
     * @return array
     */
    public static function typeList(): array
    {
        $keys  = array_keys(self::config());
        $names = array_column(array_values(self::config()), 'name');

        return array_combine($keys, $names);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'file'], 'required'],
            [['type'], 'in', 'range' => array_keys(self::config())],
            [
                ['file'],
                'file',
                'extensions'               => ['csv', 'xls', 'xlsx', 'ods', 'xml', 'html', 'slk'],
                'maxSize'                  => 20 * 1024 * 1024,
                'checkExtensionByMimeType' => false,
                'uploadRequired'           => true,
            ],
            [['file'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'type'          => '类型',
            'create_by'     => '后台人员',
            'domain_url'    => '静态地址',
            'oss_file_name' => 'OSS文件名称',
            'file'          => '文件名称',
            'success_line'  => '导入成功数',
            'error_message' => '错误信息',
            'create_at'     => '创建时间',
            'update_at'     => '更新时间',
            'status'        => '状态',
            'search'        => '搜索',
        ];
    }

    public function uploadFile(): ?bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->domain_url    = $this->file->tempName;
        $this->oss_file_name = sprintf('import/csv/%s/%s', $this->type, md5_file($this->file->tempName));
        $this->status        = self::ING_STATUS;

        return $this->save(false);
    }


    public function download(): ?string
    {
        $event = new OssEvent();
        $this->trigger(self::EVENT_DOWNLOAD, $event);

        return $event->file;
    }


    public function updateMakeTask(): bool
    {
        $this->status       = self::REBUILD_STATUS;
        $this->success_line = 0;

        return $this->save(false);
    }
}

