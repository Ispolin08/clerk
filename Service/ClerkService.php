<?php
/**
 * Created by PhpStorm.
 * User: Ispolin
 * Date: 18.03.2018
 * Time: 13:11
 */


namespace Ispolin08\ClerkBundle\Service;

use Ispolin08\ClerkBundle\CheckProvider\CheckProviderInterface;
use Ispolin08\ClerkBundle\DataSource\DataSourceInterface;
use Ispolin08\ClerkBundle\Model\Check;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Twig\Template;

class ClerkService
{


    /** @var  CheckProviderInterface */
    protected $checkProvider;

    private $cache;

    /** @var \Twig_Environment */
    private $template;

    /** @var  LoggerInterface[] */
    private $channels;

    /** @var  Check[] */
    private $checks;

    /**
     * ClerkService constructor.
     * @param $dataSources
     */
    public function __construct(CheckProviderInterface $checkProvider, \Twig_Environment $template)
    {

        // TODO CHECK IFACE
        $this->checks = $checkProvider->provideChecks();


        // TODO CONFIG IT
        $this->cache = new FilesystemAdapter();
        $this->template = $template;

    }

    public function process($checkId)
    {

        $templateParams = [];

        if (!isset($this->checks[$checkId])) {
            throw new \Exception('Check '.$checkId.' not found');
        }

        $check = $this->checks[$checkId];

        // Process all parameters
        foreach ($check->getParameters() as $checkParameter) {

            // Get Source
            $data = $checkParameter->getDataSource()->getData($checkParameter->getDataSourceOptions());

            // Transform
            foreach ($checkParameter->getDataTransformers() as $dataTransformer) {
                $data = $dataTransformer->transform($data);
            }

            // Store for template
            $templateParams[$checkParameter->getId()] = $data;
        }

        // Get data from previs check
        $templateParams['lastTime'] = $this->getLastTimeData($check, $templateParams);

        // TODO Check Existence of all sub keys;


        // TODO Configure it
        $message = $this->template->render('Checker/'.$checkId.'.html.twig', $templateParams);

        $message = trim($message);

        if (!empty($message)) {
            // Send message to channel

            // TODO Dispatch event?
            // TODO LOGGER

            $check->getChannel()->info($message);
        }
    }

    protected function getLastTimeData(Check $check, $params)
    {
        $cacheItem = $this->cache->getItem($check->getId());

        if (!$cacheItem->isHit()) {
            $lastTimeData = $params;
        } else {
            $lastTimeData = $cacheItem->get();
        }

        $cacheItem->set($params);
        $this->cache->save($cacheItem);

        return $lastTimeData;
    }


}