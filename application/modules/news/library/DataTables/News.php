<?php

/**
 * News_DataTables_NewsSerwis1
 *
 * @author Tomasz Kardas <kardi31@tlen.pl>
 */
class News_DataTables_News extends Default_DataTables_DataTablesAbstract {
    
    public function getAdapterClass() {
        return 'News_DataTables_Adapter_News';
    }
}

