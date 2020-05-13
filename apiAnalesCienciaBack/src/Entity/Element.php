<?php

/**
 * PHP version 7.4
 * src/Entity/Element.php
 *
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://www.etsisi.upm.es/ ETS de Ingeniería de Sistemas Informáticos
 */

namespace TDW\ACiencia\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Class Element
 */
abstract class Element implements JsonSerializable
{
    /**
     * @ORM\Column(
     *     name="id",
     *     type="integer",
     *     nullable=false
     *     )
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\Column(
     *     name="name",
     *     type="string",
     *     length=80,
     *     unique=true,
     *     nullable=false
     *     )
     */
    protected string $name;

    /**
     * @ORM\Column(
     *     name="birthdate",
     *     type="datetime",
     *     nullable=true
     *     )
     */
    protected ?DateTime $birthDate = null;

    /**
     * @ORM\Column(
     *     name="deathdate",
     *     type="datetime",
     *     nullable=true
     *     )
     */
    protected ?DateTime $deathDate = null;

    /**
     * @ORM\Column(
     *     name="image_url",
     *     type="string",
     *     length=2047,
     *     nullable=true
     *     )
     */
    protected ?string $imageUrl = null;

    /**
     * @ORM\Column(
     *     name="wiki_url",
     *     type="string",
     *     length=2047,
     *     nullable=true
     *     )
     */
    protected ?string $wikiUrl = null;

    /**
     * Element constructor.
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
        $this->id = 0;
        $this->name = $name;
        $this->birthDate = $birthDate;
        $this->deathDate = $deathDate;
        $this->imageUrl = $imageUrl;
        $this->wikiUrl = $wikiUrl;
    }

    /**
     * @return int
     */
    final public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Element
     */
    final public function setName(string $name): Element
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    final public function getBirthDate(): ?DateTime
    {
        return $this->birthDate;
    }

    /**
     * @param DateTime|null $birthDate
     * @return Element
     */
    final public function setBirthDate(?DateTime $birthDate): Element
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    final public function getDeathDate(): ?DateTime
    {
        return $this->deathDate;
    }

    /**
     * @param DateTime|null $deathDate
     * @return Element
     */
    final public function setDeathDate(?DateTime $deathDate): Element
    {
        $this->deathDate = $deathDate;
        return $this;
    }

    /**
     * @return string|null
     */
    final public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * @param string|null $imageUrl
     * @return Element
     */
    final public function setImageUrl(?string $imageUrl): Element
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    final public function getWikiUrl(): ?string
    {
        return $this->wikiUrl;
    }

    /**
     * @param string|null $wikiUrl
     * @return Element
     */
    final public function setWikiUrl(?string $wikiUrl): Element
    {
        $this->wikiUrl = $wikiUrl;
        return $this;
    }

    /**
     * @param array $collection
     *
     * @return array Ids in collection
     */
    protected function getCodes(array $collection): array
    {
        return (empty($collection))
            ? []
            : array_map(
                fn(Object $object) => $object->getId(),
                $collection
            );
    }

    /**
     * @param Element[] $collection
     *
     * @return string String representation of Collection
     */
    protected function getCodesTxt(array $collection): string
    {
        $codes = $this->getCodes($collection);
        return empty($codes)
            ? '[]'
            : '[' . implode(', ', $codes) . ']';
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString()
    {
        $birthdate = (null !== $this->getBirthDate())
            ? $this->getBirthDate()->format('"Y-m-d"')
            : '"null"';
        $deathdate = (null !== $this->getDeathDate())
            ? $this->getDeathDate()->format('"Y-m-d"')
            : '"null"';
        return '[' . basename(get_class($this)) . ' ' .
            '(id=' . $this->getId() . ', ' .
            'name="' . $this->getName() . '", ' .
            'birthDate=' . $birthdate . ' ' .
            'deathDate=' . $deathdate . ' ';
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'birthDate' => ($this->getBirthDate()) ? $this->getBirthDate()->format('Y-m-d') : null,
            'deathDate' => ($this->getDeathDate()) ? $this->getDeathDate()->format('Y-m-d') : null,
            'imageUrl'  => $this->getImageUrl() ?? null,
            'wikiUrl'  => $this->getWikiUrl() ?? null,
        ];
    }
}
