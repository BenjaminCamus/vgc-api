<?php
namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


class BaseObject extends BaseCreateUpdate
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column
     * @Gedmo\Slug(fields={"name"})
     */
    protected $slug;

    /**
     * @ORM\Column(type="integer", name="igdb_id", unique=true)
     */
    protected $igdbId;

    /**
     * @ORM\Column(name="igdb_url")
     */
    protected $igdbUrl;

    /**
     * To String
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name;
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
     * @return Company
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
     * @return Company
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
     * @return Company
     */
    public function setIgdbUrl($igdbUrl)
    {
        $this->igdbUrl = $igdbUrl;

        return $this;
    }
}