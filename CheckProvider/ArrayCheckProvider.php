<?php
/**
 * Created by PhpStorm.
 * User: Ispolin
 * Date: 18.03.2018
 * Time: 13:11
 */


namespace Ispolin08\ClerkBundle\CheckProvider;

use Ispolin08\ClerkBundle\DataSource\DataSourceInterface;
use Ispolin08\ClerkBundle\DataTransformer\DataTransformerInterface;
use Ispolin08\ClerkBundle\Model\Check;
use Ispolin08\ClerkBundle\Model\CheckParameter;
use Ispolin08\Monolog\Handler\TelegramHandler;
use Monolog\Handler\StreamHandler;

class ArrayCheckProvider implements CheckProviderInterface
{
    /** @var DataSourceInterface[] */
    private $dataSources = [];

    /** @var DataTransformerInterface[] */
    private $dataTransformers = [];

    protected $sourceData;

    protected $checkClass;

    function provideChecks()
    {
        $result = [];
        $handlers = [];

        foreach ($this->sourceData  as $checkId => $checkData) {

            $check = new Check();

            $check->setId($checkId);

            // Set parameters
            foreach ($checkData['parameters'] as $parameterId => $parameter) {

                // Create Parameter
                $checkParameter = new CheckParameter();

                $checkParameter->setId($parameterId);

                // Set DataSource
                $checkParameter->setDataSource($this->findDataSourceByName($parameter['source']['class']));
                $checkParameter->setDataSourceOptions($parameter['source']['options']);

                // Set all transformers
                if (isset($parameter['transformers'])) {
                    foreach ($parameter['transformers'] as $transformerClass) {
                        $checkParameter->addDataTransformer($this->getDataTransformer($transformerClass));
                    }
                }

                $check->addParameter($checkParameter);
            }


            // Setup channel

            if (isset($checkData['handlers'])) {

                $channel = new \Monolog\Logger(self::getChannelName($checkId));

                foreach ($checkData['handlers'] as $handler) {

                    // TODO Use static
                    $key = $handler['type'].'_'.implode(',', $handler['options']);

                    // TODO Create as Symfony way
                    if (!isset($handlers[$key])) {

                        switch ($handler['type']) {
                            case 'telegram':
                                $handlers[$key] = new TelegramHandler(
                                    $handler['options']['hash'],
                                    $handler['options']['chat'],
                                    \Monolog\Logger::DEBUG
                                );
                                $handlers[$key]->setFormatter(new \Monolog\Formatter\LineFormatter("%message%", null, true));
                                break;

                        }

                    }
                    $channel->pushHandler($handlers[$key]);


                }

                $check->setChannel($channel);

            }

            $result[$checkId] = $check;
        }

        return $result;
    }


    public function __construct(array $sourceData, $checkClass, $dataSources, $dataTransformers)
    {
        $this->sourceData = $sourceData;
        $this->checkClass = $checkClass;

        // TODO Test Inrefaces

        foreach ($dataSources as $dataSource) {
            if (!$dataSource instanceof DataSourceInterface) {
                throw new \Exception(':/');
            }

            $this->dataSources[get_class($dataSource)] = $dataSource;
        }

        foreach ($dataTransformers as $dataTransformer) {

            if (!$dataTransformer instanceof DataTransformerInterface) {
                throw new \Exception(':/');
            }

            $this->dataTransformers[get_class($dataTransformer)] = $dataTransformer;
        }

    }


    private function findDataSourceByName($name)
    {
        // TODO Check if not exists in consturctor

        // Init SourceClass
        $dataSourceClass = 'Ispolin08\ClerkBundle\DataSource\\'.ucfirst($name)."DataSource";

        if (!isset($this->dataSources[$dataSourceClass])) {
            $dataSourceClass = $name;

        }

        return $this->dataSources[$dataSourceClass];

    }



    private function getDataTransformer($class)
    {
        // TODO Check if not exists in consturctor
        return $this->dataTransformers[$class];
    }


    // move to abstract check class
    private static function getChannelName($checkId)
    {
        return 'check_'.$checkId;
    }




}