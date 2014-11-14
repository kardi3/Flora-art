<?php

/**
 * Slider_DataTables_Adapter_SlideLayer
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Slider_DataTables_Adapter_SlideLayer extends Default_DataTables_Adapter_AdapterAbstract {
    
    protected function getBaseQuery() {
        $q = $this->table->createQuery('l');
        $q->addSelect('l.*');
        $q->addSelect('pr.*');
        $q->leftJoin('l.PhotoRoot pr');
        
        if($id = $this->request->getParam('id')) {
            $q->andWhere('l.level > ? AND l.slide_id = ?', array(0, $id));
        }  
        $q->addOrderBy('l.lft ASC');
        return $q;
    }
    
}

