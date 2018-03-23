<?php
/**
 * Created by PhpStorm.
 * User: Ispolin
 * Date: 18.03.2018
 * Time: 13:11
 */


namespace Ispolin08\ClerkBundle\CheckProvider;

use Ispolin08\ClerkBundle\DataSource\DataSourceInterface;
use Monolog\Handler\StreamHandler;

interface CheckProviderInterface
{

    function provideChecks();

}