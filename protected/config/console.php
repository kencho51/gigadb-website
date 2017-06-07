<?php
$esConfig = json_decode(file_get_contents(dirname(__FILE__).'/es.json'), true);
$dbConfig = json_decode(file_get_contents(dirname(__FILE__).'/db.json'), true);
$awsConfig = json_decode(file_get_contents(dirname(__FILE__).'/aws.json'), true);
$previewConfig = json_decode(file_get_contents(dirname(__FILE__).'/preview.json'), true);
$pre_config = require(dirname(__FILE__).'/local.php');

// Location where user images are stored
Yii::setPathOfAlias('uploadPath',dirname(__FILE__).DIRECTORY_SEPARATOR.'../../images/uploads');
Yii::setPathOfAlias('uploadURL', '/images/uploads/');
Yii::setPathOfAlias('Elastica', realpath(dirname(__FILE__). '/../../Elastica/lib'));
Yii::setPathOfAlias('googleAPI', realpath(dirname(__FILE__).'/../../google-api-php-client/src'));
Yii::setPathOfAlias('scholar', realpath(dirname(__FILE__).'/../scripts/scholar.py'));

//Importing AWS SDK
Yii::setPathOfAlias('Aws',dirname(__FILE__).DIRECTORY_SEPARATOR.'../vendors/aws/Aws');
Yii::setPathOfAlias('Guzzle',dirname(__FILE__).DIRECTORY_SEPARATOR.'../vendors/aws/Guzzle');
Yii::setPathOfAlias('Symfony',dirname(__FILE__).DIRECTORY_SEPARATOR.'../vendors/aws/Symfony');


//Importing beanstalkd client
Yii::setPathOfAlias('Beanstalk',dirname(__FILE__).DIRECTORY_SEPARATOR.'../vendors/beanstalk');

