<?php

namespace import\models;


use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "biz_oss_file".
 *
 * @property int         $id
 * @property int         $create_by        后台人员
 * @property string      $domain_url       静态地址
 * @property string      $oss_file_name    oss文件名称
 * @property int         $success_line     导入成功数
 * @property string      $create_at
 * @property string|null $update_at
 * @property string      $status           状态(success 成功 fail 失败 ing处理中 todo待处理 )
 * @property string      $type             业务类型
 * @property string|null $error_message    错误原因
 * @property string      $file             文件名称
 * @property string      $business_channel 业务场景方
 * @property string      $from_system      系统来源
 */
class BizOssFile extends ActiveRecord
{
    public const EVENT_DOWNLOAD = 'download';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'biz_oss_file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['create_by', 'success_line'], 'integer'],
            [['create_at', 'update_at'], 'safe'],
            [['type'], 'required'],
            [['error_message'], 'string'],
            [['domain_url'], 'string', 'max' => 256],
            [['oss_file_name', 'file'], 'string', 'max' => 64],
            [['status', 'business_channel', 'from_system', 'type'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'create_by'        => '后台人员',
            'domain_url'       => '静态地址',
            'oss_file_name'    => 'oss文件名称',
            'success_line'     => '导入成功数',
            'create_at'        => 'Create At',
            'update_at'        => 'Update At',
            'status'           => '状态(success 成功 fail 失败 ing处理中 todo待处理 )',
            'type'             => '业务类型',
            'error_message'    => '错误原因',
            'file'             => '文件名称',
            'business_channel' => '业务场景方',
            'from_system'      => '系统',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class'              => BlameableBehavior::class,
                'createdByAttribute' => 'create_by',
                'updatedByAttribute' => null,
            ],
        ];
    }

}
