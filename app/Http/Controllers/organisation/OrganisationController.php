<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 19-3-2019
 * Time: 11:42
 */

namespace App\Http\Controllers\organisation;


use App\Http\Controllers\ApiController;
use App\Http\Handlers\OrganisationHandler;
use Illuminate\Http\Request;

class OrganisationController extends ApiController
{
    /**
     * @var OrganisationHandler
     */
    private $organisationHandler;

    public function __construct(OrganisationHandler $organisationHandler)
    {
        $this->organisationHandler = $organisationHandler;
    }

    public function getOrganisation(Request $request)
    {
        return $this->getReturnValueObject($request, $this->organisationHandler->getOrganisationByName($request->input('name')));
    }

}