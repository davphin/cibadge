<?php

namespace dnelson\cibadge;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PUGX\Poser\Render\SvgRender;
use PUGX\Poser\Poser;

abstract class Base extends Command {

  protected function configure() {
    $this
      ->addArgument('infile', InputArgument::REQUIRED)
      ->addOption('output', NULL, InputOption::VALUE_OPTIONAL, 'Optional name of output file.', sprintf('%s.svg', $this->getName()))
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $fileSystem = new Filesystem();

    if ($fileSystem->exists($input->getArgument('infile'))) {
      $output->writeln(sprintf('Parsing %s', $input->getArgument('infile')));
      $fileSystem->dumpFile($input->getOption('output'), $this->parseInput($input->getArgument('infile')));
      $output->writeln(sprintf('Writing results to %s', $input->getOption('output')));
    }
    else {
      throw new \InvalidArgumentException(sprintf('%s does not exist', $input->getArgument('infile')));
    }
  }

  protected function parseInput($file_path) {}

}