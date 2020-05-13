<?php

/**
 * PHP version 7.4
 * src/Entity/Person.php
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
 *     name="person",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name = "Person_name_uindex",
 *              columns = {"name"}
 *          )
 *      }
 * )
 */
class Person extends Element implements JsonSerializable
{
    /**
     * @ORM\ManyToMany(
     *     targetEntity="Entity",
     *     mappedBy="persons"
     *     )
     * @ORM\OrderBy({ "id" = "ASC" })
     */
    protected Collection $entities;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Product",
     *     mappedBy="persons"
     *     )
     * @ORM\OrderBy({ "id" = "ASC" })
     */
    protected Collection $products;

    /**
     * Person constructor.
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
        $this->entities = new ArrayCollection();
        $this->products = new ArrayCollection();
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
     * @return Person
     */
    public function addEntity(Entity $entity): Person
    {
        $entity->addPerson($this);
        $this->entities->add($entity);
        return $this;
    }

    /**
     * @param Entity $entity
     *
     * @return Person|null Person if this collection contained the specified entity, null otherwise.
     */
    public function removeEntity(Entity $entity): ?Person
    {
        $this->entities->removeElement($entity);
        return (null !== $entity->removePerson($this))
            ? $this
            : null;
    }

    // Products

    /**
     * @return Product[]
     */
    public function getProducts(): array
    {
        return $this->products->getValues();
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function containsProduct(Product $product): bool
    {
        return $this->products->contains($product);
    }

    /**
     * @param Product $product
     *
     * @return Person
     */
    public function addProduct(Product $product): Person
    {
        $this->products->add($product);
        $product->addPerson($this);
        return $this;
    }

    /**
     * @param Product $product
     *
     * @return Person|null Person if this collection contained the specified product, null otherwise.
     */
    public function removeProduct(Product $product): ?Person
    {
        $this->products->removeElement($product);
        return (null !== $product->removePerson($this))
            ? $this
            : null;
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
            'products="' . $this->getCodesTxt($this->getProducts()) . '", ' .
            'entities="' . $this->getCodesTxt($this->getEntities()) .
            '")]';
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        $data['products'] = $this->getProducts() ? $this->getCodes($this->getProducts()) : null;
        $data['entities'] = $this->getEntities() ? $this->getCodes($this->getEntities()) : null;

        return ['person' => $data];
    }
}
