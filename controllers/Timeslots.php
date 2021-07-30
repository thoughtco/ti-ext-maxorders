<?php

namespace Thoughtco\Maxorders\Controllers;

use AdminMenu;
use Admin\Facades\AdminLocation;
use ApplicationException;

class Timeslots extends \Admin\Classes\AdminController
{
    public $implement = [
        'Admin\Actions\FormController',
        'Admin\Actions\ListController',
        'Admin\Actions\LocationAwareController',
    ];

    public $listConfig = [
        'list' => [
            'model' => 'Thoughtco\Maxorders\Models\Timeslots',
            'title' => 'lang:thoughtco.maxorders::default.text_title',
            'emptyMessage' => 'lang:thoughtco.maxorders::default.text_empty',
            'defaultSort' => ['id', 'DESC'],
            'configFile' => 'timeslots',
        ],
    ];

    public $formConfig = [
        'name' => 'lang:thoughtco.maxorders::default.text_form_name',
        'model' => 'Thoughtco\Maxorders\Models\Timeslots',
        'create' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'thoughtco/maxorders/timeslots/edit/{id}',
            'redirectClose' => 'thoughtco/maxorders/timeslots',
        ],        
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'thoughtco/maxorders/timeslots/edit/{id}',
            'redirectClose' => 'thoughtco/maxorders/timeslots',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'redirect' => 'thoughtco/maxorders/timeslots',
        ],
        'delete' => [
            'redirect' => 'thoughtco/maxorders/timeslots',
        ],
        'configFile' => 'timeslots',
    ];

    protected $requiredPermissions = 'Thoughtco.Maxorders.*';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('restaurant', 'maxorders');
        
    }

    public function index()
    {
        $this->asExtension('ListController')->index();
    }
}
