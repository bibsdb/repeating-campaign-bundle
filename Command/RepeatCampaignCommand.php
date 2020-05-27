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
use Os2Display\CampaignBundle\Entity\Campaign;
use \Datetime;

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
      ->setDescription('Schedule campaign to run every day. Provide startdate and time plus duration. Distinguish between weekdays and weekends.)
      ->addArgument(
        'title',
        InputArgument::REQUIRED,
        'Campaign Title  - is only displayed in backend.'
      )
      ->addArgument(
          'startdatetime',
          InputArgument::REQUIRED,
          'When to start? Format is d-m-Y G:i. Example: 21-01-2020 21:45.'
      )
      ->addArgument(
          'duration',
          InputArgument::REQUIRED,
          'Duration in minutes. Input a number.'
      )
      ->addArgument(
          'weekday_or_weekend',
          InputArgument::REQUIRED,
          'Apply to weekdays or weekends? Enter one of the two: weekday or weekend.'
      )
      ->addArgument(
          'enddatetime',
          InputArgument::REQUIRED,
          'When to stop the repeating campaign? Format is d-m-Y G:i. This is an example: 21-02-2020 21:45.'
      )
      ->addArgument(
          'channal',
          InputArgument::REQUIRED,
          'Enter the id of the channal to be run.'
      )
      ->addArgument(
          'screengroup',
          InputArgument::REQUIRED,
          'Enter the id of the screen group the campaign should be run on.'
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $date = DateTime::createFromFormat('d-m-Y G:i', $input->getArgument('startdatetime'));
    $enddate = DateTime::createFromFormat('d-m-Y G:i', $input->getArgument('enddatetime'));
    $duration = $input->getArgument('enddatetime');


    while ($date < $enddate) {
      // Is $date weekday or weekend?
      $weekday_or_weekend = ($date->format('N') >= 6 ? "weekend" : "weekday"); 
    
      if ($weekday_or_weekend == $input->getArgument('weekday_or_weekend')) {
        // Create campaign
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getEntityManager('default');
        $campaign = new Campaign();

        // Title
        $campaign->setTitle($input->getArgument('title'));

        // SceduleFrom
        $campaign->setSceduleFrom($date);

        // ScheduleTo
        $sceduleTo = (clone $date).add(new DateInterval('PT' . $duration . 'M'));
        $campaign->setSceduleTo($sceduleTo);

        // User
        $campaign->setUser(NULL);

        // Description
        $campaign->setDescription("Repeated campaign");

        // Channals
        $campaign->setChannals = new ArrayCollection([$input->getArgument('channal')]);

        // Screens
        $campaign->setScreens = new ArrayCollection([]);

        // ScreenGroups
        $campaign->setScreenGroups = new ArrayCollection([$input->getArgument('screengroup')]);

        // Save campaign
        $em->persist($campaign);
        $em->flush();
        $output->writeln('Created campaign from ' . $date->format('d-m-Y G:i') . ' to ' . $sceduleTo->format('d-m-Y G:i'));
      }

      // Prepare next iteration
      $date->modify('+1 day');
    }
  }
}