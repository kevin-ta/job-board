<?php

namespace Zephyr\JobBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="job")
 * @ORM\Entity(repositoryClass="Zephyr\JobBundle\Entity\JobRepository")
 */
class Job extends BaseUser
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
     * @ORM\Column(type="text", length=65535)
     */
    private $content;

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
}
