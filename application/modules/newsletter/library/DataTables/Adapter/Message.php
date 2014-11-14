<?php

/**
 * Newsletter_DataTables_Adapter_Message
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Newsletter_DataTables_Adapter_Message extends Default_DataTables_Adapter_AdapterAbstract {
    
    public function getBaseQuery() {
        $q = $this->table->createQuery('x');
        $q->select('x.*');
        $q->addSelect('g.*');
        $q->leftJoin('x.Groups g');
        return $q;
    }
}