return CMap::mergeArray(array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>'GigaDB',

    'preload'=>array(
        'log',
        'bootstrap',
    ),

    'import'=>array(
        'application.models.*',
        'application.components.*',
        'application.behaviors.*',
        'application.vendors.*',
        'application.vendors.beanstalk',
        'application.helpers.*',
    ),

    'modules'=>array(
        'gii'=>array(
            'class'=>'system.gii.GiiModule',
            'password'=>'gigadbyii',
            'ipFilters'=>array('*.*.*.*'),
        ),
        'opauth' => array(
            'opauthParams' => array(
                'Security.salt' => '1234',
                'Strategy' => array(
                    'Facebook' => array(
                        'app_id' => '',
                        'app_secret' => '',
                    ),
                    'LinkedIn' => array(
                        'api_key' => '',
                        'secret_key' => '',
                    ),
                    'Google' => array(
                        'client_id' => '',
                        'client_secret' => '',
                    ),
                    'Twitter' => array(
                        'key' => '',
                        'secret' => '',
                    ),
                    'Orcid' => array(
                        'client_id' => '',
                        'client_secret' => '',
                    ),
                ),
            ),
        ),
    ),

    'components'=>array(
        'db'=>array(
            'class'=>'system.db.CDbConnection',
            'connectionString'=>"pgsql:dbname={$dbConfig['database']};host={$dbConfig['host']}",
            'username'=>$dbConfig['user'],
            'password'=>$dbConfig['password'],
            'charset'=>'utf8',
            'persistent'=>true,
            'enableParamLogging'=>true,
            'schemaCachingDuration'=>30
        ),
        'ftp' => array(
            'class' => 'ext.GFtp.GFtpApplicationComponent',
            'connectionString' => 'ftp://anonymous:anonymous@10.1.1.33:21',
            'timeout' => 120,
            'passive' => true
        ),
        'aws'=>array(
            'class' => 'application.components.AwsYiiConfig',
            'access_key' => $awsConfig['access_key'],
            'secret_key' => $awsConfig['secret_key'],
            'preview_bucket' => $awsConfig['s3_bucket_for_file_previews'],
          ),
        'mfr'=>array(
            'class' => 'application.components.ModularFileRenderer',
            'previewServer' => '128.199.125.190:7778',
            'supportedExtensions' => $previewConfig['mfr_supported_extensions'],
          ),
        'preview'=>array(
            'class' => 'application.components.FilePreview',
            'supported_media_types' => $previewConfig['supported_types'],
            'preview_job_queue' => 'previewgeneration',
            'temporary_directory' => '/tmp/previews',
            'preview_bucket' => $awsConfig['s3_bucket_for_file_previews'],
        ),
        'multidownload'=>array(
            'class' => 'application.components.MultiDownload',
            'download_host' => '10.1.1.33',
            'download_protocol' => 'ftp://',
            'multidownload_job_queue' => 'filespackaging',
            'temporary_directory' => '/tmp/bundles',
            'ftp_bundle_directory' => '/pub/user_bundles',
        ),
        'request' => array(
            //'enableCookieValidation' => true,
        ),

        'bootstrap'=>array(
            'class'=>'ext.bootstrap.components.Bootstrap',
        ),
        'cache' => array(
            'class' => 'system.caching.CFileCache'
        ),
        'redis'=>array(
            'class'=>'CRedisCache',
            'hostname'=>'10.1.1.35',
            'port'=>6379,
            'database'=>0,
        ),
        'session' => array(
            'class' => 'system.web.CDbHttpSession',
            'connectionID' => 'db',
            'timeout' => 3600,
        ),
        'beanstalk'=>array(
            'class'=>'application.components.Beanstalk',
            'servers'=>array(
                'server1'=>array(
                    'host'=>'10.1.1.35',
                    'port'=>11300,
                    'weight'=>50,
                    // array of connections/tubes
                    'connections'=>array(),
                ),

            ),
        ),
        'errorHandler'=>array(
            'errorAction'=>'site/error',
        ),
        'urlManager'=>array(
            'urlFormat'=>'path',
            'showScriptName'=>false,
            'rules'=>array(
                '/dataset/<id:\d+>'=>'dataset/view/id/<id>',
                '/dataset/<id:\d+>/<slug:.+>'=>'dataset/view/id/<id>',
                '/file/download/<bid:\w+>'=>'file/download',
                '/file/preview/'=>'file/preview',
                '/file/<operation:\w+>'=>'file/bundle',
                //'search'=>'site/index',
                //'download/<search:.+>'=>'site/index',
                //'download'=>'site/index',
                /*
		array('api/list', 'pattern'=>'api/<model:\w+>', 'verb'=>'GET'),
                array('api/view', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'GET'),
                array('api/sample', 'pattern'=>'api/sample/<name>', 'verb'=>'GET'),
                array('api/keyword', 'pattern'=>'api/keyword/<keyword:\w+>', 'verb'=>'GET'),

                //array('api/keyword', 'pattern'=>'api/keyword/<keyword:\w+>/type/<type:\w+>', 'verb'=>'GET'),
                array('api/author', 'pattern'=>'api/<action:\w+>/<name>', 'verb'=>'GET'),
                array('api/update', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'PUT'),
                array('api/delete', 'pattern'=>'api/<model:\w+>/<id:\d+>', 'verb'=>'DELETE'),
                array('api/create', 'pattern'=>'api/<model:\w+>', 'verb'=>'POST'),
                 * */
                '.*'=>'site/index',
            ),
        ),
        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'error, warning, info, debug',
                ),
                //array(
                //    'class'=>'CWebLogRoute',
                //),
            ),
        ),
        'elastic' => array(
            'class' => 'Elastic',
            'host' => $esConfig['host'],
            'port' => $esConfig['port']
        ),

        'messages'=>array(
            'class'=>'CPhpMessageSource',
        ),
        'user'=>array(
            // enable cookie-based authentication
            'allowAutoLogin'=>true,
            //User WebUser
            'class'=>'WebUser',
        ),
        'authManager'=>array(
            'class'=>'CDbAuthManager',
            'connectionID'=>'db',
        ),
        'image'=>array(
            'class'=>'application.extensions.image.CImageComponent',
            // GD or ImageMagick
            'driver'=>'GD',
        ),
    ),

    'params' => array(
        # ES search
        'es_search' => array(
            'limits' => array(
                'default' => 10,
                'dataset' => 20,
                'file' => 20,
            ),
        ),
        'scholar_query' => 'http://scholar.google.com/scholar?q=',
        'ePMC_query' => "http://europepmc.org/search?scope=fulltext&query=",
        // date formats
        'js_date_format' => 'dd-mm-yy',
        'db_date_format' => "%Y-%m-%d",
        'display_date_format' => "%gggggggd-%m-%Y",
        'display_short_date_format' => "%d-%m",
        'language' => 'en' ,
        'languages' => array('en' => 'EN', 'zh_tw' => 'TW'),
    ),
), $pre_config);
