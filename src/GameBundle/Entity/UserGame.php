<?php
namespace GameBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_game")
 * @ORM\Entity(repositoryClass="GameBundle\Repository\UserGameRepository")
 */
class UserGame extends BaseCreateUpdate
{
    const PROGRESS_LIST = [
        'Never Played' => 'NEVER_PLAYED',
        'In Progress' => 'IN_PROGRESS',
        'Finished' => 'FINISHED',
        'Abandoned' => 'ABANDONED'
    ];

    const COMPLETENESS_LIST = [
        'Loose' => 'LOOSE',
        'No Manual' => 'NO_MANUAL',
        'No Box' => 'NO_BOX',
        'Complete' => 'COMPLETE',
        'Dematerialized' => 'DEMATERIALIZED',
        'New' => 'NEW'
    ];

    const COND_LIST = [
        'Not So Good' => 'BAD',
        'Good Condition' => 'GOOD',
        'Very Good Condition' => 'VERY_GOOD',
        'Near Mint' => 'NEAR_MINT',
        'Mint' => 'MINT',
    ];

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Game")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $game;

    /**
     * @ORM\ManyToOne(targetEntity="Platform")
     * @ORM\JoinColumn(name="platform_id", referencedColumnName="id")
     **/
    private $platform;

    /**
     * @ORM\Column(type="date", name="release_date", nullable=true)
     */
    private $releaseDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min = 0, max = 20)
     */
    private $rating;

    /**
     * @ORM\Column(length=10)
     * @Assert\NotBlank()
     */
    private $version;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, name="price_asked", nullable=true)
     */
    private $priceAsked;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, name="price_paid", nullable=true)
     */
    private $pricePaid;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, name="price_resale", nullable=true)
     */
    private $priceResale;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, name="price_sold", nullable=true)
     */
    private $priceSold;

    /**
     * @ORM\Column(type="date", name="purchase_date", nullable=true)
     */
    private $purchaseDate;

    /**
     * @ORM\Column(type="date", name="sale_date", nullable=true)
     */
    private $saleDate;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotNull()
     * @Assert\Choice(choices=UserGame::PROGRESS_LIST)
     */
    private $progress = 'LOOSE';

    /**
     * @ORM\Column(type="string")
     * @Assert\NotNull()
     * @Assert\Choice(choices=UserGame::COND_LIST)
     */
    private $cond = 'VERY_GOOD';

    /**
     * @ORM\Column(type="string")
     * @Assert\NotNull()
     * @Assert\Choice(choices=UserGame::COMPLETENESS_LIST)
     */
    private $completeness = 'COMPLETE';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(name="purchase_place", nullable=true)
     */
    private $purchasePlace;

    /**
     * @ORM\Column(name="sale_place", nullable=true)
     */
    private $salePlace;

    /**
     * @ORM\ManyToOne(targetEntity="Contact")
     * @ORM\JoinColumn(name="purchase_contact_id", referencedColumnName="id")
     **/
    private $purchaseContact;
    /**
     * @ORM\ManyToOne(targetEntity="Contact")
     * @ORM\JoinColumn(name="sale_contact_id", referencedColumnName="id")
     **/
    private $saleContact;

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
     * Get rating
     *
     * @return integer
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set rating
     *
     * @param integer $rating
     *
     * @return UserGame
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version
     *
     * @param string $version
     *
     * @return UserGame
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get priceAsked
     *
     * @return integer
     */
    public function getPriceAsked()
    {
        return $this->priceAsked;
    }

    /**
     * Set priceAsked
     *
     * @param integer $priceAsked
     *
     * @return UserGame
     */
    public function setPriceAsked($priceAsked)
    {
        if ($priceAsked == '') {
            $this->priceAsked = null;
        } else {
            $this->priceAsked = $priceAsked;
        }

        return $this;
    }

    /**
     * Get pricePaid
     *
     * @return integer
     */
    public function getPricePaid()
    {
        return $this->pricePaid;
    }

    /**
     * Set pricePaid
     *
     * @param integer $pricePaid
     *
     * @return UserGame
     */
    public function setPricePaid($pricePaid)
    {
        if ($pricePaid == '') {
            $this->pricePaid = null;
        } else {
            $this->pricePaid = $pricePaid;
        }

        return $this;
    }

    /**
     * Get priceResale
     *
     * @return integer
     */
    public function getPriceResale()
    {
        return $this->priceResale;
    }

    /**
     * Set priceResale
     *
     * @param integer $priceResale
     *
     * @return UserGame
     */
    public function setPriceResale($priceResale)
    {
        if ($priceResale == '') {
            $this->priceResale = null;
        } else {
            $this->priceResale = $priceResale;
        }

        return $this;
    }

    /**
     * Get priceSold
     *
     * @return integer
     */
    public function getPriceSold()
    {
        return $this->priceSold;
    }

    /**
     * Set priceSold
     *
     * @param integer $priceSold
     *
     * @return UserGame
     */
    public function setPriceSold($priceSold)
    {
        if ($priceSold == '') {
            $this->priceSold = null;
        } else {
            $this->priceSold = $priceSold;
        }

        return $this;
    }

    /**
     * Get purchaseDate
     *
     * @return \DateTime
     */
    public function getPurchaseDate()
    {
        return $this->purchaseDate;
    }

    /**
     * Set purchaseDate
     *
     * @param \DateTime $purchaseDate
     *
     * @return UserGame
     */
    public function setPurchaseDate($purchaseDate)
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

    /**
     * Get saleDate
     *
     * @return \DateTime
     */
    public function getSaleDate()
    {
        return $this->saleDate;
    }

    /**
     * Set saleDate
     *
     * @param \DateTime $saleDate
     *
     * @return UserGame
     */
    public function setSaleDate($saleDate)
    {
        $this->saleDate = $saleDate;

        return $this;
    }

    /**
     * Get progress
     *
     * @return string
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Set progress
     *
     * @param string $progress
     *
     * @return UserGame
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * Get cond
     *
     * @return string
     */
    public function getCond()
    {
        return $this->cond;
    }

    /**
     * Set cond
     *
     * @param string $cond
     *
     * @return UserGame
     */
    public function setCond($cond)
    {
        $this->cond = $cond;

        return $this;
    }

    /**
     * Get completeness
     *
     * @return string
     */
    public function getCompleteness()
    {
        return $this->completeness;
    }

    /**
     * Set cond
     *
     * @param string $completeness
     *
     * @return UserGame
     */
    public function setCompleteness($completeness)
    {
        $this->completeness = $completeness;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return UserGame
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return UserGame
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get game
     *
     * @return Game
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * Set game
     *
     * @param Game $game
     *
     * @return UserGame
     */
    public function setGame(Game $game)
    {
        $this->game = $game;

        return $this;
    }

    /**
     * Get platform
     *
     * @return Platform
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set platform
     *
     * @param Platform $platform
     *
     * @return UserGame
     */
    public function setPlatform(Platform $platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get releaseDate
     *
     * @return \DateTime
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * Set releaseDate
     *
     * @param \DateTime $releaseDate
     *
     * @return UserGame
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    /**
     * Get purchasePlace
     *
     * @return string
     */
    public function getPurchasePlace()
    {
        return $this->purchasePlace;
    }

    /**
     * Set purchasePlace
     *
     * @param string
     *
     * @return UserGame
     */
    public function setPurchasePlace($purchasePlace)
    {
        $this->purchasePlace = $purchasePlace;

        return $this;
    }

    /**
     * Get salePlace
     *
     * @return string
     */
    public function getSalePlace()
    {
        return $this->salePlace;
    }

    /**
     * Set salePlace
     *
     * @param string
     *
     * @return UserGame
     */
    public function setSalePlace($salePlace)
    {
        $this->salePlace = $salePlace;

        return $this;
    }

    /**
     * Get purchaseContact
     *
     * @return Contact
     */
    public function getPurchaseContact()
    {
        return $this->purchaseContact;
    }

    /**
     * Set purchaseContact
     *
     * @param Contact $purchaseContact
     *
     * @return UserGame
     */
    public function setPurchaseContact(Contact $purchaseContact = null)
    {
        $this->purchaseContact = $purchaseContact;

        return $this;
    }

    /**
     * Get saleContact
     *
     * @return Contact
     */
    public function getSaleContact()
    {
        return $this->saleContact;
    }

    /**
     * Set saleContact
     *
     * @param Contact $saleContact
     *
     * @return UserGame
     */
    public function setSaleContact(Contact $saleContact = null)
    {
        $this->saleContact = $saleContact;

        return $this;
    }
}
