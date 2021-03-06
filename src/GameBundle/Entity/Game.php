<?php
namespace GameBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="game")
 * @ORM\Entity(repositoryClass="GameBundle\Repository\GameRepository")
 */
class Game extends BaseObject
{
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
     * @ORM\Column(type="boolean")
     */
    private $igdbUpdate;

    /**
     * @ORM\Column(type="datetime", name="igdb_created_at")
     */
    private $igdbCreatedAt;

    /**
     * @ORM\Column(type="datetime", name="igdb_updated_at")
     */
    private $igdbUpdatedAt;

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
     * @ORM\ManyToMany(targetEntity="Video")
     * @ORM\JoinTable(name="games__videos",
     *      joinColumns={@ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="video_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $videos;

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
     * @ORM\OneToMany(targetEntity="ReleaseDate", mappedBy="game")
     */
    private $releaseDates;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->screenshots = new \Doctrine\Common\Collections\ArrayCollection();
        $this->videos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->series = new \Doctrine\Common\Collections\ArrayCollection();
        $this->developers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->publishers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->modes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->themes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->genres = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return bool
     */
    public function isIgdbUpdate(): bool
    {
        return $this->igdbUpdate;
    }

    /**
     * @param bool $igdbUpdate
     * @return Game
     */
    public function setIgdbUpdate(bool $igdbUpdate): Game
    {
        $this->igdbUpdate = $igdbUpdate;
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
     * Add video
     *
     * @param Video $video
     *
     * @return Game
     */
    public function addVideo(Video $video)
    {
        $this->videos[] = $video;
        return $this;
    }

    /**
     * Remove video
     *
     * @param Video $video
     */
    public function removeVideo(Video $video)
    {
        $this->videos->removeElement($video);
    }

    /**
     * Get videos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Set videos
     *
     * @param \Doctrine\Common\Collections\Collection $videos
     *
     * @return Game
     */
    public function setVideos($videos)
    {
        $this->videos = $videos;
        return $this;
    }

    /**
     * Add series
     *
     * @param Series $series
     *
     * @return Game
     */
    public function addSeries(Series $series)
    {
        $this->series[] = $series;

        return $this;
    }

    /**
     * Remove series
     *
     * @param Series $series
     */
    public function removeSeries(Series $series)
    {
        $this->series->removeElement($series);
    }

    /**
     * Get series
     *
     * @return ArrayCollection
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * Add developer
     *
     * @param Company $developer
     *
     * @return null|Game
     */
    public function addDeveloper(Company $developer): ?Game
    {
        if ($this->developers->contains($developer)) {
            return null;
        }
        $this->developers->add($developer);

        return $this;
    }

    /**
     * Remove developer
     *
     * @param Company $developer
     */
    public function removeDeveloper(Company $developer)
    {
        if (!$this->developers->contains($developer)) {
            return;
        }
        $this->developers->removeElement($developer);
    }

    /**
     * Get developers
     *
     * @return ArrayCollection
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
     * @return null|Game
     */
    public function addPublisher(Company $publisher): ?Game
    {
        if ($this->publishers->contains($publisher)) {
            return null;
        }
        $this->publishers->add($publisher);

        return $this;
    }

    /**
     * Remove publisher
     *
     * @param Company $publisher
     */
    public function removePublisher(Company $publisher)
    {
        if (!$this->publishers->contains($publisher)) {
            return;
        }
        $this->publishers->removeElement($publisher);
    }

    /**
     * Get publishers
     *
     * @return ArrayCollection
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

    /**
     * Reset release dates
     *
     * @return Game
     */
    public function resetReleaseDate()
    {
        $this->releaseDates = new \Doctrine\Common\Collections\ArrayCollection();

        return $this;
    }

    /**
     * Add release date
     *
     * @param ReleaseDate $releaseDate
     *
     * @return Game
     */
    public function addReleaseDate(ReleaseDate $releaseDate)
    {
        $this->releaseDates[] = $releaseDate;

        return $this;
    }

    /**
     * Get release dates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReleaseDates()
    {
        return $this->releaseDates;
    }
}
