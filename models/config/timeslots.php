<?php

return [
    'list' => [
        'toolbar' => [
            'buttons' => [
		        'create' => [
		            'label' => 'lang:admin::lang.button_new',
		            'class' => 'btn btn-primary',
		            'href' => 'thoughtco/maxorders/timeslots/create',
		        ],	            
                'delete' => ['label' => 'lang:admin::lang.button_delete', 'class' => 'btn btn-danger', 'data-request-form' => '#list-form', 'data-request' => 'onDelete', 'data-request-data' => "_method:'DELETE'", 'data-request-data' => "_method:'DELETE'", 'data-request-confirm' => 'lang:admin::lang.alert_warning_confirm'],
            ],
        ],
		'filter' => [
			'scopes' => [
				'is_enabled' => [
					'label' => 'lang:admin::lang.text_filter_status',
					'type' => 'switch',
					'conditions' => 'timeslot_status = :filtered',
				],
			],	
		],
        'columns' => [
            'edit' => [
                'type' => 'button',
                'iconCssClass' => 'fa fa-pencil',
                'attributes' => [
                    'class' => 'btn btn-edit',
                    'href' => 'thoughtco/maxorders/timeslots/edit/{id}',
                ],
            ],
			'timeslot_label' => [
                'label' => 'lang:thoughtco.maxorders::default.column_label',
                'type' => 'text',
                'sortable' => TRUE,
            ],
			'timeslot_day' => [
				'label' => 'lang:thoughtco.maxorders::default.column_day',
				'type' => 'day-of-week',
				'sortable' => FALSE,
				'formatter' => function($record, $column, $value){
					$days = Thoughtco\Maxorders\Models\Timeslots::getTimeslotDayOptions();
					foreach ($value as $i=>$j)
						$value[$i] = $days[$j];
					return implode(', ', $value);
				}
			],				
			'timeslot_start' => [
				'label' => 'lang:thoughtco.maxorders::default.column_start',
				'type' => 'text',
				'sortable' => FALSE,
			],			
			'timeslot_end' => [
				'label' => 'lang:thoughtco.maxorders::default.column_end',
				'type' => 'text',
				'sortable' => FALSE,
			],			  
			'timeslot_status' => [
				'label' => 'lang:thoughtco.maxorders::default.column_status',
				'type' => 'switch',
				'sortable' => FALSE,
			],
        ],
    ],

    'form' => [
        'toolbar' => [
            'buttons' => [
                'back' => ['label' => 'lang:admin::lang.button_icon_back', 'class' => 'btn btn-default', 'href' => 'thoughtco/maxorders/timeslots'],
                'save' => [
                    'label' => 'lang:admin::lang.button_save',
                    'class' => 'btn btn-primary',
                    'data-request' => 'onSave',
                ],
                'saveClose' => [
                    'label' => 'lang:admin::lang.button_save_close',
                    'class' => 'btn btn-default',
                    'data-request' => 'onSave',
                    'data-request-data' => 'close:1',
                ],
            ],
        ],
        'tabs' => [
	        'fields' => [
	            'timeslot_label' => [
	                'tab' => 'lang:thoughtco.maxorders::default.tab_setup',
	                'label' => 'lang:thoughtco.maxorders::default.label_label',
	                'type' => 'text',
	            ],				
	            'timeslot_locations' => [
	                'tab' => 'lang:thoughtco.maxorders::default.tab_setup',
	                'label' => 'lang:thoughtco.maxorders::default.label_location',
	                'type' => 'selectlist',
					'span' => 'left',
	            ],
				'timeslot_status' => [
	                'tab' => 'lang:thoughtco.maxorders::default.tab_setup',
					'label' => 'lang:thoughtco.maxorders::default.label_status',
					'type' => 'switch',
					'span' => 'right',
				],
				'timeslot_day' => [
	                'tab' => 'lang:thoughtco.maxorders::default.tab_setup',
					'label' => 'lang:thoughtco.maxorders::default.label_dayofweek',
					'type' => 'selectlist',
					'span' => 'left',
				],					
		        'timeslot_order_type' => [
	                'tab' => 'lang:thoughtco.maxorders::default.tab_setup',
		            'label' => 'lang:thoughtco.maxorders::default.label_ordertype',
		            'type' => 'radiotoggle',
		            'span' => 'right',
		            'options' => [
		            	'lang:thoughtco.maxorders::default.value_all',
		            	'lang:thoughtco.maxorders::default.value_delivery',
		            	'lang:thoughtco.maxorders::default.value_collection',
		            ],
		        ],				
				'timeslot_start' => [
	                'tab' => 'lang:thoughtco.maxorders::default.tab_setup',
					'label' => 'lang:thoughtco.maxorders::default.label_starttime',
					'type' => 'datepicker',
					'mode' => 'time',
					'span' => 'left',
				],					
				'timeslot_end' => [
	                'tab' => 'lang:thoughtco.maxorders::default.tab_setup',
					'label' => 'lang:thoughtco.maxorders::default.label_endtime',
					'type' => 'datepicker',
					'mode' => 'time',
					'span' => 'right',
				],
				'timeslot_max_type' => [
	                'tab' => 'lang:thoughtco.maxorders::default.tab_setup',
					'label' => 'lang:thoughtco.maxorders::default.label_type',
					'type' => 'select',
					'span' => 'left',
				],	
				'timeslot_max' => [
	                'tab' => 'lang:thoughtco.maxorders::default.tab_setup',
					'label' => 'lang:thoughtco.maxorders::default.label_max',
					'type' => 'number',
					'span' => 'right',
		        ],
		        'timeslot_categories' => [
	                'tab' => 'lang:thoughtco.maxorders::default.tab_setup',
		            'label' => 'lang:thoughtco.maxorders::default.label_categories',
		            'type' => 'selectlist',
		            'trigger' => [
		                'action' => 'show',
		                'field' => 'timeslot_max_type',
		                'condition' => 'value[covers]',
		            ],
		        ],					
				
			]
        ]
    ],
];