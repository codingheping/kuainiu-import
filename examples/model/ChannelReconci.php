<?php

namespace import\examples\model;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * This is the model class for table "{{%channel_reconci}}".
 *
 * @property int          $channel_reconci_id
 * @property string       $channel_reconci_date              订单日期
 * @property string       $channel_reconci_channel_name      支付通道名称
 * @property string       $channel_reconci_channel_key       商户订单号
 * @property string       $channel_reconci_channel_order_no  通道平台订单号
 * @property int          $channel_reconci_amount            金额
 * @property string       $channel_reconci_bank_name         银行名称
 * @property string       $channel_reconci_bank_code         银行编码
 * @property string       $channel_reconci_account           实际账号
 * @property string       $channel_reconci_user_name         姓名
 * @property string       $channel_reconci_remark            备注
 * @property string|null  $channel_reconci_order_created_at  流水创建时间
 * @property string|null  $channel_reconci_order_finished_at 流水完成时间
 * @property int          $channel_reconci_status            订单状态：0=新建, 1=处理中，2=成功，3=失败
 * @property string       $channel_reconci_type              交易类型: withhold=代扣 withdraw=代付 verify=鉴权
 * @property string       $channel_reconci_created_at
 * @property string       $channel_reconci_updated_at
 * @property string       $channel_reconci_provider_code     渠道编码
 * @property string       $channel_reconci_merchant_no       渠道商户编号
 * @property string       $channel_reconci_settlement_id     结算批次
 * @property int          $channel_reconci_settlement_amount 结算金额=channel_reconci_amount-channel_reconci_fees
 * @property string       $channel_reconci_payment_mode      支付模式，如bank_account,card,upi
 * @property int          $channel_reconci_fees              实际成本=channel_reconci_service_charge+channel_reconci_service_tax
 * @property int          $channel_reconci_service_charge    服务费，某些通道需要反推
 * @property int          $channel_reconci_service_tax       税费
 * @property-read Channel $channel
 */
class ChannelReconci extends ActiveRecord
{
    const TYPE_VERIFY = 'verify';
    const TYPE_WITHHOLD = 'withhold';
    const TYPE_WITHDRAW = 'withdraw';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%channel_reconci}}';
    }

    /**
     * @return Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return parent::getDb();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                [
                    'channel_reconci_date',
                    'channel_reconci_order_created_at',
                    'channel_reconci_order_finished_at',
                    'channel_reconci_created_at',
                    'channel_reconci_updated_at',
                ],
                'safe',
            ],
            [
                [
                    'channel_reconci_amount',
                    'channel_reconci_status',
                    'channel_reconci_settlement_amount',
                    'channel_reconci_fees',
                    'channel_reconci_service_charge',
                    'channel_reconci_service_tax',
                ],
                'integer',
            ],
            [['channel_reconci_settlement_amount'], 'required'],
            [
                [
                    'channel_reconci_channel_name',
                    'channel_reconci_channel_key',
                    'channel_reconci_channel_order_no',
                    'channel_reconci_account',
                    'channel_reconci_user_name',
                ],
                'string',
                'max' => 64,
            ],
            [
                [
                    'channel_reconci_bank_name',
                    'channel_reconci_bank_code',
                    'channel_reconci_type',
                    'channel_reconci_provider_code',
                    'channel_reconci_merchant_no',
                    'channel_reconci_settlement_id',
                    'channel_reconci_payment_mode',
                ],
                'string',
                'max' => 32,
            ],
            [['channel_reconci_remark'], 'string', 'max' => 255],
            [
                ['channel_reconci_channel_key', 'channel_reconci_channel_name'],
                'unique',
                'targetAttribute' => ['channel_reconci_channel_key', 'channel_reconci_channel_name'],
            ],
            [
                ['channel_reconci_channel_order_no', 'channel_reconci_channel_name'],
                'unique',
                'targetAttribute' => ['channel_reconci_channel_order_no', 'channel_reconci_channel_name'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'channel_reconci_id'                => Yii::t('payment', 'Channel Reconci ID'),
            'channel_reconci_date'              => Yii::t('payment', '订单日期'),
            'channel_reconci_channel_name'      => Yii::t('payment', '支付通道名称'),
            'channel_reconci_channel_key'       => Yii::t('payment', '商户订单号'),
            'channel_reconci_channel_order_no'  => Yii::t('payment', '通道平台订单号'),
            'channel_reconci_amount'            => Yii::t('payment', '金额'),
            'channel_reconci_bank_name'         => Yii::t('payment', '银行名称'),
            'channel_reconci_bank_code'         => Yii::t('payment', '银行编码'),
            'channel_reconci_account'           => Yii::t('payment', '实际账号'),
            'channel_reconci_user_name'         => Yii::t('payment', '姓名'),
            'channel_reconci_remark'            => Yii::t('payment', '备注'),
            'channel_reconci_order_created_at'  => Yii::t('payment', '流水创建时间'),
            'channel_reconci_order_finished_at' => Yii::t('payment', '流水完成时间'),
            'channel_reconci_status'            => Yii::t('payment', '订单状态：0=新建, 1=处理中，2=成功，3=失败'),
            'channel_reconci_type'              => Yii::t('payment', '交易类型: withhold=代扣 withdraw=代付 verify=鉴权'),
            'channel_reconci_created_at'        => Yii::t('payment', 'Channel Reconci Created At'),
            'channel_reconci_updated_at'        => Yii::t('payment', 'Channel Reconci Updated At'),
            'channel_reconci_provider_code'     => Yii::t('payment', '渠道编码'),
            'channel_reconci_merchant_no'       => Yii::t('payment', '渠道商户编号'),
            'channel_reconci_settlement_id'     => Yii::t('payment', '结算批次'),
            'channel_reconci_settlement_amount' => Yii::t('payment',
                '结算金额=channel_reconci_amount-channel_reconci_fees'),
            'channel_reconci_payment_mode'      => Yii::t('payment', '支付模式，如bank_account,card,upi'),
            'channel_reconci_fees'              => Yii::t('payment',
                '实际成本=channel_reconci_service_charge+channel_reconci_service_tax'),
            'channel_reconci_service_charge'    => Yii::t('payment', '服务费，某些通道需要反推'),
            'channel_reconci_service_tax'       => Yii::t('payment', '税费'),
        ];
    }
}
