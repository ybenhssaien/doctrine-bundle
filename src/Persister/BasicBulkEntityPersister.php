<?php

namespace SymfonyExtra\DoctrineBundle\Persister;

use Doctrine\ORM\Persisters\Entity\BasicEntityPersister;

class BasicBulkEntityPersister extends BasicEntityPersister
{

    /**
     * The INSERT SQL statement used for entities handled by this persister.
     * This SQL is only generated once per request, if at all.
     *
     * @var string
     */
    protected $insertSql;

    /**
     * {@inheritdoc}
     */
    public function executeInserts()
    {
        if ( ! $this->queuedInserts) {
            return [];
        }

        $postInsertIds  = [];
        $idGenerator    = $this->class->idGenerator;
        $isPostInsertId = $idGenerator->isPostInsertGenerator();
        $identifier     = current($this->class->getIdentifierColumnNames());

        $stmt       = $this->conn->prepare($this->getInsertSQL(count($this->queuedInserts)));
        $tableName  = $this->class->getTableName();
        $paramIndex = 1;

        if ($isPostInsertId && $identifier) {
            /* Get the Max Id */
            $firstId = $this->em
                ->createQueryBuilder()
                ->from($this->class->getName(), 'e')
                ->select(sprintf('MAX(e.%s) as idMax', $identifier))
                ->getQuery()
                ->getSingleResult()['idMax'];
        }

        foreach ($this->queuedInserts as $entity) {
            $insertData = $this->prepareInsertData($entity);

            if (isset($insertData[$tableName])) {
                foreach ($insertData[$tableName] as $column => $value) {
                    $stmt->bindValue($paramIndex++, $value, $this->columnTypes[$column]);
                }
            }

            if ($isPostInsertId) {
                $generatedId = ++$firstId;

                $postInsertIds[] = [
                    'generatedId' => $generatedId,
                    'entity' => $entity,
                ];
                $id = [
                    $this->class->identifier[0] => $generatedId
                ];

                $insertedId = $generatedId;
            } else {
                $id = $this->class->getIdentifierValues($entity);
                $insertedId = current($id);
            }

            if ($this->class->isVersioned) {
                $this->assignDefaultVersionValue($entity, $id);
            }

            $this->class->setFieldValue($entity, $identifier, $insertedId);
        }

        $stmt->execute();

        $stmt->closeCursor();
        $this->queuedInserts = [];

        return $postInsertIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getInsertSQL($count = 1)
    {
        if ($this->insertSql !== null) {
            return $this->insertSql;
        }

        $columns   = $this->getInsertColumnList();
        $tableName = $this->quoteStrategy->getTableName($this->class, $this->platform);

        if (empty($columns)) {
            $identityColumn  = $this->quoteStrategy->getColumnName($this->class->identifier[0], $this->class, $this->platform);
            $this->insertSql = $this->platform->getEmptyIdentityInsertSQL($tableName, $identityColumn);

            return $this->insertSql;
        }

        $values  = [];
        $columns = array_unique($columns);

        foreach ($columns as $column) {
            $placeholder = '?';

            if (isset($this->class->fieldNames[$column])
                && isset($this->columnTypes[$this->class->fieldNames[$column]])
                && isset($this->class->fieldMappings[$this->class->fieldNames[$column]]['requireSQLConversion'])) {
                $type        = Type::getType($this->columnTypes[$this->class->fieldNames[$column]]);
                $placeholder = $type->convertToDatabaseValueSQL('?', $this->platform);
            }

            $values[] = $placeholder;
        }

        $columns = implode(', ', $columns);
        $values  = implode(', ', $values);

        $bulkValues = array_fill(0, $count, sprintf('(%s)', $values));
        $this->insertSql = sprintf('INSERT INTO %s (%s) VALUES %s', $tableName, $columns, implode(', ', $bulkValues));

        return $this->insertSql;
    }

}