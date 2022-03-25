<?php

namespace App\Doctrine\Listener;

use App\Doctrine\Model\HasInlinedProperties;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Listen for changes on data object inlined as JSON (or something else) in an
 * entity.
 *
 * This listener snapshots the <entity>.<configuration> and detect if the
 * configuration has changed between the first time it has been loaded an the
 * flush, and also other extra flush
 *
 * The 4 listeners are mandatory
 *  * postLoad|postPersist to save the initial value;
 *  * preFlush to be able to schedule an update;
 *  * onFlush to add the changes to the UOW. This can not be done in the
 *    preFlush since doctrine override any changes if some already exists;
 *  * onClear to clear everything
 */
class InlinedPropertiesListener implements EventSubscriber, ResetInterface
{
    private \SplObjectStorage $entitiesWithInitialValues;
    private array $changes;

    public function __construct()
    {
        $this->reset();
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $this->watchObject($event);
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $this->watchObject($event);
    }

    public function preFlush(PreFlushEventArgs $event)
    {
        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($this->entitiesWithInitialValues as $entity) {
            $data = $this->entitiesWithInitialValues[$entity];
            foreach ($entity->getInlinedProperties() as $property) {
                [$oldValue, $oldSnapshot] = $data[$property];
                [$newValue, $newSnapshot] = $this->extractValueAndSnapshot($em, $entity, $property);
                // We compare only the value that will be inserted in the database
                if ($oldSnapshot !== $newSnapshot) {
                    $uow->scheduleForUpdate($entity);
                    $this->changes[] = [
                        $entity, $property, $oldValue, $newValue, $newSnapshot,
                    ];
                }
            }
        }
    }

    public function onFlush(OnFlushEventArgs $event)
    {
        $uow = $event->getEntityManager()->getUnitOfWork();

        foreach ($this->changes as [$entity, $property, $oldValue, $newValue, $newSnapshot]) {
            $uow->propertyChanged($entity, $property, $oldValue, $newValue);

            // We update the initial value with the last value
            $data = $this->entitiesWithInitialValues[$entity];
            $data[$property] = [$newValue, $newSnapshot];
            $this->entitiesWithInitialValues[$entity] = $data;
        }

        $this->changes = [];
    }

    public function onClear()
    {
        $this->reset();
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postLoad,
            Events::onFlush,
            Events::preFlush,
            Events::onClear,
        ];
    }

    public function reset()
    {
        $this->entitiesWithInitialValues = new \SplObjectStorage();
        $this->changes = [];
    }

    private function watchObject(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof HasInlinedProperties) {
            return;
        }

        $em = $event->getEntityManager();

        foreach ($entity->getInlinedProperties() as $property) {
            $data = [];
            if ($this->entitiesWithInitialValues->contains($entity)) {
                $data = $this->entitiesWithInitialValues[$entity];
            }
            [$value, $snapshot] = $this->extractValueAndSnapshot($em, $entity, $property);
            $data[$property] = [$value, $snapshot];
            $this->entitiesWithInitialValues[$entity] = $data;
        }
    }

    private function extractValueAndSnapshot(EntityManagerInterface $em, HasInlinedProperties $entity, string $property): array
    {
        $metadata = $em->getClassMetadata($entity::class);

        $value = $metadata->getFieldValue($entity, $property);

        $dbalType = $metadata->getFieldMapping($property)['type'];
        $type = Type::getType($dbalType);
        $snapshot = $type->convertToDatabaseValue($value, $em->getConnection()->getDatabasePlatform());

        return [$value, $snapshot];
    }
}
