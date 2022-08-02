<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Faucet
 *
 * @ORM\Table(name="faucets", uniqueConstraints={@ORM\UniqueConstraint(name="url_query_ind", columns={"url", "query"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FaucetRepository")

 */
class Faucet
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=100)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="query", type="string", length=100, nullable=true)
     */
    private $query;

    /**
     * @var string
     *
     * @ORM\Column(name="info", type="string", length=500, nullable=true)
     */
    private $info;

    /**
     * @var int
     *
     * @ORM\Column(name="duration", type="integer", options={"default" = 1800})
     */
    private $duration;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="until", type="datetime", options={"default" = "CURRENT_TIMESTAMP"})
     */
    private $until;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", options={"default" = "CURRENT_TIMESTAMP"})
     */
    private $updated;

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="integer", options={"default" = 1})
     */
    private $priority;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="ban_until", type="datetime", options={"default" = "CURRENT_TIMESTAMP"})
     */
    private $banUntil;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_debt", type="boolean", options={"default" = false})
     */
    private $isDebt;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_tab", type="boolean", options={"default" = false})
     */
    private $isTab;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Faucet
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set query
     *
     * @param string $query
     *
     * @return Faucet
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get query
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set info
     *
     * @param string $info
     *
     * @return Faucet
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Get info
     *
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     *
     * @return Faucet
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set until
     *
     * @param DateTime $until
     *
     * @return Faucet
     */
    public function setUntil($until)
    {
        $this->until = $until;

        return $this;
    }

    /**
     * Get until
     *
     * @return DateTime
     */
    public function getUntil()
    {
        return $this->until;
    }

    /**
     * Set updated
     *
     * @param DateTime $updated
     *
     * @return Faucet
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     *
     * @return Faucet
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set banUntil
     *
     * @param DateTime $banUntil
     *
     * @return Faucet
     */
    public function setBanUntil($banUntil)
    {
        $this->banUntil = $banUntil;

        return $this;
    }

    /**
     * Get banUntil
     *
     * @return DateTime
     */
    public function getBanUntil()
    {
        return $this->banUntil;
    }

    /**
     * Set isDebt
     *
     * @param boolean $isDebt
     *
     * @return Faucet
     */
    public function setIsDebt($isDebt)
    {
        $this->isDebt = $isDebt;

        return $this;
    }

    /**
     * Get isDebt
     *
     * @return bool
     */
    public function getIsDebt()
    {
        return $this->isDebt;
    }

    /**
     * Set isTab
     *
     * @param boolean $isTab
     *
     * @return Faucet
     */
    public function setIsTab($isTab)
    {
        $this->isTab = $isTab;

        return $this;
    }

    /**
     * Get isTab
     *
     * @return bool
     */
    public function getIsTab()
    {
        return $this->isTab;
    }

}

