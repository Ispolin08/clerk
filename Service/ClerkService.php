<?php
/**
 * Created by PhpStorm.
 * User: Ispolin
 * Date: 18.03.2018
 * Time: 13:11
 */


namespace Ispolin08\ClerkBundle\Service;

use Ispolin08\ClerkBundle\DataSource\DataSourceInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Twig\Template;

class ClerkService
{


    /** @var DataSourceInterface[] */
    private $dataSources = [];

    private $dataTransformers = [];

    private $cache;

    /** @var \Twig_Environment */
    private $template;

    /** @var  LoggerInterface[] */
    private $channels;

    private $checks;

    /**
     * ClerkService constructor.
     * @param $dataSources
     */
    public function __construct($checks, iterable $dataSources, iterable $dataTransformers, \Twig_Environment $template)
    {
        foreach ($dataSources as $dataSource) {
            // TODO check Interface
            $this->dataSources[get_class($dataSource)] = $dataSource;
        }

        foreach ($dataTransformers as $dataTransformer) {
//             TODO check Interface
            $this->dataTransformers[get_class($dataTransformer)] = $dataTransformer;
        }

        $this->cache = new FilesystemAdapter();
        $this->template = $template;

        $this->checks = $checks;

        $this->initChannels();

    }


    private function getDataSource($name)
    {
        // TODO Check if not exists in consturctor

        // Init SourceClass
        $dataSourceClass = 'Ispolin08\ClerkBundle\DataSource\\'.ucfirst($name)."DataSource";

        return $this->dataSources[$dataSourceClass];

    }

    private function getDataTransformer($class)
    {
        // TODO Check if not exists in consturctor
        return $this->dataTransformers[$class];
    }

    public function check($checkId)
    {

        $params = [];

        if (!isset($this->checks[$checkId])) {
            return false;
        }

        $check = $this->checks[$checkId];


        foreach ($check['data_sources'] as $dataSourceId => $dataSourceData) {

            $data = $this->getDataSource($dataSourceData['type'])->getData($dataSourceData['options']);

            if (isset($dataSourceData['transformers'])) {

                foreach ($dataSourceData['transformers'] as $transformerClass) {
                    $data = $this->getDataTransformer($transformerClass)->transform($data);
                }
            }

            $params[$dataSourceId] = $data;
        }


        // Get data from previs check
        $cacheItem = $this->cache->getItem($checkId);


        if (!$cacheItem->isHit()) {
            $prevCheckData = $params;
        } else {
            $prevCheckData = $cacheItem->get();
        }

        $cacheItem->set($params);
        $this->cache->save($cacheItem);

        $params['prevCheck'] = $prevCheckData;


        $message = $this->template->render('Checker/'.$checkId.'.html.twig', $params);

        $message = trim($message);

        if (!empty($message)) {
            // Send message to channel

            echo $message;

            if (isset($this->channels[$checkId])) {
                echo "send";
                $this->channels[$checkId]->debug($message);
            }
        }
    }

    protected function initChannels()
    {

        $handlers = [];

        foreach ($this->checks as $checkId => $check) {
            $channel = new \Monolog\Logger(self::getChannelName($checkId));

//            if ($this->env == 'dev') {
//                $check['handlers'] = [];
//            }


            if (!isset($check['handlers'])) {
                continue;
            }

            foreach ($check['handlers'] as $handler) {

                $key = $handler['type'].'_'.implode(',', $handler['options']);

                if (!isset($handlers[$key])) {

                    switch ($handler['type']) {
                        case 'telegram':
                            $handlers[$key] = new \Mero\Monolog\Handler\TelegramHandler(
                                $handler['options']['hash'],
                                $handler['options']['chat'],
                                \Monolog\Logger::DEBUG
                            );
                            break;
                        case 'slack':
                            break;

                        case 'email':
                            break;

                    }
                    $handlers[$key]->setFormatter(new \Monolog\Formatter\LineFormatter("%message%"));

                }

                $channel->pushHandler($handlers[$key]);


            }
            $this->channels[$checkId] = $channel;
        }
    }

    // move to abstract check class
    private static function getChannelName($checkId)
    {
        return 'check_'.$checkId;
    }

    /**
     * @return mixed
     */
    public function getChecks()
    {
        return $this->checks;
    }

    /**
     * @param mixed $checks
     */
    public function setChecks($checks)
    {
        $this->checks = $checks;
    }


}