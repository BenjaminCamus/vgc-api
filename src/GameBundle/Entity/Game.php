<?php
namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="game")
 * @ORM\Entity(repositoryClass="GameBundle\Repository\GameRepository")
 */
class Game
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Range(min = 0, max = 20)
     */
    private $rating;

    /**
     * @ORM\Column(type="integer")
     */
    private $ratingCount;

    /**
     * @ORM\Column(type="integer", name="igdb_id", unique=true)
     */
    private $igdbId;

    /**
     * @ORM\Column(name="igdb_url")
     */
    private $igdbUrl;

    /**
     * @ORM\Column(type="datetime", name="igdb_created_at")
     */
    private $igdbCreatedAt;

    /**
     * @ORM\Column(type="datetime", name="igdb_updated_at")
     */
    private $igdbUpdatedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity="Image")
     * @ORM\JoinColumn(name="cover_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $cover;

    /**
     * @ORM\ManyToMany(targetEntity="Image")
     * @ORM\JoinTable(name="games__screenshots",
     *      joinColumns={@ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="image_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $screenshots;

    /**
     * @ORM\ManyToMany(targetEntity="Series")
     * @ORM\JoinTable(name="games__series",
     *      joinColumns={@ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="series_id", referencedColumnName="id")}
     *      )
     */
    private $series;

    /**
     * @ORM\ManyToMany(targetEntity="Company")
     * @ORM\JoinTable(name="games__developers",
     *      joinColumns={@ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="company_id", referencedColumnName="id")}
     *      )
     */
    private $developers;

    /**
     * @ORM\ManyToMany(targetEntity="Company")
     * @ORM\JoinTable(name="games__publishers",
     *      joinColumns={@ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="company_id", referencedColumnName="id")}
     *      )
     */
    private $publishers;

    /**
     * @ORM\ManyToMany(targetEntity="Mode")
     * @ORM\JoinTable(name="games__modes")
     **/
    private $modes;

    /**
     * @ORM\ManyToMany(targetEntity="Theme")
     * @ORM\JoinTable(name="games__themes")
     **/
    private $themes;

    /**
     * @ORM\ManyToMany(targetEntity="Genre")
     * @ORM\JoinTable(name="games__genres")
     **/
    private $genres;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->developers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->publishers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->modes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->themes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->genres = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * @return Game
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
     * @return Game
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get rating
     *
     * @return string
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set rating
     *
     * @param string $rating
     *
     * @return Game
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get ratingCount
     *
     * @return integer
     */
    public function getRatingCount()
    {
        return $this->ratingCount;
    }

    /**
     * Set ratingCount
     *
     * @param integer $ratingCount
     *
     * @return Game
     */
    public function setRatingCount($ratingCount)
    {
        $this->ratingCount = $ratingCount;

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
     * @return Game
     */
    public function setIgdbUrl($igdbUrl)
    {
        $this->igdbUrl = $igdbUrl;

        return $this;
    }

    /**
     * Get igdbCreatedAt
     *
     * @return \DateTime
     */
    public function getIgdbCreatedAt()
    {
        return $this->igdbCreatedAt;
    }

    /**
     * Set igdbCreatedAt
     *
     * @param \DateTime $igdbCreatedAt
     *
     * @return Game
     */
    public function setIgdbCreatedAt($igdbCreatedAt)
    {
        $this->igdbCreatedAt = $igdbCreatedAt;

        return $this;
    }

    /**
     * Get igdbUpdatedAt
     *
     * @return \DateTime
     */
    public function getIgdbUpdatedAt()
    {
        return $this->igdbUpdatedAt;
    }

    /**
     * Set igdbUpdatedAt
     *
     * @param \DateTime $igdbUpdatedAt
     *
     * @return Game
     */
    public function setIgdbUpdatedAt($igdbUpdatedAt)
    {
        $this->igdbUpdatedAt = $igdbUpdatedAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Game
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Game
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get cover
     *
     * @return Image
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * Set cover
     *
     * @param Image $cover
     *
     * @return Game
     */
    public function setCover($cover)
    {
        $this->cover = $cover;

        return $this;
    }

    /**
     * Add screenshot
     *
     * @param Image $screenshot
     *
     * @return Game
     */
    public function addScreenshot(Image $screenshot)
    {
        $this->screenshots[] = $screenshot;

        return $this;
    }

    /**
     * Remove screenshot
     *
     * @param Image $screenshot
     */
    public function removeScreenshot(Image $screenshot)
    {
        $this->screenshots->removeElement($screenshot);
    }

    /**
     * Get screenshots
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getScreenshots()
    {
        return $this->screenshots;
    }

    /**
     * Set screenshots
     *
     * @param \Doctrine\Common\Collections\Collection $screenshots
     *
     * @return Game
     */
    public function setScreenshots($screenshots)
    {
        $this->screenshots = $screenshots;

        return $this;
    }

    /**
     * Get series
     *
     * @return Series
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * Set series
     *
     * @param Series $series
     *
     * @return Game
     */
    public function setSeries(Series $series = null)
    {
        $this->series = $series;

        return $this;
    }

    /**
     * Add developer
     *
     * @param Company $developer
     *
     * @return Game
     */
    public function addDeveloper(Company $developer)
    {
        $this->developers[] = $developer;

        return $this;
    }

    /**
     * Remove developer
     *
     * @param Company $developer
     */
    public function removeDeveloper(Company $developer)
    {
        $this->developers->removeElement($developer);
    }

    /**
     * Get developers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDevelopers()
    {
        return $this->developers;
    }

    /**
     * Add publisher
     *
     * @param Company $publisher
     *
     * @return Game
     */
    public function addPublisher(Company $publisher)
    {
        $this->publishers[] = $publisher;

        return $this;
    }

    /**
     * Remove publisher
     *
     * @param Company $publisher
     */
    public function removePublisher(Company $publisher)
    {
        $this->publishers->removeElement($publisher);
    }

    /**
     * Get publishers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPublishers()
    {
        return $this->publishers;
    }

    /**
     * Add mode
     *
     * @param Mode $mode
     *
     * @return Game
     */
    public function addMode(Mode $mode)
    {
        $this->modes[] = $mode;

        return $this;
    }

    /**
     * Remove mode
     *
     * @param Mode $mode
     */
    public function removeMode(Mode $mode)
    {
        $this->modes->removeElement($mode);
    }

    /**
     * Get modes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModes()
    {
        return $this->modes;
    }

    /**
     * Add theme
     *
     * @param Theme $theme
     *
     * @return Game
     */
    public function addTheme(Theme $theme)
    {
        $this->themes[] = $theme;

        return $this;
    }

    /**
     * Remove theme
     *
     * @param Theme $theme
     */
    public function removeTheme(Theme $theme)
    {
        $this->themes->removeElement($theme);
    }

    /**
     * Get themes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getThemes()
    {
        return $this->themes;
    }

    /**
     * Add genre
     *
     * @param Genre $genre
     *
     * @return Game
     */
    public function addGenre(Genre $genre)
    {
        $this->genres[] = $genre;

        return $this;
    }

    /**
     * Remove genre
     *
     * @param Genre $genre
     */
    public function removeGenre(Genre $genre)
    {
        $this->genres->removeElement($genre);
    }

    /**
     * Get genres
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGenres()
    {
        return $this->genres;
    }
}
