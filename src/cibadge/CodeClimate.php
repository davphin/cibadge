<?php

namespace dnelson\cibadge;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PUGX\Poser\Render\SvgRender;
use PUGX\Poser\Poser;

class CodeClimate extends Base {

  protected function configure() {
    $this
      ->setName('codeclimate')
      ->setDescription('Just a tester')
      ->setHelp('Help for a tester')
    ;

    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $fileSystem = new Filesystem();

    if ($fileSystem->exists($input->getArgument('infile'))) {
      $output->writeln('Parsing ' . $input->getArgument('infile') . '...');
      $fileSystem->dumpFile($input->getOption('output'), $this->parseInput($input->getArgument('infile')));
      $output->writeln('Writing results to ' . $input->getOption('output'));
    }
    else {
      throw new \InvalidArgumentException(sprintf('%s does not exist', $input->getArgument('infile')));
    }
  }

  protected function parseInput($file_path) {
    $total_score = 0;
    $lines = 0;
    $files = array();

    $handle = file_get_contents($file_path, "r");


    if ($array = json_decode($handle)) {
      foreach ($array as $obj) {
        // Process the line read.
        if (isset($obj->remediation_points)) {
          if (isset($files[$obj->location->path]['total_score'])) {
            $files[$obj->location->path]['total_score'] += $obj->remediation_points;
            $files[$obj->location->path]['error_count']++;
          }
          else {
            $files[$obj->location->path]['total_score'] = $obj->remediation_points;
            $files[$obj->location->path]['error_count'] = 1;
            $files[$obj->location->path]['path'] = $obj->location->path;
            $lines++;
          }

          $total_score += $obj->remediation_points;
        }
      }
    }
    else {
      throw new \InvalidArgumentException(sprintf('%s is not valid json.', $file_path));
    }

    $total = $total_score / $lines;

    switch ($total) {
      case $total < 2000000:
        $grade = 'A';
        $color = 'brightgreen';
        break;

      case $total < 4000000:
        $grade = 'B';
        $color = 'green';
        break;

      case $total < 8000000:
        $grade = 'C';
        $color = 'yellowgreen';
        break;

      case $total < 16000000:
        $grade = 'D';
        $color = 'orange';
        break;

      default:
        $grade = 'F';
        $color = 'red';
    }

//    print $total . PHP_EOL;
//    print $total_score . PHP_EOL;
//    print $lines . PHP_EOL;

    $render = new SvgRender();
    $poser = new Poser(array($render));

    return $poser->generate('Maintainability grade', $grade, $color, 'plastic');
  }
}