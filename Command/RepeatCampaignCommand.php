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
      ->setDescription('Schedule campaign to run every day. Provide startdate and time plus duration. Distinguish between weekdays and weekends.')
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
          'channel',
          InputArgument::REQUIRED,
          'Enter the id of the channl to be run.'
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
    $doctrine = $this->getContainer()->get('doctrine');
    $em = $doctrine->getEntityManager('default');

    $date = \DateTime::createFromFormat('d-m-Y G:i', $input->getArgument('startdatetime'), new \DateTimeZone('Europe/Copenhagen'));
    $enddate = \DateTime::createFromFormat('d-m-Y G:i', $input->getArgument('enddatetime'), new \DateTimeZone('Europe/Copenhagen'));
    $duration = $input->getArgument('duration');

    while ($date < $enddate) {
      
      // Is $date weekday or weekend?
      $weekday_or_weekend = ($date->format('N') >= 6 ? "weekend" : "weekday"); 
    
      if ($weekday_or_weekend == $input->getArgument('weekday_or_weekend')) {
        // Create campaign
        $campaign = new Campaign();
        $campaign->setCreatedAt(new \DateTime());
        $campaign->setUpdatedAt(new \DateTime());

        // SceduleFrom
        $campaign->setScheduleFrom($date);

        // ScheduleTo
        $sceduleTo = (clone $date)->add(new \DateInterval('PT' . $duration . 'M'));
        $campaign->setScheduleTo($sceduleTo);

        // Title
        $campaign->setTitle($input->getArgument('title') . " " . $date->format('d-m-Y G:i'));

        // User
        $campaign->setUser(NULL);

        // Description
        $campaign->setDescription("Repeated campaign");

        // Save campaign
        $thing = $em->persist($campaign);
        $em->flush();

        // Relate campaign with screen group - ugly sql
        $sql = "INSERT INTO ik_campaign_group (campaign_id, group_id) VALUES (" . $campaign->getId() . ", " . $input->getArgument('screengroup') . ")";
        $stmt = $em->getConnection()->prepare($sql);
        $result = $stmt->execute();

        // Relate campaign with channal - ugly sql
        $sql = "INSERT INTO ik_campaign_channel (campaign_id, channel_id) VALUES (" . $campaign->getId() . ", " . $input->getArgument('channel') . ")";
        $stmt = $em->getConnection()->prepare($sql);
        $result = $stmt->execute();

        $output->writeln('Created campaign from ' . $date->format('d-m-Y G:i') . ' to ' . $sceduleTo->format('d-m-Y G:i'));
      }

      // Prepare next iteration
      $date->modify('+1 day');
    }
  }
}