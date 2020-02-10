# doctrine-bundle

## Usage
- With dependency Injection (thanks to `services.yml` or `autowiring`) : just use `SymfonyExtra\DoctrineBundle\Manager\BulkEntityManager` instead of `Doctrine\ORM\EntityManager`.

## Support
- DoctrineBundle supports :
   - BulkInsert : create one insert query per Entity
   
> Note : DoctrineBundle is in development, and not check if database support bulk insert.