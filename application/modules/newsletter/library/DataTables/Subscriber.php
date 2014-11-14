<?php

/**
 * Newsletter_DataTables_Subscriber
 *
 * @author Tomasz Kardas <kardi31@o2.pl>
 */
class Newsletter_DataTables_Subscriber extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'Newsletter_DataTables_Adapter_Subscriber';
    }
}

