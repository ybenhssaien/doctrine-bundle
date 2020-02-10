<?php

namespace SymfonyExtra\DoctrineBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Persisters\Entity\BasicEntityPersister;
use Doctrine\ORM\UnitOfWork;
use SymfonyExtra\DoctrineBundle\Persister\BasicBulkEntityPersister;

class BulkUnitOfWork extends UnitOfWork
{

    /**
     * The EntityManager that "owns" this UnitOfWork instance.
     *
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct($em);
    }

    /**
     * The entity persister instances used to persist entity instances.
     *
     * @var array
     */
    protected $persisters = [];

    public function getEntityPersister($entityName)
    {
        $persister = parent::getEntityPersister($entityName);

        if ($persister instanceof BasicEntityPersister) {
            $class = $this->em->getClassMetadata($entityName);

            $persister = new BasicBulkEntityPersister($this->em, $class);
        }

        return $this->persisters[$entityName] = $persister;
    }

}