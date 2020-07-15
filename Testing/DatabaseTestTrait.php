<?php

/*
 * This file is part of the Omed project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Omed\Component\ORM\Testing;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Persistence\ObjectManager;

trait DatabaseTestTrait
{
    private static $entityPaths = [];

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var ORMPurger
     */
    private $purger;

    protected function addEntityPath($path)
    {
        if (!\in_array($path, static::$entityPaths, true)) {
            static::$entityPaths[] = $path;
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    private function bootstrapDatabase()
    {
        $paths = static::$entityPaths;
        $isDevMode = true;

        // the connection configuration
        $dbParams = [
            'url' => 'sqlite:///:memory:',
        ];

        $config = Setup::createAnnotationMetadataConfiguration(
            $paths,
            $isDevMode,
            null,
            null,
            false
        );

        $driverImpl = $config->newDefaultAnnotationDriver($paths, false);
        $config->setMetadataDriverImpl($driverImpl);

        $em = EntityManager::create($dbParams, $config);
        //$purger = new ORMPurger($em);
        //$purger->purge();
        $this->createSchema($em);
        $this->em = $em;
    }

    private function createSchema(EntityManager $em)
    {
        $classes = $em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($em);

        $queries = $tool->getCreateSchemaSql($classes);
        $connection = $em->getConnection();
        foreach ($queries as $query) {
            $connection->exec($query);
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     *
     * @return ObjectManager
     */
    protected function getEntityManager()
    {
        if (!$this->em instanceof ObjectManager) {
            $this->bootstrapDatabase();
        }

        return $this->em;
    }
}
