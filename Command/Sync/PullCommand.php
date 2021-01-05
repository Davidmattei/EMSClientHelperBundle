<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command\Sync;

use EMS\ClientHelperBundle\Helper\Sync\PullHelper;
use EMS\CommonBundle\Command\CommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class PullCommand extends Command implements CommandInterface
{
    /** @var PullHelper */
    private $pullService;
    /** @var SymfonyStyle */
    private $style;

    protected static $defaultName = 'emsch:sync:pull';

    public function __construct(PullHelper $pullService)
    {
        parent::__construct();
        $this->pullService = $pullService;
    }

    protected function configure(): void
    {
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->style = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->style->title('Sync - pull');

        return 1;
    }
}
