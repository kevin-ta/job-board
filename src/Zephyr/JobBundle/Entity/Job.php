<?php

namespace Zephyr\JobBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="job")
 * @ORM\Entity(repositoryClass="Zephyr\JobBundle\Entity\JobRepository")
 */
class Job
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $short_content;

    /**
     * @ORM\Column(type="text", length=65535)
     */
    private $content;

    /**
     * @ORM\Column(type="boolean")
     */
    private $valid;

    /**
     * @ORM\Column(type="boolean")
     */
    private $done;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $expire;

    /**
     * @ORM\ManyToOne(targetEntity="Zephyr\JobBundle\Entity\Category")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Zephyr\UserBundle\Entity\User")
     */
    private $id_user;

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Job
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Job
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set category
     *
     * @param \Zephyr\JobBundle\Entity\Category $category
     *
     * @return Job
     */
    public function setCategory(\Zephyr\JobBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Zephyr\JobBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set idUser
     *
     * @param \Zephyr\UserBundle\Entity\User $idUser
     *
     * @return Job
     */
    public function setIdUser(\Zephyr\UserBundle\Entity\User $idUser = null)
    {
        $this->id_user = $idUser;

        return $this;
    }

    /**
     * Get idUser
     *
     * @return \Zephyr\UserBundle\Entity\User
     */
    public function getIdUser()
    {
        return $this->id_user;
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
     * Set shortContent
     *
     * @param string $shortContent
     *
     * @return Job
     */
    public function setShortContent($shortContent)
    {
        $this->short_content = $shortContent;

        return $this;
    }

    /**
     * Get shortContent
     *
     * @return string
     */
    public function getShortContent()
    {
        return $this->short_content;
    }

    /**
     * Set valid
     *
     * @param boolean $valid
     *
     * @return Job
     */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * Get valid
     *
     * @return boolean
     */
    public function getValid()
    {
        return $this->valid;
    }
}
