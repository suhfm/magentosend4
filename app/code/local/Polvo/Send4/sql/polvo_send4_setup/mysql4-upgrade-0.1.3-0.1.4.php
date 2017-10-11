<?php/*

create table sfm_send4_quote(
    entity_id int(10) AUTO_INCREMENT NOT NULL,
    quote_id int(10),
    created_at datetime,
    updated_at datetime,
    CONSTRAINT PK_send4_quote PRIMARY KEY (entity_id)
);

$installer = $this;
 
// Required tables
$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');
 
// Insert statuses
$installer->getConnection()->insertArray(
    $statusTable,
    array(
        'status',
        'label'
    ),
    array(
        array('status' => 'send4order_pending', 'label' => 'Aguardando Envio Send4'),
        array('status' => 'send4order_processing', 'label' => 'Enviado Send4'),
        array('status' => 'send4order_complete', 'label' => 'Recebido Send4'),
        array('status' => 'send4order_canceled', 'label' => 'Retirado pelo Cliente Send4')
    )
);
 
// Insert states and mapping of statuses to states
$installer->getConnection()->insertArray(
    $statusStateTable,
    array(
        'status',
        'state',
        'is_default'
    ),
    array(
        array(
            'status' => 'send4order_pending',
            'state' => 'send4order_state',
            'is_default' => 0
        ),
        array(
            'status' => 'send4order_processing',
            'state' => 'send4order_state',
            'is_default' => 0
        ),
        array(
            'status' => 'send4order_complete',
            'state' => 'send4order_state',
            'is_default' => 0
        ),
        array(
            'status' => 'send4order_canceled',
            'state' => 'send4order_state',
            'is_default' => 0
        )
    )
);*/
