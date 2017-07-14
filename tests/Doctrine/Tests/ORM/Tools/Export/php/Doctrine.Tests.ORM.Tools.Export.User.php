<?php

use Doctrine\ORM\Events;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping;
use Doctrine\Tests\ORM\Tools\Export;
use Doctrine\Tests\ORM\Tools\Export\AddressListener;
use Doctrine\Tests\ORM\Tools\Export\GroupListener;
use Doctrine\Tests\ORM\Tools\Export\UserListener;

$tableMetadata = new Mapping\TableMetadata();
$tableMetadata->setName('cms_users');
$tableMetadata->addOption('engine', 'MyISAM');
$tableMetadata->addOption('foo', ['bar' => 'baz']);

$metadata->setTable($tableMetadata);
$metadata->setIdGeneratorType(Mapping\GeneratorType::AUTO);
$metadata->setInheritanceType(Mapping\InheritanceType::NONE);
$metadata->setChangeTrackingPolicy(Mapping\ChangeTrackingPolicy::DEFERRED_IMPLICIT);

$metadata->addLifecycleCallback('doStuffOnPrePersist', Events::prePersist);
$metadata->addLifecycleCallback('doOtherStuffOnPrePersistToo', Events::prePersist);
$metadata->addLifecycleCallback('doStuffOnPostPersist', Events::postPersist);

// Property: "id"
$fieldMetadata = new Mapping\FieldMetadata('id');

$fieldMetadata->setType(Type::getType('integer'));
$fieldMetadata->setPrimaryKey(true);

$metadata->addProperty($fieldMetadata);

// Property: "name"
$fieldMetadata = new Mapping\FieldMetadata('name');

$fieldMetadata->setType(Type::getType('string'));
$fieldMetadata->setLength(50);
$fieldMetadata->setColumnName('name');
$fieldMetadata->setNullable(true);
$fieldMetadata->setUnique(true);

$metadata->addProperty($fieldMetadata);

// Property: "email"
$fieldMetadata = new Mapping\FieldMetadata('email');

$fieldMetadata->setType(Type::getType('string'));
$fieldMetadata->setColumnName('user_email');
$fieldMetadata->setColumnDefinition('CHAR(32) NOT NULL');

$metadata->addProperty($fieldMetadata);

// Property: "age"
$fieldMetadata = new Mapping\FieldMetadata('age');

$fieldMetadata->setType(Type::getType('integer'));
$fieldMetadata->setOptions(['unsigned' => true]);

$metadata->addProperty($fieldMetadata);

// Property: "mainGroup"
$association = new Mapping\ManyToOneAssociationMetadata('mainGroup');

$association->setTargetEntity(Export\Group::class);

$metadata->addProperty($association);

// Property: "address"
$joinColumns = [];

$joinColumn = new Mapping\JoinColumnMetadata();

$joinColumn->setColumnName("address_id");
$joinColumn->setReferencedColumnName("id");
$joinColumn->setOnDelete("CASCADE");

$joinColumns[] = $joinColumn;

$association = new Mapping\OneToOneAssociationMetadata('address');

$association->setJoinColumns($joinColumns);
$association->setTargetEntity(Export\Address::class);
$association->setInversedBy('user');
$association->setCascade(['persist']);
$association->setFetchMode(Mapping\FetchMode::EAGER);
$association->setOrphanRemoval(true);

$metadata->addProperty($association);

// Property: "cart"
$association = new Mapping\OneToOneAssociationMetadata('cart');

$association->setTargetEntity(Export\Cart::class);
$association->setMappedBy('user');
$association->setCascade(['persist']);
$association->setFetchMode(Mapping\FetchMode::EAGER);
$association->setOrphanRemoval(false);

$metadata->addProperty($association);

// Property: "phonenumbers"
$association = new Mapping\OneToManyAssociationMetadata('phonenumbers');

$association->setTargetEntity(Export\Phonenumber::class);
$association->setMappedBy('user');
$association->setCascade(['persist', 'merge']);
$association->setFetchMode(Mapping\FetchMode::LAZY);
$association->setOrphanRemoval(true);
$association->setOrderBy(['number' => 'ASC']);

$metadata->addProperty($association);

// Property: "groups"
$joinTable = new Mapping\JoinTableMetadata();
$joinTable->setName('cms_users_groups');

$joinColumn = new Mapping\JoinColumnMetadata();
$joinColumn->setColumnName("user_id");
$joinColumn->setReferencedColumnName("id");

$joinTable->addJoinColumn($joinColumn);

$inverseJoinColumns = [];

$joinColumn = new Mapping\JoinColumnMetadata();
$joinColumn->setColumnName("group_id");
$joinColumn->setReferencedColumnName("id");
$joinColumn->setColumnDefinition("INT NULL");

$joinTable->addInverseJoinColumn($joinColumn);

$association = new Mapping\ManyToManyAssociationMetadata('groups');

$association->setJoinTable($joinTable);
$association->setTargetEntity(Export\Group::class);
$association->setCascade(['remove', 'persist', 'refresh', 'merge', 'detach']);
$association->setFetchMode(Mapping\FetchMode::EXTRA_LAZY);

$metadata->addProperty($association);

$metadata->addEntityListener(Events::prePersist, UserListener::class, 'customPrePersist');
$metadata->addEntityListener(Events::postPersist, UserListener::class, 'customPostPersist');
$metadata->addEntityListener(Events::prePersist, GroupListener::class, 'prePersist');
$metadata->addEntityListener(Events::postPersist, AddressListener::class, 'customPostPersist');
