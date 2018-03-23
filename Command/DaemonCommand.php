<?php

namespace Ispolin08\ClerkBundle\Command;

use Ispolin08\ClerkBundle\Service\ClerkService;
use Ispolin08\Monolog\Handler\TelegramHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Serializer;


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
            ->setName('ispolin08:clerk:process')
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
        $checks = $input->getArgument('checks');
        $io = new SymfonyStyle($input, $output);

        $io->title('Process checks');

        $io->progressStart(count($checks));

        foreach ($checks as $checkId) {

            $this->clerkService->process($checkId);

            $io->progressAdvance();
        }

        $io->success('Finish');

    }
}