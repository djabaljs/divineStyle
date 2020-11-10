<?php


class ProductSearch{

    private $colors;

    private $lengths;

    private $name;

    private $category;

    

    /**
     * Get the value of colors
     */ 
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * Set the value of colors
     *
     * @return  self
     */ 
    public function setColors($colors)
    {
        $this->colors = $colors;

        return $this;
    }

    /**
     * Get the value of lengths
     */ 
    public function getLengths()
    {
        return $this->lengths;
    }

    /**
     * Set the value of lengths
     *
     * @return  self
     */ 
    public function setLengths($lengths)
    {
        $this->lengths = $lengths;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of category
     */ 
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set the value of category
     *
     * @return  self
     */ 
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }
}