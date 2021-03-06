<?php

/**
 * Slider_Model_Doctrine_SlideTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Slider_Model_Doctrine_SlideTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object Slider_Model_Doctrine_SlideTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Slider_Model_Doctrine_Slide');
    }
    
    public function getFullSlideQuery() {
        $q = $this->createQuery('sl')
                ->addSelect('sl.*')
                ->addSelect('l.*')
                ->addSelect('pr.*')
                ->leftJoin('sl.PhotoRoot pr')
                ->leftJoin('sl.Layers l')
                ;
        return $q;
    }
}