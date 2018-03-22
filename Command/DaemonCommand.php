<?php

namespace Ispolin08\ClerkBundle\Command;

use Ispolin08\ClerkBundle\Service\ClerkService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class DaemonCommand extends ContainerAwareCommand
{
    /** @var string */
    private $env;

    /** @var ClerkService */
    private $clerkService;

    /** @inheritdoc */
    protected function configure()
    {
        $this
            ->setName('ispolin08:clerk:run')
            ->setDescription('Process checks')
            ->addArgument(
                'checks',
                InputArgument::IS_ARRAY
            )
            ->setHelp('This command allows you to run checks');
    }

    /** {@inheritdoc} */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->clerkService = $this->getContainer()->get(ClerkService::class);
        $this->env = $this->getContainer()->getParameter('kernel.environment');

    }

    /** {@inheritdoc} */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $log = new \Monolog\Logger('telegram_channel');

        $handler = new \Mero\Monolog\Handler\TelegramHandler(
            '569191535:AAE0zEME__2XCqq6DxoHHBMbIjmhTxgLVic',
            226628487,
            \Monolog\Logger::DEBUG
        );
        $handler->setFormatter(new \Monolog\Formatter\LineFormatter());
        $log->pushHandler($handler);

        $log->debug('Message log');

        sleep (2);
        foreach ($input->getArgument('checks') as $checkId) {

            $output->writeln($checkId);

            $this->clerkService->check($checkId);

            sleep(1);
        }
    }
}