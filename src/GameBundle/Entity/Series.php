<?php
namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="game_series")
 */
class Series
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column
     */
    private $name;

    /**
     * @ORM\Column
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    /**
     * @ORM\Column(type="integer", name="igdb_id", unique=true, nullable=true)
     */
    private $igdbId;

    /**
     * @ORM\Column(name="igdb_url", nullable=true)
     */
    private $igdbUrl;

    /**
     * To String
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Series
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Series
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get igdbId
     *
     * @return integer
     */
    public function getIgdbId()
    {
        return $this->igdbId;
    }

    /**
     * Set igdbId
     *
     * @param integer $igdbId
     *
     * @return Game
     */
    public function setIgdbId($igdbId)
    {
        $this->igdbId = $igdbId;

        return $this;
    }

    /**
     * Get igdbUrl
     *
     * @return string
     */
    public function getIgdbUrl()
    {
        return $this->igdbUrl;
    }

    /**
     * Set igdbUrl
     *
     * @param string $igdbUrl
     *
     * @return Series
     */
    public function setIgdbUrl($igdbUrl)
    {
        $this->igdbUrl = $igdbUrl;

        return $this;
    }
}
