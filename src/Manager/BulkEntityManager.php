<?php

namespace SymfonyExtra\DoctrineBundle\Manager;

use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use SymfonyExtra\DoctrineBundle\Doctrine\BulkUnitOfWork;

class BulkEntityManager extends EntityManagerDecorator
{
    protected $unitOfWork;

    public function __construct(EntityManagerInterface $wrapped)
    {
        parent::__construct($wrapped);

        $this->unitOfWork = new BulkUnitOfWork($this);
    }

    public function getUnitOfWork()
    {
        return $this->unitOfWork;
    }

    public function flush($entity = null)
    {
        $this->unitOfWork->commit($entity);
    }

    public function persist($entity)
    {
        $this->unitOfWork->persist($entity);
    }

    public function commit()
    {
        return $this->unitOfWork->commit();
    }

}