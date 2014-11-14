<?php

/**
 * Product_DataTables_Adapter_Product
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class Media_DataTables_Adapter_Attachment extends Default_DataTables_Adapter_AdapterAbstract {
    protected function getBaseQuery() {
        $q = $this->table->createQuery('a');
        $q->leftJoin('a.Translation at');
        
        if($id = $this->request->getParam('id')) {
           $q->addWhere('root_id = ?',$id);
        }  
        
        $q->addWhere('a.level > 0');
        
        return $q;
    }
}

