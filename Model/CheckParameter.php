<?php

namespace Ispolin08\ClerkBundle\Model;


use Doctrine\Common\Collections\ArrayCollection;
use Ispolin08\ClerkBundle\DataSource\DataSourceInterface;
use Ispolin08\ClerkBundle\DataTransformer\DataTransformerInterface;

class CheckParameter {

    /** @var string */
    protected $id;

    /** @var DataTransformerInterface[] */
    protected $dataTransformers;

    /** @var DataSourceInterface */
    protected $dataSource;

    /** @var array */
    protected $dataSourceOptions;

    /**
     * CheckParameter constructor.
     * @param DataSourceInterface $dataSource
     */
    public function __construct()
    {
        $this->dataTransformers = new ArrayCollection();
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
     * @return DataTransformerInterface[]
     */
    public function getDataTransformers()
    {
        return $this->dataTransformers;
    }

    public function addDataTransformer(DataTransformerInterface $dataTransformer) {
        $this->dataTransformers->add($dataTransformer);
    }

    /**
     * @param DataTransformerInterface[] $dataTransformers
     */
    public function setDataTransformers($dataTransformers)
    {
        $this->dataTransformers = $dataTransformers;
    }

    /**
     * @return DataSourceInterface
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * @param DataSourceInterface $dataSource
     */
    public function setDataSource($dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * @return array
     */
    public function getDataSourceOptions()
    {
        return $this->dataSourceOptions;
    }

    /**
     * @param array $dataSourceOptions
     */
    public function setDataSourceOptions($dataSourceOptions)
    {
        $this->dataSourceOptions = $dataSourceOptions;
    }




}