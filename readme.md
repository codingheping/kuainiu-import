## 通用导入组件

# 目录

- [1.目录结构](#1目录结构)
- [2.在config配置文件配置模块](#2在config配置文件配置模块)
- [3.配置每个功能的回调事件(具体根据场景来实现)](#3配置每个功能的回调事件(具体根据场景来实现))
- [4.重要文件说明](#4重要文件说明)
- [5.备注](#5备注)
- [6.使用建议](#6使用建议)

## 1.目录结构

```
├── examples                                       案列目录
│   ├── filter                                     filter过滤目录
│   │    └── DemoFilter.php                        filterdemo文件
│   ├── formatter                                  格式化类目录
│   |    └── ExamplesDataDataFormatterBehavior.php demo文件
│   ├── listener                                   事件监听目录(事件回调后执行的程序)
│   |    └── OssListener.php                       事件回调类
│   ├── model                                      数据导入类目录
│   |    └── ChannelReconci.php                    demo文件
│   ├── oss                                        oss目录
│   |    └── LocalAdapter.php                      demo文件
│   ├── task                                       oss目录
│   |    └── ImportCsvTaskHandler.php              导入TASK
│   |    └── CleanCsvTaskHandler.php               清理数据TASK
│   ├── template.json                              kv配置        
├── src                                            组件目录
│   ├── behaviors                                  行为类目录
│   │    ├── ImportDataFormatterBehavior.php       格式化数据类                  
│   └── controllers   
│   │    ├── ImportDataFormatterBehavior.php       导入控制器                
│   └── event   
│   │    ├── OssEvent.php                          Oss事件类  
│   └── interfaces   
│   │    ├── InterfaceFileSystem.php               文件OSS资源接口
│   │    ├── InterfaceFilter.php                   数据过滤接口
│   │    ├── InterfaceImportService.php            导入服务接口
│   └── kernel   
│   └──────── db 
│   │         ├── QueryBuilder.php                 QueryBuilder                   
│   └──────── phpoffice  
│   │         ├── ReadFilter.php                   ReadFilter                      
│   │         ├── Row.php                          Row                          
│   └──────── response
│   │         ├── ImportResponse.php               导入结果响应类                            
│   └── messages                                   语言包
│   │    ├── en                                    英文
│   │    ├── zh-CN                                 中文
│   └── models   
│   │    ├── BizOssFile.php                        BizOssFile
│   │    ├── ImportCsv.php                         ImportCsv  
│   │    ├── ImportCsvSearch.php                   ImportCsvSearch
│   └── services   
│   │    ├── ImportService.php                     导入服务service
│   └── views   
│   └──────── import 
│   │         ├── _search.php                      搜索视图
│   │         ├── csv.php                          导入视图
│   │         ├── index.php                        列表
│   └── Module.php
├── test  
├── composer.json                                  包管理工具
└── README.MD                                      文档说明
```

## 2.在config配置模块

```php
use import\models\BizOssFile;
Use yii\base\Event;
use common\models\KeyValue;
use import\examples\listener\OssListener;
'modules'         => [
        'import'     => [
            'class'                            => \import\Module::class,
            'on ' . \import\Module::EVENT_INIT => function ($event) {
                ImportCsv::$config = KeyValue::take('channel_reconci_template');
                 
                 //事件handler
                $importHandler = function (Event $event) {
                    $listener = new OssListener();
                    $listener->process($event);
                };
                //创建导入task
                Event::on(BizOssFile::class, BizOssFile::EVENT_AFTER_INSERT, $importHandler);
                //重新导入
                Event::on(BizOssFile::class, BizOssFile::EVENT_AFTER_UPDATE, $importHandler);
                //上传文件
                Event::on(BizOssFile::class, BizOssFile::EVENT_BEFORE_INSERT, $importHandler);
                //删除
                Event::on(BizOssFile::class, BizOssFile::EVENT_AFTER_DELETE, $importHandler);
                //下载
                Event::on(BizOssFile::class, BizOssFile::EVENT_DOWNLOAD, $importHandler);
            },
        ],
    ]
```

## 3.配置每个功能的回调事件(具体根据场景来实现)

```php
 protected function clean(ImportCsv $csv): void;
 protected function import(ImportCsv $csv): void
 protected function delete(ImportCsv $csv): void
 protected function download(ImportCsv $csv): void
 protected function upload(ImportCsv $csv): void
```

## 4.重要文件说明

```
ImportService.php  
如果用户需要实现脱敏服务,需要注册self::EVENT_DESENSITISE脱敏事件.
```

```php

 /**
     * @param array $data
     *
     * @return int
     * @throws InvalidConfigException|\yii\db\Exception|UserException
     */
    protected function batchWrite(array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        $event = new Event();

        $event->data = $data;
        $this->trigger(self::EVENT_DESENSITISE, $event);

        return $this->upsert($event->data);
    }
```

## 5.备注
examples目录的task均实现了分段读取文件的task，可以结合业务场景参考代码实现自己的逻辑.

## 6.使用建议
暂无
