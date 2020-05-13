<?php

/**
 * PHP version 7.4
 * src/Entity/Product.php
 */

namespace TDW\ACiencia\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="product",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name = "Product_name_uindex",
 *              columns = {"name"}
 *          )
 *      }
 * )
 */
class Product extends Element implements JsonSerializable
{
    /**
     * @ORM\ManyToMany(
     *     targetEntity="Person",
     *     inversedBy="products"
     *     )
     * @ORM\JoinTable(
     *   name="person_contributes_product",
     *   joinColumns={
     *     @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     *   }
     * )
     */
    protected Collection $persons;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Entity",
     *     inversedBy="products"
     *     )
     * @ORM\JoinTable(
     *   name="entity_contributes_product",
     *   joinColumns={
     *     @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
     *   }
     * )
     */
    protected Collection $entities;

    /**
     * Entity constructor.
     * @param string $name
     * @param DateTime|null $birthDate
     * @param DateTime|null $deathDate
     * @param string|null $imageUrl
     * @param string|null $wikiUrl
     */
    public function __construct(
        string $name,
        ?DateTime $birthDate = null,
        ?DateTime $deathDate = null,
        ?string $imageUrl = null,
        ?string $wikiUrl = null
    ) {
        parent::__construct($name, $birthDate, $deathDate, $imageUrl, $wikiUrl);
        $this->persons = new ArrayCollection();
        $this->entities = new ArrayCollection();
    }

    // Entities

    /**
     * @return Entity[]
     */
    public function getEntities(): array
    {
        return $this->entities->getValues();
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function containsEntity(Entity $entity): bool
    {
        return $this->entities->contains($entity);
    }

    /**
     * @param Entity $entity
     *
     * @return Product
     */
    public function addEntity(Entity $entity): Product
    {
        if ($this->containsEntity($entity)) {
            return $this;
        }

        $this->entities->add($entity);
        return $this;
    }

    /**
     * @param Entity $entity
     *
     * @return Product|null Product if this collection contained the specified entity, null otherwise.
     */
    public function removeEntity(Entity $entity): ?Product
    {
        if (!$this->containsEntity($entity)) {
            return null;
        }

        $this->entities->removeElement($entity);
        return $this;
    }

    // Persons

    /**
     * @return Person[]
     */
    public function getPersons(): array
    {
        return $this->persons->getValues();
    }

    /**
     * @param Person $person
     * @return bool
     */
    public function containsPerson(Person $person): bool
    {
        return $this->persons->contains($person);
    }

    /**
     * @param Person $person
     *
     * @return Product
     */
    public function addPerson(Person $person): Product
    {
        if ($this->containsPerson($person)) {
            return $this;
        }

        $this->persons->add($person);
        return $this;
    }

    /**
     * @param Person $person
     *
     * @return Product|null Product if this collection contained the specified person, null otherwise.
     */
    public function removePerson(Person $person): ?Product
    {
        if (!$this->containsPerson($person)) {
            return null;
        }

        $this->persons->removeElement($person);
        return $this;
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString()
    {
        return parent::__toString() .
            'persons="' . $this->getCodesTxt($this->getPersons()) . '", ' .
            'entities="' . $this->getCodesTxt($this->getEntities()) .
            '")]';
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        $data['persons'] = $this->getPersons() ? $this->getCodes($this->getPersons()) : null;
        $data['entities'] = $this->getEntities() ? $this->getCodes($this->getEntities()) : null;

        return ['product' => $data];
    }
}
