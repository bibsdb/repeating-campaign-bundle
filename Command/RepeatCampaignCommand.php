<?php
/**
 * @file
 * This file is a part of the Bibsdb RepeatingCampaignBundle.
 *
 */

namespace Bibsdb\RepeatingCampaignBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RepeatCampaignCommand
 *
 * @package Bibsdb\RepeatingCampaignBundle\Command
 */
class RepeatCampaignCommand extends ContainerAwareCommand
{
    /**
   * Configure the command
   */
    protected function configure()
    {
        $this
            ->setName('bibsdb:campaign:schedule')
            ->setDescription('Schedule campaign to run every day. Provide starttime and duration. Distinguish between weekdays and weekends.')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Who do you want to greet?'
            )
            ->addOption(
                'yell',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will yell in uppercase letters'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        if ($name) {
            $text = 'Hello '.$name;
        } else {
            $text = 'Hello';
        }

        if ($input->getOption('yell')) {
            $text = strtoupper($text);
        }

        $output->writeln($text);
    }
}