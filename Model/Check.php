<?php

namespace Ispolin08\ClerkBundle\Model;


use Doctrine\Common\Collections\ArrayCollection;
use Ispolin08\ClerkBundle\DataSource\DataSourceInterface;
use Ispolin08\ClerkBundle\DataTransformer\DataTransformerInterface;

class Check {

    /** @var string */
    protected $id;

    /** @var string */
    protected $title;

    /** @var CheckParameter[] */
    protected $parameters;


    protected $channel;

    public function __construct()
    {
        $this->parameters = new  ArrayCollection();
        $this->handlers = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return CheckParameter[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param CheckParameter[] $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }


    /**
     * @param CheckParameter $parameter
     */
    public function addParameter(CheckParameter $parameter) {
        $this->parameters->add($parameter);
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param mixed $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }





}