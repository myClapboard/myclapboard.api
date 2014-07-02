<?php

/**
 * (c) benatespina <benatespina@gmail.com>
 *
 * This file belongs to Filmboot.
 * The source code of application includes a LICENSE file
 * with all information about license.
 */

namespace Filmbot\MovieBundle\Command;

use Filmbot\MovieBundle\Entity\MovieTranslation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;

/**
 * Class LoadMoviesCommand.
 *
 * @package Filmbot\MovieBundle\Command
 */
class LoadMoviesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('filmbot:movie:load:movie')
            ->setDescription('Loads movie from yml file')
            ->addArgument('file', InputArgument::REQUIRED, 'Path of file to be loaded')
            ->setHelp(
                'The <info>filmbot:movie:load:movie</info> command loads content of file passed by argument 
<info>php app/console filmbot:movie:load:movie <path-of-file></info>'
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Loading movies");
        $this->loadMovies($input->getArgument('file'));
        $output->writeln("Movies loaded successfully");
    }

    /**
     * Loads all the movies from fixtures app folder
     *
     * @param string $path The path of file
     *
     * @return void
     */
    public function loadMovies($path)
    {
        $yaml = new Parser();

        $fixtures = $yaml->parse(file_get_contents($path));

        $container = $this->getContainer();
        $doctrine = $container->get('doctrine');
        $manager = $doctrine->getManager();
        foreach ($fixtures as $values) {
            $movie = $container->get('filmbot_movie.manager.movie')->create();
            $country = $doctrine->getRepository('JJsGeonamesBundle:City')
                ->findOneBy(array('geonameIdentifier' => $values['country']));

            $movie->setTitle($values['title']['en']);
            $this->addTranslation($movie, $values, 'title');

            $movie->setDuration($values['duration']);
            $movie->setYear($values['year']);
            $movie->setCountry($country);

            $movie->setStoryline($values['storyline']['en']);
            $this->addTranslation($movie, $values, 'storyline');


            $movie->setProducer($values['producer']);

            $this->addArtists($movie, $values['cast'], 'addActor', 'actor');
            $this->addArtists($movie, $values['directors'], 'addDirector', 'director');
            $this->addArtists($movie, $values['writers'], 'addWriter', 'writer');

            foreach ($values['genres'] as $nameOfGenre) {
                $genre = $container->get('filmbot_movie.manager.genre')
                    ->findOneByName(array('name' => $nameOfGenre));
                $movie->addGenre($genre);
            }

            $manager->persist($movie);
        }

        $manager->flush();
    }

    /**
     * Finds artist and creates the role with the role given, adding this role into movie object.
     *
     * @param \Filmbot\MovieBundle\Model\MovieInterface $movie  The movie object
     * @param array                                     $array  The values of yaml file
     * @param string                                    $method The name of the function
     * @param string                                    $class  The class
     *
     * @return void
     */
    private function addArtists($movie, $array, $method, $class)
    {
        $container = $this->getContainer();

        foreach ($array as $artist) {
            $artist = $container->get('filmbot_artist.manager.artist')
                ->findOneByFullName($artist['firstName'], $artist['lastName']);

            if ($artist) {
                $role = $container->get('filmbot_artist.manager.' . $class)->create();

                $role->setArtist($artist);
                $role->setMovie($movie);

                $movie->$method($role);
            }
        }
    }

    /**
     * Adds translations into Movie with the field given.
     *
     * @param \Filmbot\MovieBundle\Model\MovieInterface $movie The movie object
     * @param array                                     $array The values of yaml file
     * @param string                                    $field The name of field which is translatable
     *
     * @return void
     */
    private function addTranslation($movie, $array, $field)
    {
        if ($array[$field]['es']) {
            $translation = new MovieTranslation('es', $field, $array[$field]['es']);
            $movie->addTranslation($translation);
        }
    }
}
