<?php

/**
 * Slider_Model_Doctrine_SlideLayerTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Slider_Model_Doctrine_SlideLayerTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object Slider_Model_Doctrine_SlideLayerTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Slider_Model_Doctrine_SlideLayer');
    }
    
    public function getFullSlideLayerQuery() {
        $q = $this->createQuery('l')
                ->select('l.*')
                ->addSelect('pr.*')
                ->leftJoin('l.PhotoRoot pr')
                ;
        return $q;
    }
}